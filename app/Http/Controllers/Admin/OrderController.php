<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\StripeCheckout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Stripe\Exception\ApiErrorException;

class OrderController extends Controller
{
    public function __construct(private StripeCheckout $stripeCheckout) {}

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
        $order->load(['items', 'taxLines', 'payments', 'refunds.administrator']);

        return view('admin.orders.show', ['order' => $order]);
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

        return back()->with('status', 'Refund issued.');
    }
}
