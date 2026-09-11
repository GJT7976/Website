<x-admin-layout title="Orders">
    <form method="get" class="flex flex-wrap gap-2">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Order #, name, or email…" class="rounded-lg border border-border-strong px-3 py-2 text-sm">
        <select name="status" onchange="this.form.submit()" class="rounded-lg border border-border-strong px-3 py-2 text-sm">
            <option value="">All statuses</option>
            @foreach (['pending', 'paid', 'failed', 'refunded', 'partially_refunded'] as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-secondary">Filter</button>
    </form>

    <x-admin.data-table class="mt-4">
        <table>
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>App(s)</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td class="font-semibold">{{ $order->order_number }}</td>
                        <td class="text-small">{{ $order->customer_name }}<br>{{ $order->customer_email }}</td>
                        <td class="text-small">{{ $order->items->pluck('app_name_snapshot')->implode(', ') }}</td>
                        <td class="text-small">{{ $order->moneyLabel($order->total_cents) }}</td>
                        <td><span class="badge {{ $order->payment_status === 'paid' ? 'badge-brand' : '' }}">{{ str_replace('_', ' ', $order->payment_status) }}</span></td>
                        <td class="text-small">{{ $order->created_at->format('M j, Y') }}</td>
                        <td class="text-right"><a href="{{ route('admin.orders.show', $order) }}" class="btn btn-ghost !px-3 !py-1.5 text-xs">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-8 text-small">No orders yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-admin.data-table>

    <div class="mt-6">{{ $orders->links() }}</div>
</x-admin-layout>
