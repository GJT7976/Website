@props(['app'])

@php
    // Default supporting text per §2 when the admin hasn't written a custom
    // description for this edition.
    $defaultDescriptions = [
        'android' => 'For Android phones and tablets.',
        'windows' => 'For compatible Windows PCs and tablets.',
        'android-windows' => "One purchase. Install the app on your compatible Android and Windows devices, subject to the app's license terms.",
        'web' => 'Works on iPhone, iPad, Mac, Windows, Android & Chromebook — right from a browser, no installation required.',
        'complete' => 'Get all customer platforms included with this app.',
    ];

    $editionsForAlpine = $app->editions->mapWithKeys(function ($edition) use ($app) {
        $platformNames = collect($edition->includedPlatforms())->pluck('platform_name')->unique()->values()->all();

        return [$edition->id => [
            'name' => $edition->name,
            'price_label' => $edition->priceLabel(),
            'includes' => $platformNames,
            'license' => $app->licenseLabel(),
            'checkout_url' => route('checkout.create.edition', [$app, $edition]),
        ]];
    });
@endphp

<div x-data="{ selected: null, editions: {{ Illuminate\Support\Js::from($editionsForAlpine) }} }">
    <h2 class="text-h2">Choose Your Version</h2>
    <p class="text-body mt-2">Select where you want to use {{ $app->name }}.</p>

    @if (in_array($app->android_delivery_mode, ['direct', 'both']) && $app->editions->contains(fn ($e) => collect($e->includedPlatforms())->contains('platform', 'android')))
        <x-alert type="info" class="mt-4">
            <strong>Direct Android Download.</strong> This Android app is purchased and downloaded directly from Niagara Inde Apps rather than through Google Play. Android may ask you to allow installation from this source when installing the app.
            <a href="{{ route('support.install.android') }}" class="font-semibold underline">Learn how to install</a>.
        </x-alert>
    @endif

    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($app->editions as $edition)
            <button
                type="button"
                @click="selected = {{ $edition->id }}"
                :class="selected === {{ $edition->id }} ? 'border-niagara-500 ring-2 ring-niagara-500' : 'border-border'"
                class="relative flex flex-col items-start rounded-xl border bg-white p-5 text-left transition"
            >
                @if ($edition->featured)
                    <span class="badge absolute -top-3 right-4 bg-cta-500 text-white">Best Value</span>
                @endif
                <p class="text-nav font-semibold">{{ $edition->name }}</p>
                <p class="text-small mt-1 text-navy-soft">{{ $edition->description ?: ($defaultDescriptions[$edition->slug] ?? '') }}</p>
                <p class="text-h3 mt-4">{{ $edition->priceLabel() }}</p>
                <span class="btn btn-secondary mt-4 w-full text-center" :class="selected === {{ $edition->id }} ? 'btn-primary' : ''">
                    {{ $edition->slug === 'android-windows' ? 'Select Bundle' : ($edition->slug === 'complete' ? 'Select Complete' : 'Select') }}
                </span>
            </button>
        @endforeach
    </div>

    <template x-if="selected">
        <div class="card mt-6 p-6" x-cloak>
            <p class="text-label text-niagara-600">Your Selection</p>
            <h3 class="text-h3 mt-1">{{ $app->name }}</h3>
            <p class="font-semibold" x-text="editions[selected].name"></p>
            <p class="text-small mt-1" x-text="editions[selected].license"></p>

            <ul class="text-small mt-3 space-y-1">
                <template x-for="platform in editions[selected].includes" :key="platform">
                    <li x-text="'✓ ' + platform"></li>
                </template>
            </ul>

            <p class="text-h3 mt-4">
                Subtotal: <span x-text="editions[selected].price_label"></span>
            </p>
            <p class="text-small mt-1">Applicable tax is calculated at checkout.</p>

            <div class="mt-5 flex flex-wrap gap-3">
                <a :href="editions[selected].checkout_url" class="btn btn-primary">Buy Now</a>
                @if ($app->demo_enabled)
                    <a href="{{ route('demos.show', $app) }}" class="btn btn-secondary">Try Live Demo</a>
                @endif
            </div>
        </div>
    </template>
</div>
