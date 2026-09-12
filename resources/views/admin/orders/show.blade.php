<x-admin-layout :title="'Order '.$order->order_number">
    <a href="{{ route('admin.orders.index') }}" class="text-small font-semibold text-niagara-600 hover:underline">&larr; Back to orders</a>

    <div class="mt-4 grid gap-6 lg:grid-cols-[2fr_1fr]">
        <div class="space-y-6">
            <div class="card p-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-h3">Order {{ $order->order_number }}</h2>
                    <span class="badge {{ $order->payment_status === 'paid' ? 'badge-brand' : '' }}">{{ str_replace('_', ' ', $order->payment_status) }}</span>
                </div>
                <p class="text-small mt-1">{{ $order->created_at->format('F j, Y, g:ia') }}</p>

                <form method="post" action="{{ route('admin.orders.resend-receipt', $order) }}" class="mt-3">
                    @csrf
                    <button type="submit" class="btn btn-secondary text-xs">Resend Receipt Email</button>
                </form>

                <table class="mt-5 w-full text-sm">
                    <thead>
                        <tr class="text-label border-b border-border text-left">
                            <th class="pb-2">Item</th>
                            <th class="pb-2 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->items as $item)
                            <tr class="border-b border-border">
                                <td class="py-2">
                                    {{ $item->app_name_snapshot }}@if ($item->edition_name_snapshot) — {{ $item->edition_name_snapshot }}@endif &times; {{ $item->quantity }}
                                    @if ($item->included_platforms_snapshot)
                                        <br><span class="text-small">Includes: {{ collect($item->included_platforms_snapshot)->pluck('platform_name')->unique()->implode(', ') }}</span>
                                    @endif
                                </td>
                                <td class="py-2 text-right">{{ $order->moneyLabel($item->line_subtotal_cents) }}</td>
                            </tr>
                        @endforeach
                        <tr class="border-b border-border">
                            <td class="py-2">Subtotal</td>
                            <td class="py-2 text-right">{{ $order->moneyLabel($order->subtotal_cents) }}</td>
                        </tr>
                        @foreach ($order->taxLines as $line)
                            <tr class="border-b border-border">
                                <td class="py-2">{{ $line->tax_name_snapshot }} ({{ rtrim(rtrim($line->percentage_snapshot, '0'), '.') }}%)</td>
                                <td class="py-2 text-right">{{ $order->moneyLabel($line->amount_cents) }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td class="py-2 font-semibold">Total</td>
                            <td class="py-2 text-right font-semibold">{{ $order->moneyLabel($order->total_cents) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="card p-6">
                <h2 class="text-h3">Payments</h2>
                <ul class="mt-3 divide-y divide-border">
                    @forelse ($order->payments as $payment)
                        <li class="flex justify-between py-2 text-small">
                            <span>{{ $payment->created_at->format('M j, Y g:ia') }} &mdash; {{ $payment->provider_payment_id }}</span>
                            <span>{{ $order->moneyLabel($payment->amount_cents) }} ({{ $payment->status }})</span>
                        </li>
                    @empty
                        <li class="text-small py-2">No confirmed payments yet — payment status updates arrive via the Stripe webhook.</li>
                    @endforelse
                </ul>
            </div>

            <div class="card p-6">
                <h2 class="text-h3">Refunds</h2>
                <ul class="mt-3 divide-y divide-border">
                    @forelse ($order->refunds as $refund)
                        <li class="py-2 text-small">
                            {{ $refund->created_at->format('M j, Y g:ia') }} &mdash; {{ $order->moneyLabel($refund->amount_cents) }}
                            @if ($refund->administrator)&mdash; issued by {{ $refund->administrator->name }}@endif
                            @if ($refund->reason) &mdash; "{{ $refund->reason }}"@endif
                        </li>
                    @empty
                        <li class="text-small py-2">No refunds.</li>
                    @endforelse
                </ul>

                @if (auth()->user()->isOwner() && $order->payment_status === 'paid' && $order->stripe_payment_intent_id)
                    <form method="post" action="{{ route('admin.orders.refund', $order) }}" class="mt-4 grid gap-2 sm:grid-cols-[auto_1fr_auto]">
                        @csrf
                        <input type="number" step="0.01" min="0.01" name="amount" placeholder="Full amount" class="rounded-lg border border-border-strong px-3 py-2 text-sm">
                        <input type="text" name="reason" placeholder="Reason (optional)" class="rounded-lg border border-border-strong px-3 py-2 text-sm">
                        <button type="submit" class="btn btn-secondary" onclick="return confirm('Issue this refund via Stripe?');">Issue Refund</button>
                    </form>
                @endif
            </div>
            <div class="card p-6">
                <h2 class="text-h3">Customer Access</h2>
                <ul class="mt-3 divide-y divide-border">
                    @forelse ($order->entitlements as $entitlement)
                        <li class="flex items-center justify-between gap-3 py-2 text-small">
                            <span>
                                {{ $entitlement->platform->name }} ({{ $entitlement->access_type === 'download' ? 'download' : 'web access' }})
                                @if ($entitlement->status === 'revoked')
                                    <span class="badge bg-red-50 text-red-700">Revoked</span>
                                @else
                                    <span class="badge bg-cta-50 text-cta-600">Active</span>
                                @endif
                            </span>
                            @if ($entitlement->status === 'active')
                                <form method="post" action="{{ route('admin.entitlements.revoke', $entitlement) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-ghost text-xs text-red-600">Revoke</button>
                                </form>
                            @else
                                <form method="post" action="{{ route('admin.entitlements.restore', $entitlement) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-secondary text-xs">Restore</button>
                                </form>
                            @endif
                        </li>
                    @empty
                        <li class="text-small py-2">No download/web-access entitlements for this order yet — created automatically once payment is confirmed.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <aside class="space-y-6">
            <div class="card p-6">
                <h2 class="text-h3">Billing</h2>
                <dl class="text-small mt-3 space-y-2">
                    <div><dt class="text-label">Name</dt><dd>{{ $order->customer_name }}</dd></div>
                    <div><dt class="text-label">Email</dt><dd>{{ $order->customer_email }}</dd></div>
                    @if ($order->billing_company)
                        <div><dt class="text-label">Company</dt><dd>{{ $order->billing_company }}</dd></div>
                    @endif
                    <div>
                        <dt class="text-label">Address</dt>
                        <dd>{{ $order->billing_address }}<br>{{ $order->billing_city }}, {{ $order->billing_province }} {{ $order->billing_postal_code }}<br>{{ $order->billing_country }}</dd>
                    </div>
                </dl>
            </div>
        </aside>
    </div>
</x-admin-layout>
