<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\App;
use App\Models\Order;
use App\Models\Setting;
use App\Services\StripeCheckout;
use App\Services\TaxCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Stripe\Exception\ApiErrorException;

class CheckoutController extends Controller
{
    public function __construct(
        private TaxCalculator $taxCalculator,
        private StripeCheckout $stripeCheckout,
    ) {}

    public function create(App $app): View
    {
        $this->guardPurchasable($app);

        return view('checkout.create', ['app' => $app]);
    }

    public function store(CheckoutRequest $request, App $app): RedirectResponse
    {
        $this->guardPurchasable($app);

        $data = $request->validated();
        $subtotalCents = $app->effectivePriceCents();

        $taxLines = $this->taxCalculator->calculate($subtotalCents, $data['billing_country'], $data['billing_province'] ?: null);
        $taxCents = $this->taxCalculator->totalCents($taxLines);

        $order = DB::transaction(function () use ($app, $data, $subtotalCents, $taxLines, $taxCents) {
            $order = Order::create([
                ...$data,
                'order_number' => $this->generateOrderNumber(),
                'subtotal_cents' => $subtotalCents,
                'tax_cents' => $taxCents,
                'total_cents' => $subtotalCents + $taxCents,
                'currency' => $app->currency ?? 'CAD',
                'payment_status' => 'pending',
                'order_status' => 'pending',
            ]);

            $order->items()->create([
                'app_id' => $app->id,
                'app_name_snapshot' => $app->name,
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

        try {
            $url = $this->stripeCheckout->createSessionUrl(
                $order,
                $app->name,
                $order->total_cents,
                $order->currency,
                route('checkout.success', $app),
                route('checkout.cancel', $app),
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

        return view('checkout.success', ['app' => $app, 'order' => $order]);
    }

    public function cancel(App $app): View
    {
        return view('checkout.cancel', ['app' => $app]);
    }

    private function guardPurchasable(App $app): void
    {
        abort_unless(
            $app->status === 'published' && ! $app->is_free && $app->direct_purchase_enabled && $app->effectivePriceCents() !== null,
            404
        );
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
