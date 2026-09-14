<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\App;
use App\Models\AppEdition;
use App\Models\Order;
use App\Models\Setting;
use App\Services\StripeCheckout;
use App\Services\TaxCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Stripe\Exception\ApiErrorException;

class CheckoutController extends Controller
{
    public function __construct(
        private TaxCalculator $taxCalculator,
        private StripeCheckout $stripeCheckout,
    ) {}

    public function create(App $app, ?AppEdition $edition = null): View
    {
        $this->guardPurchasable($app, $edition);

        return view('checkout.create', ['app' => $app, 'edition' => $edition]);
    }

    public function store(CheckoutRequest $request, App $app, ?AppEdition $edition = null): RedirectResponse
    {
        $this->guardPurchasable($app, $edition);

        $data = $request->validated();
        $subtotalCents = $edition ? $edition->price_cents : $app->effectivePriceCents();

        $taxLines = $this->taxCalculator->calculate($subtotalCents, $data['billing_country'], $data['billing_province'] ?: null);
        $taxCents = $this->taxCalculator->totalCents($taxLines);

        $order = DB::transaction(function () use ($app, $edition, $data, $subtotalCents, $taxLines, $taxCents) {
            $order = Order::create([
                ...$data,
                'order_number' => $this->generateOrderNumber(),
                'subtotal_cents' => $subtotalCents,
                'tax_cents' => $taxCents,
                'total_cents' => $subtotalCents + $taxCents,
                'currency' => $edition->currency ?? $app->currency ?? 'CAD',
                'payment_status' => 'pending',
                'order_status' => 'pending',
            ]);

            $order->items()->create([
                'app_id' => $app->id,
                'app_edition_id' => $edition?->id,
                'app_name_snapshot' => $app->name,
                'edition_name_snapshot' => $edition?->name,
                // Immutable at purchase time — §21/§24: an entitlement/
                // receipt belongs to what was bought then, not to
                // whatever the edition's entitlements say later.
                'included_platforms_snapshot' => $edition?->includedPlatforms(),
                'license_label_snapshot' => $app->licenseLabel(),
                'unit_price_cents' => $subtotalCents,
                'quantity' => 1,
                'line_subtotal_cents' => $subtotalCents,
            ]);

            foreach ($taxLines as $line) {
                $order->taxLines()->create([
                    'tax_rule_id' => $line['tax_rule_id'],
                    'tax_name_snapshot' => $line['tax_name'],
                    'percentage_snapshot' => $line['percentage'],
                    'amount_cents' => $line['amount_cents'],
                ]);
            }

            return $order;
        });

        if (! $this->stripeCheckout->isConfigured()) {
            $order->delete();

            return back()->withErrors(['checkout' => 'Online purchasing isn\'t available yet — please use the Contact page to buy this app.']);
        }

        $lineItemName = $edition ? "{$app->name} — {$edition->name}" : $app->name;

        try {
            $url = $this->stripeCheckout->createSessionUrl(
                $order,
                $lineItemName,
                $order->total_cents,
                $order->currency,
                route('checkout.success', $app),
                route('checkout.cancel', $app),
                $edition?->id,
            );
        } catch (ApiErrorException $e) {
            $order->delete();

            return back()->withErrors(['checkout' => 'We couldn\'t start checkout right now. Please try again shortly or use the Contact page.']);
        }

        return redirect()->away($url);
    }

    public function success(App $app): View
    {
        $order = request('session_id')
            ? Order::where('stripe_checkout_session_id', request('session_id'))->first()
            : null;

        $myDownloadsUrl = ($order && $order->payment_status === 'paid')
            ? URL::signedRoute('my-downloads.show', ['email' => $order->customer_email])
            : null;

        // §20: only ever what the webhook has already confirmed and
        // issued — never generated or faked from this success-page
        // request itself (see CLAUDE.md's payment-confirmation rule).
        $licenses = ($order && $order->payment_status === 'paid')
            ? $order->licenses()->where('app_id', $app->id)->get()
            : collect();

        return view('checkout.success', ['app' => $app, 'order' => $order, 'myDownloadsUrl' => $myDownloadsUrl, 'licenses' => $licenses]);
    }

    public function cancel(App $app): View
    {
        return view('checkout.cancel', ['app' => $app]);
    }

    private function guardPurchasable(App $app, ?AppEdition $edition): void
    {
        abort_unless($app->status === 'published' && $app->direct_purchase_enabled, 404);

        if ($app->hasEditions()) {
            // Editions configured: the customer must go through edition
            // selection — never let the old flat-price route bypass it.
            abort_unless($edition && $edition->app_id === $app->id && $edition->active, 404);

            return;
        }

        abort_if($edition, 404);
        abort_unless(! $app->is_free && $app->effectivePriceCents() !== null, 404);
    }

    private function generateOrderNumber(): string
    {
        $prefix = Setting::get('order_prefix', 'NIA-');

        do {
            $candidate = $prefix.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (Order::where('order_number', $candidate)->exists());

        return $candidate;
    }
}
