<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\OrderReceipt;
use App\Models\Order;
use App\Services\AuditLogger;
use App\Services\EntitlementService;
use App\Services\LicenseService;
use App\Services\StripeCheckout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Stripe\Exception\ApiErrorException;

class OrderController extends Controller
{
    public function __construct(
        private StripeCheckout $stripeCheckout,
        private EntitlementService $entitlements,
        private LicenseService $licenses,
    ) {}

    public function index(Request $request): View
    {
        $query = Order::query()->with('items')->latest();

        if ($status = $request->string('status')->toString()) {
            $query->where('payment_status', $status);
        }

        if ($search = $request->string('q')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        return view('admin.orders.index', [
            'orders' => $query->paginate(25)->withQueryString(),
        ]);
    }

    public function show(Order $order): View
    {
        $order->load(['items', 'taxLines', 'payments', 'refunds.administrator', 'entitlements.platform', 'entitlements.edition']);

        return view('admin.orders.show', ['order' => $order]);
    }

    public function resendReceipt(Order $order): RedirectResponse
    {
        try {
            Mail::to($order->customer_email)->send(new OrderReceipt($order));
        } catch (\Throwable $e) {
            return back()->withErrors(['receipt' => 'Couldn\'t resend the receipt email: '.$e->getMessage()]);
        }

        return back()->with('status', "Receipt resent to {$order->customer_email}.");
    }

    public function refund(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['nullable', 'numeric', 'min:0.01'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        if (! $order->stripe_payment_intent_id) {
            return back()->withErrors(['refund' => 'This order has no recorded payment to refund.']);
        }

        $amountCents = ! empty($data['amount']) ? (int) round($data['amount'] * 100) : null;

        try {
            $stripeRefund = $this->stripeCheckout->refund($order->stripe_payment_intent_id, $amountCents);
        } catch (ApiErrorException $e) {
            return back()->withErrors(['refund' => 'Stripe refund failed: '.$e->getMessage()]);
        }

        $order->refunds()->create([
            'payment_id' => $order->payments()->latest()->first()?->id,
            'stripe_refund_id' => $stripeRefund->id,
            'amount_cents' => $amountCents ?? $order->total_cents,
            'reason' => $data['reason'] ?? null,
            'administrator_id' => Auth::id(),
            'status' => 'succeeded',
        ]);

        $fullyRefunded = $order->totalRefundedCents() >= $order->total_cents;
        $order->update(['payment_status' => $fullyRefunded ? 'refunded' : 'partially_refunded']);

        if ($fullyRefunded) {
            $this->entitlements->revokeForOrder($order, 'refund');
            $this->licenses->revokeForOrder($order, 'refunded');
        }

        AuditLogger::record('order.refunded', $order, null, [
            'amount_cents' => $amountCents ?? $order->total_cents,
            'reason' => $data['reason'] ?? null,
        ]);

        return back()->with('status', 'Refund issued.');
    }
}
