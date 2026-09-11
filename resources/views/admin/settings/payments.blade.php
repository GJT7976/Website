@php
    $groupLabels = ['business' => 'Business Settings', 'site' => 'Site Settings', 'store' => 'Store Settings', 'payments' => 'Payments'];
@endphp

<x-admin-layout title="Payments">
    <div class="flex gap-2">
        @foreach (['business', 'site', 'store', 'payments'] as $g)
            <a href="{{ route('admin.settings.edit', $g) }}" class="badge {{ $g === 'payments' ? 'badge-brand' : '' }}">{{ $groupLabels[$g] }}</a>
        @endforeach
    </div>

    <div class="card mt-6 max-w-2xl p-6">
        <h2 class="text-h3">Stripe configuration status</h2>
        <p class="text-small mt-1">Stripe keys are set in the server's <code>.env</code> file, not here — never displayed in full, and never editable from the admin.</p>

        <dl class="mt-5 space-y-3">
            <div class="flex items-center justify-between border-b border-border pb-3">
                <dt class="text-nav">Publishable key (<code>STRIPE_KEY</code>)</dt>
                <dd><span class="badge {{ $keyConfigured ? 'badge-brand' : '' }}">{{ $keyConfigured ? 'Configured' : 'Not set' }}</span></dd>
            </div>
            <div class="flex items-center justify-between border-b border-border pb-3">
                <dt class="text-nav">Secret key (<code>STRIPE_SECRET</code>)</dt>
                <dd><span class="badge {{ $secretConfigured ? 'badge-brand' : '' }}">{{ $secretConfigured ? 'Configured' : 'Not set' }}</span></dd>
            </div>
            <div class="flex items-center justify-between border-b border-border pb-3">
                <dt class="text-nav">Webhook secret (<code>STRIPE_WEBHOOK_SECRET</code>)</dt>
                <dd><span class="badge {{ $webhookConfigured ? 'badge-brand' : '' }}">{{ $webhookConfigured ? 'Configured' : 'Not set' }}</span></dd>
            </div>
            <div class="flex items-center justify-between">
                <dt class="text-nav">Mode</dt>
                <dd>
                    @if ($mode === 'live')
                        <span class="badge" style="background:#fee2e2;color:#991b1b;border-color:#fecaca;">Live — real charges</span>
                    @elseif ($mode === 'test')
                        <span class="badge badge-water">Test mode</span>
                    @else
                        <span class="badge">Unknown</span>
                    @endif
                </dd>
            </div>
        </dl>

        @if (! $keyConfigured || ! $secretConfigured)
            <x-alert type="info" class="mt-5">
                Direct purchasing won't work until real Stripe keys are added to <code>.env</code> — see <code>STRIPE_SETUP.md</code>. Any app with "Enable direct in-browser purchase" turned on will show a friendly error instead of a broken checkout until then.
            </x-alert>
        @endif
        @if (! $webhookConfigured)
            <x-alert type="info" class="mt-3">
                Without <code>STRIPE_WEBHOOK_SECRET</code>, successful payments won't be confirmed automatically — orders will stay "pending" even after a customer pays. Set this up before enabling purchases on a real app.
            </x-alert>
        @endif
    </div>
</x-admin-layout>
