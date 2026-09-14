<x-admin-layout :title="'License — '.$license->customer_email">
    <a href="{{ route('admin.licenses.index') }}" class="text-small font-semibold text-niagara-600 hover:underline">&larr; Back to licenses</a>

    <div class="mt-4 grid gap-6 lg:grid-cols-[2fr_1fr]">
        <div class="space-y-6">
            <div class="card p-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-h3">{{ $license->app->name }} — {{ str_replace('_', ' ', $license->platform_entitlement) }}</h2>
                    <span class="badge {{ $license->status === 'active' ? 'badge-brand' : '' }}">{{ $license->status }}</span>
                </div>
                <p class="text-small mt-1">Purchased {{ $license->purchase_date?->format('F j, Y, g:ia') }}</p>

                @if (session('status'))
                    <x-alert type="success" class="mt-3">{{ session('status') }}</x-alert>
                @endif
                @if ($errors->any())
                    <x-alert type="error" class="mt-3">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </x-alert>
                @endif

                <dl class="text-small mt-4 grid grid-cols-2 gap-3">
                    <div><dt class="text-label">Customer</dt><dd>{{ $license->customer_name }}<br>{{ $license->customer_email }}</dd></div>
                    <div><dt class="text-label">Amount paid</dt><dd>${{ number_format($license->price_paid_cents / 100, 2) }} {{ $license->currency }}</dd></div>
                    <div><dt class="text-label">Transaction ID</dt><dd>{{ $license->transaction_id ?? '—' }}</dd></div>
                    <div><dt class="text-label">Payment provider</dt><dd>{{ ucfirst($license->payment_provider) }}</dd></div>
                    <div><dt class="text-label">Order</dt><dd>@if ($license->order)<a href="{{ route('admin.orders.show', $license->order) }}" class="text-niagara-600 hover:underline">{{ $license->order->order_number }}</a>@else — @endif</dd></div>
                    <div><dt class="text-label">Max devices</dt><dd>{{ $license->maximum_devices }}</dd></div>
                </dl>

                <div class="mt-5 flex flex-wrap gap-2">
                    <form method="post" action="{{ route('admin.licenses.resend-email', $license) }}">
                        @csrf
                        <button type="submit" class="btn btn-secondary text-xs">Resend License Email</button>
                    </form>
                    <form method="post" action="{{ route('admin.licenses.reset-activations', $license) }}">
                        @csrf
                        <button type="submit" class="btn btn-secondary text-xs" onclick="return confirm('Deactivate every device on this license?');">Reset Activations</button>
                    </form>
                    @if ($license->status === 'active')
                        <form method="post" action="{{ route('admin.licenses.revoke', $license) }}">
                            @csrf
                            <button type="submit" class="btn btn-ghost text-xs text-red-600" onclick="return confirm('Revoke this license?');">Revoke</button>
                        </form>
                        <form method="post" action="{{ route('admin.licenses.mark-refunded', $license) }}">
                            @csrf
                            <button type="submit" class="btn btn-ghost text-xs text-red-600" onclick="return confirm('Mark this license as refunded?');">Mark Refunded</button>
                        </form>
                    @else
                        <form method="post" action="{{ route('admin.licenses.restore', $license) }}">
                            @csrf
                            <button type="submit" class="btn btn-secondary text-xs">Restore</button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="card p-6">
                <h2 class="text-h3">Activated Devices</h2>
                <ul class="mt-3 divide-y divide-border">
                    @forelse ($license->devices as $device)
                        <li class="flex items-center justify-between gap-3 py-2 text-small">
                            <span>
                                {{ $device->label ?? ucfirst($device->platform) }} ({{ ucfirst($device->platform) }})
                                &mdash; {{ $device->status === 'active' ? 'activated' : 'deactivated' }}
                                {{ ($device->status === 'active' ? $device->activated_at : $device->deactivated_at)?->format('M j, Y') }}
                                @if ($device->app_version) &mdash; v{{ $device->app_version }} @endif
                            </span>
                            @if ($device->status === 'active')
                                <form method="post" action="{{ route('admin.license-devices.deactivate', $device) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-ghost text-xs text-red-600">Deactivate</button>
                                </form>
                            @endif
                        </li>
                    @empty
                        <li class="text-small py-2">No devices activated yet.</li>
                    @endforelse
                </ul>
            </div>

            <div class="card p-6">
                <h2 class="text-h3">History</h2>
                <ul class="mt-3 divide-y divide-border">
                    @forelse ($license->events as $event)
                        <li class="py-2 text-small">
                            {{ $event->created_at->format('M j, Y g:ia') }} &mdash; {{ str_replace('_', ' ', $event->type) }}
                            @if ($event->platform) ({{ $event->platform }}) @endif
                            @if ($event->ip_address) &mdash; {{ $event->ip_address }} @endif
                        </li>
                    @empty
                        <li class="text-small py-2">No activity yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <aside class="space-y-6">
            <div class="card p-6">
                <h2 class="text-h3">License</h2>
                <dl class="text-small mt-3 space-y-2">
                    <div><dt class="text-label">App</dt><dd>{{ $license->app->name }}</dd></div>
                    <div><dt class="text-label">Edition</dt><dd>{{ $license->edition?->name ?? '—' }}</dd></div>
                    <div><dt class="text-label">Entitlement</dt><dd>{{ str_replace('_', ' ', $license->platform_entitlement) }}</dd></div>
                    <div><dt class="text-label">Created</dt><dd>{{ $license->created_at->format('F j, Y') }}</dd></div>
                </dl>
            </div>
        </aside>
    </div>
</x-admin-layout>
