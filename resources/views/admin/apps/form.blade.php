@php
    $isEdit = $app->exists;
    $inputClass = 'w-full rounded-lg border border-border-strong px-3 py-2 focus:border-niagara-500 focus:outline-none';
@endphp

<x-admin-layout :title="$isEdit ? 'Edit '.$app->name : 'Add App'">
    <form method="post" action="{{ $isEdit ? route('admin.apps.update', $app) : route('admin.apps.store') }}" class="space-y-8">
        @csrf
        @if ($isEdit) @method('PUT') @endif

        {{-- CORE --}}
        <section class="card p-6">
            <h2 class="text-h3">Core Details</h2>
            <div class="mt-4 grid gap-5 sm:grid-cols-2">
                <x-admin.form-field name="name" label="App name">
                    <input id="name" name="name" value="{{ old('name', $app->name) }}" required class="{{ $inputClass }}">
                </x-admin.form-field>
                <x-admin.form-field name="slug" label="Slug" hint="Leave blank to auto-generate from the name.">
                    <input id="slug" name="slug" value="{{ old('slug', $app->slug) }}" class="{{ $inputClass }}">
                </x-admin.form-field>
                <x-admin.form-field name="tagline" label="Tagline" class="sm:col-span-2">
                    <input id="tagline" name="tagline" value="{{ old('tagline', $app->tagline) }}" class="{{ $inputClass }}">
                </x-admin.form-field>
                <x-admin.form-field name="short_description" label="Short description" class="sm:col-span-2">
                    <textarea id="short_description" name="short_description" rows="2" class="{{ $inputClass }}">{{ old('short_description', $app->short_description) }}</textarea>
                </x-admin.form-field>
                <x-admin.form-field name="long_description" label="Long description (Overview)" class="sm:col-span-2">
                    <textarea id="long_description" name="long_description" rows="8" class="{{ $inputClass }}">{{ old('long_description', $app->long_description) }}</textarea>
                </x-admin.form-field>
                <x-admin.form-field name="category_id" label="Category">
                    <select id="category_id" name="category_id" class="{{ $inputClass }}">
                        <option value="">— None —</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $app->category_id) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </x-admin.form-field>
                <x-admin.form-field name="status" label="Status">
                    <select id="status" name="status" class="{{ $inputClass }}">
                        @foreach (['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $app->status ?? 'draft') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </x-admin.form-field>
                <x-admin.form-field name="version" label="Version">
                    <input id="version" name="version" value="{{ old('version', $app->version) }}" class="{{ $inputClass }}">
                </x-admin.form-field>
                <x-admin.form-field name="release_date" label="Release date">
                    <input type="date" id="release_date" name="release_date" value="{{ old('release_date', $app->release_date?->format('Y-m-d')) }}" class="{{ $inputClass }}">
                </x-admin.form-field>
                <x-admin.form-field name="updated_on" label="Last updated on">
                    <input type="date" id="updated_on" name="updated_on" value="{{ old('updated_on', $app->updated_on?->format('Y-m-d')) }}" class="{{ $inputClass }}">
                </x-admin.form-field>
                <label class="flex items-center gap-2 text-nav">
                    <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $app->is_featured)) class="rounded border-border-strong">
                    Featured (appears on homepage)
                </label>
            </div>
        </section>

        {{-- PLATFORMS --}}
        <section class="card p-6">
            <h2 class="text-h3">Platforms</h2>
            <div class="mt-4 flex flex-wrap gap-4">
                @php $selectedPlatforms = old('platforms', $app->relationLoaded('platforms') ? $app->platforms->pluck('id')->all() : []); @endphp
                @foreach ($platforms as $platform)
                    <label class="flex items-center gap-2 text-nav">
                        <input type="checkbox" name="platforms[]" value="{{ $platform->id }}" @checked(in_array($platform->id, $selectedPlatforms)) class="rounded border-border-strong">
                        {{ $platform->name }}
                    </label>
                @endforeach
            </div>
        </section>

        {{-- PRICING --}}
        <section class="card p-6">
            <h2 class="text-h3">Pricing</h2>
            <div class="mt-4 grid gap-5 sm:grid-cols-3" x-data="{ isFree: {{ old('is_free', $app->is_free) ? 'true' : 'false' }} }">
                <label class="flex items-center gap-2 text-nav sm:col-span-3">
                    <input type="checkbox" name="is_free" value="1" x-model="isFree" class="rounded border-border-strong">
                    This app is free
                </label>
                <template x-if="isFree">
                    <p class="text-small sm:col-span-3" style="margin-top:-0.5rem;">
                        Price fields below are disabled and won't be saved while this is checked — uncheck it to set a price.
                    </p>
                </template>
                <x-admin.form-field name="price" label="Price">
                    <input type="number" step="0.01" min="0" id="price" name="price" :disabled="isFree" :class="{ 'opacity-50': isFree }" value="{{ old('price', $app->price_cents !== null ? $app->price_cents / 100 : '') }}" class="{{ $inputClass }}">
                </x-admin.form-field>
                <x-admin.form-field name="sale_price" label="Sale price (optional)">
                    <input type="number" step="0.01" min="0" id="sale_price" name="sale_price" :disabled="isFree" :class="{ 'opacity-50': isFree }" value="{{ old('sale_price', $app->sale_price_cents !== null ? $app->sale_price_cents / 100 : '') }}" class="{{ $inputClass }}">
                </x-admin.form-field>
                <x-admin.form-field name="currency" label="Currency">
                    <input id="currency" name="currency" maxlength="3" :disabled="isFree" :class="{ 'opacity-50': isFree }" value="{{ old('currency', $app->currency ?? 'CAD') }}" class="{{ $inputClass }}">
                </x-admin.form-field>
            </div>
            <label class="mt-4 flex items-center gap-2 text-nav">
                <input type="checkbox" name="direct_purchase_enabled" value="1" @checked(old('direct_purchase_enabled', $app->direct_purchase_enabled)) class="rounded border-border-strong">
                Enable direct in-browser purchase (Stripe Checkout)
            </label>
            <x-alert type="info" class="mt-3">
                When enabled, a real "Buy Now" button sends buyers to Stripe Checkout, with Canadian tax calculated from Settings &rarr; Taxes. Make sure Stripe keys are configured (Settings &rarr; Payments) before enabling this on a real app. External store links below are always shown when set, regardless of this setting.
            </x-alert>
            <div class="mt-4 grid gap-5 sm:grid-cols-3">
                <x-admin.form-field name="google_play_url" label="Google Play URL">
                    <input id="google_play_url" name="google_play_url" value="{{ old('google_play_url', $app->google_play_url) }}" class="{{ $inputClass }}">
                </x-admin.form-field>
                <x-admin.form-field name="microsoft_store_url" label="Microsoft Store URL">
                    <input id="microsoft_store_url" name="microsoft_store_url" value="{{ old('microsoft_store_url', $app->microsoft_store_url) }}" class="{{ $inputClass }}">
                </x-admin.form-field>
                <x-admin.form-field name="apple_url" label="Apple URL">
                    <input id="apple_url" name="apple_url" value="{{ old('apple_url', $app->apple_url) }}" class="{{ $inputClass }}">
                </x-admin.form-field>
            </div>
        </section>

        {{-- PLATFORM SALES CONFIG — §22 --}}
        <section class="card p-6">
            <h2 class="text-h3">Platform Delivery, License &amp; Updates</h2>
            <p class="text-small mt-1">Only matters once this app has Editions configured below — a single-price app ignores these.</p>
            <div class="mt-4 grid gap-5 sm:grid-cols-2">
                <x-admin.form-field name="android_delivery_mode" label="Android delivery">
                    <select id="android_delivery_mode" name="android_delivery_mode" class="{{ $inputClass }}">
                        @foreach (['none' => 'Not available', 'direct' => 'Direct Download only', 'play' => 'Google Play only', 'both' => 'Both'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('android_delivery_mode', $app->android_delivery_mode ?? 'none') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </x-admin.form-field>
                <x-admin.form-field name="windows_delivery_mode" label="Windows delivery">
                    <select id="windows_delivery_mode" name="windows_delivery_mode" class="{{ $inputClass }}">
                        @foreach (['none' => 'Not available', 'direct' => 'Direct Download only', 'store' => 'Microsoft Store only', 'both' => 'Both'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('windows_delivery_mode', $app->windows_delivery_mode ?? 'none') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </x-admin.form-field>
                <x-admin.form-field name="web_app_url" label="Paid Web App URL" hint="The hosted, purchased app — distinct from the Live Demo above.">
                    <input id="web_app_url" name="web_app_url" value="{{ old('web_app_url', $app->web_app_url) }}" class="{{ $inputClass }}">
                </x-admin.form-field>
                <div class="flex flex-col justify-center gap-2">
                    <label class="flex items-center gap-2 text-nav">
                        <input type="checkbox" name="web_available" value="1" @checked(old('web_available', $app->web_available ?? false)) class="rounded border-border-strong">
                        Paid Web App available
                    </label>
                    <label class="flex items-center gap-2 text-nav">
                        <input type="checkbox" name="web_login_required" value="1" @checked(old('web_login_required', $app->web_login_required ?? false)) class="rounded border-border-strong">
                        Web app requires login
                    </label>
                </div>
                <x-admin.form-field name="license_type" label="License scope">
                    <select id="license_type" name="license_type" class="{{ $inputClass }}">
                        @foreach (['personal' => 'Personal License', 'single_business' => 'Single Business License', 'other' => 'Other (custom label)'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('license_type', $app->license_type ?? 'personal') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </x-admin.form-field>
                <x-admin.form-field name="license_label" label="Custom license label (used when License scope is Other)">
                    <input id="license_label" name="license_label" value="{{ old('license_label', $app->license_label) }}" class="{{ $inputClass }}">
                </x-admin.form-field>
                <x-admin.form-field name="update_policy" label="Update policy">
                    <select id="update_policy" name="update_policy" class="{{ $inputClass }}">
                        @foreach (['updates_included' => 'Updates Included', 'major_upgrades_separate' => 'Major Upgrades Sold Separately'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('update_policy', $app->update_policy ?? 'updates_included') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </x-admin.form-field>
            </div>
        </section>

        {{-- DEMO --}}
        <section class="card p-6">
            <h2 class="text-h3">Demo</h2>
            <div class="mt-4 grid gap-5 sm:grid-cols-2">
                <label class="flex items-center gap-2 text-nav sm:col-span-2">
                    <input type="checkbox" name="demo_enabled" value="1" @checked(old('demo_enabled', $app->demo_enabled)) class="rounded border-border-strong">
                    Demo enabled
                </label>
                <x-admin.form-field name="demo_url" label="Demo URL" hint="e.g. /demo-builds/{slug}/index.html for a static build placed in public/demo-builds/{slug}/. Avoid public/demos/ — it collides with the /demos catalogue route.">
                    <input id="demo_url" name="demo_url" value="{{ old('demo_url', $app->demo_url) }}" class="{{ $inputClass }}">
                </x-admin.form-field>
                <x-admin.form-field name="demo_type" label="Demo type">
                    <input id="demo_type" name="demo_type" value="{{ old('demo_type', $app->demo_type) }}" placeholder="static_web, flutter_web…" class="{{ $inputClass }}">
                </x-admin.form-field>
                <x-admin.form-field name="demo_version" label="Demo version">
                    <input id="demo_version" name="demo_version" value="{{ old('demo_version', $app->demo_version) }}" class="{{ $inputClass }}">
                </x-admin.form-field>
                <x-admin.form-field name="demo_reset_mode" label="Reset instructions">
                    <input id="demo_reset_mode" name="demo_reset_mode" value="{{ old('demo_reset_mode', $app->demo_reset_mode) }}" class="{{ $inputClass }}">
                </x-admin.form-field>
                <x-admin.form-field name="demo_instructions" label="Demo instructions" class="sm:col-span-2">
                    <textarea id="demo_instructions" name="demo_instructions" rows="2" class="{{ $inputClass }}">{{ old('demo_instructions', $app->demo_instructions) }}</textarea>
                </x-admin.form-field>
                <x-admin.form-field name="demo_warning" label="Demo warning (optional)" class="sm:col-span-2">
                    <textarea id="demo_warning" name="demo_warning" rows="2" class="{{ $inputClass }}">{{ old('demo_warning', $app->demo_warning) }}</textarea>
                </x-admin.form-field>
            </div>
        </section>

        {{-- INFO --}}
        <section class="card p-6">
            <h2 class="text-h3">Support &amp; Requirements</h2>
            <div class="mt-4 grid gap-5 sm:grid-cols-2">
                <x-admin.form-field name="documentation_url" label="Documentation URL">
                    <input id="documentation_url" name="documentation_url" value="{{ old('documentation_url', $app->documentation_url) }}" class="{{ $inputClass }}">
                </x-admin.form-field>
                <x-admin.form-field name="privacy_policy_url" label="App-specific privacy URL (optional)">
                    <input id="privacy_policy_url" name="privacy_policy_url" value="{{ old('privacy_policy_url', $app->privacy_policy_url) }}" class="{{ $inputClass }}">
                </x-admin.form-field>
                <x-admin.form-field name="support_info" label="Support info" class="sm:col-span-2">
                    <textarea id="support_info" name="support_info" rows="2" class="{{ $inputClass }}">{{ old('support_info', $app->support_info) }}</textarea>
                </x-admin.form-field>
                <x-admin.form-field name="system_requirements" label="System requirements" class="sm:col-span-2">
                    <textarea id="system_requirements" name="system_requirements" rows="2" class="{{ $inputClass }}">{{ old('system_requirements', $app->system_requirements) }}</textarea>
                </x-admin.form-field>
            </div>
        </section>

        {{-- SEO --}}
        <section class="card p-6">
            <h2 class="text-h3">SEO</h2>
            <div class="mt-4 grid gap-5 sm:grid-cols-2">
                <x-admin.form-field name="seo_title" label="SEO title">
                    <input id="seo_title" name="seo_title" value="{{ old('seo_title', $app->seo_title) }}" class="{{ $inputClass }}">
                </x-admin.form-field>
                <x-admin.form-field name="seo_description" label="SEO description">
                    <input id="seo_description" name="seo_description" value="{{ old('seo_description', $app->seo_description) }}" class="{{ $inputClass }}">
                </x-admin.form-field>
            </div>
        </section>

        <div class="flex gap-3">
            <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Save Changes' : 'Create App' }}</button>
            <a href="{{ route('admin.apps.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>

    @if ($isEdit)
        <div class="mt-10 grid gap-6 lg:grid-cols-2">
            @include('admin.apps.partials.media', ['app' => $app])
            @include('admin.apps.partials.features', ['app' => $app])
        </div>

        <div class="mt-6">
            @include('admin.apps.partials.editions', ['app' => $app, 'platforms' => $platforms])
        </div>

        <div class="mt-6">
            @include('admin.apps.partials.releases', ['app' => $app, 'platforms' => $platforms])
        </div>

        <div class="mt-6">
            @include('admin.apps.partials.entitlements', ['app' => $app])
        </div>
    @else
        <x-alert type="info" class="mt-8">Save this app first to add screenshots, icon, and feature bullets.</x-alert>
    @endif
</x-admin-layout>
