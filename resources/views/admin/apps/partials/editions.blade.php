@php
    // Only platforms that make sense to grant, and how — a download grant
    // for android/windows, web access for web/pwa. Keeps the checkbox list
    // from offering nonsensical combinations like "iOS download".
    $grantablePlatforms = $platforms->filter(fn ($p) => in_array($p->code, ['android', 'windows', 'web', 'pwa']));
    $accessTypeFor = fn ($code) => in_array($code, ['web', 'pwa']) ? 'web_access' : 'download';
@endphp

<div class="card p-6">
    <h2 class="text-h3">Editions</h2>
    <p class="text-small mt-1">What a customer can choose to buy for this app (§11) — Android, Windows, a bundle, Web, Complete, or any combination. An app with no active editions here falls back to the single flat price above.</p>

    <div class="mt-4 space-y-4">
        @forelse ($app->editions as $edition)
            <details class="rounded-lg border border-border p-4">
                <summary class="flex cursor-pointer items-center justify-between gap-3">
                    <span class="font-semibold">
                        {{ $edition->name }}
                        <span class="text-small font-normal">— {{ $edition->priceLabel() }}</span>
                        @unless ($edition->active)
                            <span class="badge bg-mist">Inactive</span>
                        @endunless
                        @if ($edition->featured)
                            <span class="badge bg-cta-50 text-cta-600">Best Value</span>
                        @endif
                    </span>
                    <span class="text-small">{{ collect($edition->includedPlatforms())->pluck('platform_name')->unique()->implode(', ') ?: 'No platforms granted yet' }}</span>
                </summary>

                <form method="post" action="{{ route('admin.apps.editions.update', [$app, $edition]) }}" class="mt-4 grid gap-3 sm:grid-cols-2">
                    @csrf @method('PUT')
                    <x-admin.form-field name="name" label="Name">
                        <input name="name" value="{{ $edition->name }}" required class="{{ $inputClass }}">
                    </x-admin.form-field>
                    <x-admin.form-field name="slug" label="Slug">
                        <input name="slug" value="{{ $edition->slug }}" class="{{ $inputClass }}">
                    </x-admin.form-field>
                    <x-admin.form-field name="price" label="Price">
                        <input type="number" step="0.01" min="0" name="price" value="{{ $edition->price_cents / 100 }}" required class="{{ $inputClass }}">
                    </x-admin.form-field>
                    <x-admin.form-field name="sort_order" label="Sort order">
                        <input type="number" min="0" name="sort_order" value="{{ $edition->sort_order }}" class="{{ $inputClass }}">
                    </x-admin.form-field>
                    <x-admin.form-field name="description" label="Description (optional — a sensible default is used otherwise)" class="sm:col-span-2">
                        <input name="description" value="{{ $edition->description }}" class="{{ $inputClass }}">
                    </x-admin.form-field>
                    <label class="flex items-center gap-2 text-nav">
                        <input type="checkbox" name="active" value="1" @checked($edition->active) class="rounded border-border-strong"> Active (visible to customers)
                    </label>
                    <label class="flex items-center gap-2 text-nav">
                        <input type="checkbox" name="featured" value="1" @checked($edition->featured) class="rounded border-border-strong"> "Best Value" badge
                    </label>
                    <div class="sm:col-span-2">
                        <button type="submit" class="btn btn-secondary">Save Edition</button>
                    </div>
                </form>

                <form method="post" action="{{ route('admin.apps.editions.entitlements', [$app, $edition]) }}" class="mt-4 border-t border-border pt-4">
                    @csrf
                    <p class="text-label">Includes</p>
                    <div class="mt-2 flex flex-wrap gap-4">
                        @foreach ($grantablePlatforms as $platform)
                            @php
                                $accessType = $accessTypeFor($platform->code);
                                $granted = $edition->entitlements->contains(fn ($e) => $e->platform_id === $platform->id && $e->access_type === $accessType);
                            @endphp
                            <label class="flex items-center gap-2 text-nav">
                                <input type="checkbox" name="grants[]" value="{{ $platform->id }}:{{ $accessType }}" @checked($granted) class="rounded border-border-strong">
                                {{ $platform->name }} ({{ $accessType === 'download' ? 'download' : 'web access' }})
                            </label>
                        @endforeach
                    </div>
                    <button type="submit" class="btn btn-secondary mt-3">Save What's Included</button>
                </form>

                <form method="post" action="{{ route('admin.apps.editions.destroy', [$app, $edition]) }}" class="mt-4 border-t border-border pt-4" onsubmit="return confirm('Delete this edition? Past orders keep their snapshot regardless.');">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-ghost text-xs text-red-600">Delete Edition</button>
                </form>
            </details>
        @empty
            <p class="text-small">No editions yet — this app is sold at its single flat price above.</p>
        @endforelse
    </div>

    <form method="post" action="{{ route('admin.apps.editions.store', $app) }}" class="mt-6 grid gap-3 border-t border-border pt-4 sm:grid-cols-4">
        @csrf
        <input type="text" name="name" placeholder="e.g. Android + Windows Bundle" required class="rounded-lg border border-border-strong px-3 py-2 sm:col-span-2">
        <input type="number" step="0.01" min="0" name="price" placeholder="Price" required class="rounded-lg border border-border-strong px-3 py-2">
        <button type="submit" class="btn btn-secondary">Add Edition</button>
    </form>
</div>
