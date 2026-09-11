<x-admin-layout title="Dashboard">
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <x-admin.metric-card label="Sales Today" :value="'$'.number_format($salesTodayCents / 100, 2)" icon="💰" />
        <x-admin.metric-card label="Sales This Month" :value="'$'.number_format($salesMonthCents / 100, 2)" icon="📈" />
        <x-admin.metric-card label="Pending Orders" :value="$pendingOrderCount" icon="⏳" />
        <x-admin.metric-card label="Published Apps" :value="$publishedCount" icon="✅" />
    </div>

    <div class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <x-admin.metric-card label="Draft Apps" :value="$draftCount" icon="📝" />
        <x-admin.metric-card label="Archived Apps" :value="$archivedCount" icon="🗄️" />
        <x-admin.metric-card label="Demo-Enabled Apps" :value="$demoEnabledCount" icon="🧪" />
        <x-admin.metric-card label="New Support Messages" :value="$newSupportCount" icon="✉️" />
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-3">
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <h2 class="text-h3">Recent Orders</h2>
                <a href="{{ route('admin.orders.index') }}" class="text-small font-semibold text-niagara-600 hover:underline">View all &rarr;</a>
            </div>
            <ul class="mt-4 divide-y divide-border">
                @forelse ($recentOrders as $order)
                    <li class="py-3">
                        <a href="{{ route('admin.orders.show', $order) }}" class="flex items-center justify-between gap-3 hover:text-niagara-600">
                            <span class="truncate text-small">{{ $order->order_number }}</span>
                            <span class="badge shrink-0 {{ $order->payment_status === 'paid' ? 'badge-brand' : '' }}">{{ str_replace('_', ' ', $order->payment_status) }}</span>
                        </a>
                    </li>
                @empty
                    <li class="text-small py-3">No orders yet.</li>
                @endforelse
            </ul>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between">
                <h2 class="text-h3">Support Inbox</h2>
                <a href="{{ route('admin.support.index') }}" class="text-small font-semibold text-niagara-600 hover:underline">View all &rarr;</a>
            </div>
            <p class="text-small mt-1">{{ $newSupportCount }} new message{{ $newSupportCount === 1 ? '' : 's' }}</p>
            <ul class="mt-4 divide-y divide-border">
                @forelse ($recentSupportRequests as $request)
                    <li class="py-3">
                        <a href="{{ route('admin.support.show', $request) }}" class="flex items-center justify-between gap-3 hover:text-niagara-600">
                            <span class="truncate">
                                <span class="font-semibold">{{ $request->name }}</span>
                                <span class="text-small">— {{ $request->subject ?: 'No subject' }}</span>
                            </span>
                            <span class="badge shrink-0">{{ $request->status }}</span>
                        </a>
                    </li>
                @empty
                    <li class="text-small py-3">No support requests yet.</li>
                @endforelse
            </ul>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between">
                <h2 class="text-h3">Recently Updated Apps</h2>
                <a href="{{ route('admin.apps.index') }}" class="text-small font-semibold text-niagara-600 hover:underline">View all &rarr;</a>
            </div>
            <ul class="mt-4 divide-y divide-border">
                @forelse ($recentApps as $app)
                    <li class="py-3">
                        <a href="{{ route('admin.apps.edit', $app) }}" class="flex items-center justify-between gap-3 hover:text-niagara-600">
                            <span>{{ $app->name }}</span>
                            <span class="badge">{{ $app->status }}</span>
                        </a>
                    </li>
                @empty
                    <li class="text-small py-3">No apps yet — <a href="{{ route('admin.apps.create') }}" class="font-semibold text-niagara-600 hover:underline">add your first one</a>.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <div class="mt-8">
        <x-alert type="info">
            Accounting (fiscal-year exports, by-province/country breakdowns, Stripe fees), Expense tracking, Backups, and the Audit Log are a further phase and not implemented yet — no figures are shown for them so nothing here is fabricated.
        </x-alert>
    </div>
</x-admin-layout>
