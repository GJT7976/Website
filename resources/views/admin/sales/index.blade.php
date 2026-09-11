<x-admin-layout title="Sales">
    <form method="get" class="flex flex-wrap items-end gap-2">
        <div class="flex gap-2">
            @foreach (['today' => 'Today', 'month' => 'This Month', 'quarter' => 'This Quarter', 'year' => 'This Year', 'custom' => 'Custom'] as $value => $label)
                <a href="{{ route('admin.sales.index', ['period' => $value]) }}" class="badge {{ $period === $value ? 'badge-brand' : '' }}">{{ $label }}</a>
            @endforeach
        </div>
        @if ($period === 'custom')
            <input type="date" name="from" value="{{ request('from', $from->toDateString()) }}" class="rounded-lg border border-border-strong px-3 py-2 text-sm">
            <input type="date" name="to" value="{{ request('to', $to->toDateString()) }}" class="rounded-lg border border-border-strong px-3 py-2 text-sm">
            <input type="hidden" name="period" value="custom">
            <button type="submit" class="btn btn-secondary">Apply</button>
        @endif
        <a href="{{ route('admin.sales.export', request()->query()) }}" class="btn btn-secondary ms-auto">Export CSV</a>
    </form>

    <p class="text-small mt-2">{{ $from->format('M j, Y') }} &ndash; {{ $to->format('M j, Y') }}</p>

    <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-5">
        <x-admin.metric-card label="Orders" :value="$totals['order_count']" icon="🧾" />
        <x-admin.metric-card label="Gross Sales" :value="'$'.number_format($totals['gross_cents'] / 100, 2)" icon="💰" />
        <x-admin.metric-card label="Tax Collected" :value="'$'.number_format($totals['tax_cents'] / 100, 2)" icon="🏛️" />
        <x-admin.metric-card label="Refunded" :value="'$'.number_format($totals['refunded_cents'] / 100, 2)" icon="↩️" />
        <x-admin.metric-card label="Net Sales" :value="'$'.number_format($totals['net_cents'] / 100, 2)" icon="📈" />
    </div>

    <x-alert type="info" class="mt-6">
        This is a basic sales view — gross/tax/refunds/net for paid orders in the selected period. Fiscal-year exports, by-province/by-country breakdowns, Stripe processing fees, and expense tracking are a further phase (see <code>ARCHITECTURE.md</code>).
    </x-alert>

    <x-admin.data-table class="mt-6">
        <table>
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Subtotal</th>
                    <th>Tax</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td><a href="{{ route('admin.orders.show', $order) }}" class="font-semibold hover:text-niagara-600">{{ $order->order_number }}</a></td>
                        <td class="text-small">{{ $order->customer_name }}</td>
                        <td class="text-small">{{ $order->moneyLabel($order->subtotal_cents) }}</td>
                        <td class="text-small">{{ $order->moneyLabel($order->tax_cents) }}</td>
                        <td class="text-small">{{ $order->moneyLabel($order->total_cents) }}</td>
                        <td><span class="badge {{ $order->payment_status === 'paid' ? 'badge-brand' : '' }}">{{ str_replace('_', ' ', $order->payment_status) }}</span></td>
                        <td class="text-small">{{ $order->created_at->format('M j, Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-8 text-small">No orders in this period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-admin.data-table>
</x-admin-layout>
