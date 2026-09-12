@php
    $grantablePlatforms = $platforms ?? \App\Models\Platform::orderBy('sort_order')->get();
    $grantablePlatforms = $grantablePlatforms->filter(fn ($p) => in_array($p->code, ['android', 'windows', 'web', 'pwa']));
@endphp

<div class="card p-6">
    <h2 class="text-h3">Customer Access</h2>
    <p class="text-small mt-1">Every purchase creates rows here automatically. Manual grants/revokes are admin overrides (§36) and are stamped with who did it and when.</p>

    <div class="mt-4 divide-y divide-border">
        @forelse ($app->entitlements as $entitlement)
            <div class="flex flex-wrap items-center justify-between gap-3 py-3">
                <div>
                    <p class="font-semibold">
                        {{ $entitlement->customer_email }}
                        <span class="text-small font-normal">— {{ $entitlement->platform->name }} ({{ $entitlement->access_type === 'download' ? 'download' : 'web access' }})</span>
                        @if ($entitlement->status === 'revoked')
                            <span class="badge bg-red-50 text-red-700">Revoked</span>
                        @else
                            <span class="badge bg-cta-50 text-cta-600">Active</span>
                        @endif
                    </p>
                    <p class="text-small mt-0.5">
                        {{ $entitlement->edition?->name ?? '—' }} &middot; {{ $entitlement->source === 'admin_grant' ? 'Admin grant' : 'Purchase' }}
                        @if ($entitlement->order_id)
                            &middot; <a href="{{ route('admin.orders.show', $entitlement->order_id) }}" class="font-semibold text-niagara-600 hover:underline">Order #{{ $entitlement->order_id }}</a>
                        @endif
                        @if ($entitlement->status === 'revoked' && $entitlement->revoked_reason)
                            &middot; {{ $entitlement->revoked_reason }}
                        @endif
                    </p>
                </div>
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
            </div>
        @empty
            <p class="text-small py-3">No customer access records yet.</p>
        @endforelse
    </div>

    <form method="post" action="{{ route('admin.apps.entitlements.store', $app) }}" class="mt-4 grid gap-3 border-t border-border pt-4 sm:grid-cols-4">
        @csrf
        <input type="email" name="email" placeholder="customer@example.com" required class="rounded-lg border border-border-strong px-3 py-2 sm:col-span-2">
        <select name="app_edition_id" class="rounded-lg border border-border-strong px-3 py-2">
            <option value="">— Grant a single platform instead —</option>
            @foreach ($app->editions as $edition)
                <option value="{{ $edition->id }}">{{ $edition->name }}</option>
            @endforeach
        </select>
        <select name="platform_id" class="rounded-lg border border-border-strong px-3 py-2">
            <option value="">Platform (if no edition chosen)</option>
            @foreach ($grantablePlatforms as $platform)
                <option value="{{ $platform->id }}">{{ $platform->name }}</option>
            @endforeach
        </select>
        <select name="access_type" class="rounded-lg border border-border-strong px-3 py-2">
            <option value="download">Download</option>
            <option value="web_access">Web access</option>
        </select>
        <div class="sm:col-span-4">
            <button type="submit" class="btn btn-secondary">Grant Access</button>
        </div>
    </form>
</div>
