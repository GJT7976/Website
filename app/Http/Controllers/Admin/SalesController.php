<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class SalesController extends Controller
{
    public function index(Request $request): View
    {
        [$from, $to, $period] = $this->resolvePeriod($request);

        $orders = Order::with('items')->between($from, $to)->orderByDesc('created_at')->get();

        return view('admin.sales.index', [
            'orders' => $orders,
            'period' => $period,
            'from' => $from,
            'to' => $to,
            'totals' => $this->totals($orders),
        ]);
    }

    public function export(Request $request): Response
    {
        [$from, $to] = $this->resolvePeriod($request);

        $orders = Order::with('items')->between($from, $to)->orderBy('created_at')->get();

        $csv = "Date,Order Number,Customer,App,Subtotal,Tax,Total,Refunded,Status\n";

        foreach ($orders as $order) {
            $appNames = $order->items->pluck('app_name_snapshot')->implode('; ');
            $csv .= implode(',', [
                $order->created_at->toDateString(),
                $order->order_number,
                '"'.str_replace('"', '""', $order->customer_name).'"',
                '"'.str_replace('"', '""', $appNames).'"',
                number_format($order->subtotal_cents / 100, 2),
                number_format($order->tax_cents / 100, 2),
                number_format($order->total_cents / 100, 2),
                number_format($order->totalRefundedCents() / 100, 2),
                $order->payment_status,
            ])."\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="sales-'.$from->toDateString().'-to-'.$to->toDateString().'.csv"',
        ]);
    }

    private function totals($orders): array
    {
        $paid = $orders->whereIn('payment_status', ['paid', 'partially_refunded', 'refunded']);

        return [
            'order_count' => $orders->count(),
            'gross_cents' => $paid->sum('total_cents'),
            'tax_cents' => $paid->sum('tax_cents'),
            'refunded_cents' => $orders->sum(fn (Order $o) => $o->totalRefundedCents()),
            'net_cents' => $paid->sum('total_cents') - $orders->sum(fn (Order $o) => $o->totalRefundedCents()),
        ];
    }

    /**
     * @return array{0: Carbon, 1: Carbon, 2: string}
     */
    private function resolvePeriod(Request $request): array
    {
        $period = $request->string('period')->toString() ?: 'month';
        $now = Carbon::now();

        [$from, $to] = match ($period) {
            'today' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            'quarter' => [$now->copy()->startOfQuarter(), $now->copy()->endOfQuarter()],
            'year' => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            'custom' => [
                $request->filled('from') ? Carbon::parse($request->string('from')->toString())->startOfDay() : $now->copy()->startOfMonth(),
                $request->filled('to') ? Carbon::parse($request->string('to')->toString())->endOfDay() : $now->copy()->endOfMonth(),
            ],
            default => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
        };

        return [$from, $to, $period];
    }
}
