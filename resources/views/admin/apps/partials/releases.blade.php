@php
    $releasePlatforms = $platforms->filter(fn ($p) => in_array($p->code, ['android', 'windows']));
@endphp

<div class="card p-6">
    <h2 class="text-h3">Releases</h2>
    <p class="text-small mt-1">The file customers actually receive per platform (§17). An entitlement always resolves to whichever release below is marked <strong>Current</strong> — not to a specific historical upload (§24).</p>

    <div class="mt-4 divide-y divide-border">
        @forelse ($app->releases as $release)
            <div class="flex flex-wrap items-center justify-between gap-3 py-3">
                <div>
                    <p class="font-semibold">
                        {{ $release->platform->name }} {{ $release->version }}
                        @if ($release->is_current)
                            <span class="badge bg-cta-50 text-cta-600">Current</span>
                        @endif
                        @unless ($release->customer_downloadable)
                            <span class="badge bg-red-50 text-red-700">Developer / Store Publishing Only — Never Customer Download</span>
                        @endunless
                    </p>
                    <p class="text-small mt-0.5">{{ $release->original_filename }} &middot; {{ $release->fileSizeLabel() }} &middot; released {{ $release->released_at->format('M j, Y') }}</p>
                </div>
                <div class="flex gap-2">
                    @if (! $release->is_current && $release->customer_downloadable)
                        <form method="post" action="{{ route('admin.apps.releases.activate', [$app, $release]) }}">
                            @csrf
                            <button type="submit" class="btn btn-secondary text-xs">Make Current</button>
                        </form>
                    @endif
                    <form method="post" action="{{ route('admin.apps.releases.destroy', [$app, $release]) }}" onsubmit="return confirm('Delete this release file?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-ghost text-xs text-red-600">Delete</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="text-small py-3">No releases uploaded yet.</p>
        @endforelse
    </div>

    @error('file')
        <p class="text-small mt-3 text-red-600">{{ $message }}</p>
    @enderror

    <form method="post" action="{{ route('admin.apps.releases.store', $app) }}" enctype="multipart/form-data" class="mt-4 grid gap-3 border-t border-border pt-4 sm:grid-cols-2">
        @csrf
        <x-admin.form-field name="platform_id" label="Platform">
            <select name="platform_id" required class="{{ $inputClass }}">
                @foreach ($releasePlatforms as $platform)
                    <option value="{{ $platform->id }}">{{ $platform->name }}</option>
                @endforeach
            </select>
        </x-admin.form-field>
        <x-admin.form-field name="version" label="Version">
            <input name="version" required placeholder="1.3.0" class="{{ $inputClass }}">
        </x-admin.form-field>
        <x-admin.form-field name="build_number" label="Build number (optional)">
            <input name="build_number" class="{{ $inputClass }}">
        </x-admin.form-field>
        <x-admin.form-field name="min_os" label="Minimum OS (optional)">
            <input name="min_os" placeholder="e.g. Android 8.0+" class="{{ $inputClass }}">
        </x-admin.form-field>
        <x-admin.form-field name="released_at" label="Release date">
            <input type="date" name="released_at" value="{{ now()->toDateString() }}" class="{{ $inputClass }}">
        </x-admin.form-field>
        <x-admin.form-field name="file" label="File" hint="Android: .apk (customer-downloadable) or .aab (forced internal-only). Windows: .exe, .msix, or .msixbundle.">
            <input type="file" name="file" required class="{{ $inputClass }}">
        </x-admin.form-field>
        <x-admin.form-field name="release_notes" label="Release notes (optional)" class="sm:col-span-2">
            <textarea name="release_notes" rows="2" class="{{ $inputClass }}"></textarea>
        </x-admin.form-field>
        <label class="flex items-center gap-2 text-nav sm:col-span-2">
            <input type="checkbox" name="internal_only" value="1" class="rounded border-border-strong">
            For internal/developer record only — never customer-downloadable (required for an .aab)
        </label>
        <div class="sm:col-span-2">
            <button type="submit" class="btn btn-secondary">Upload Release</button>
        </div>
    </form>
</div>
