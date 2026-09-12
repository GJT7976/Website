@php
    $inputClass = 'w-full rounded-lg border border-border-strong px-3 py-2 focus:border-niagara-500 focus:outline-none';
    $provinces = [
        'AB' => 'Alberta', 'BC' => 'British Columbia', 'MB' => 'Manitoba', 'NB' => 'New Brunswick',
        'NL' => 'Newfoundland and Labrador', 'NS' => 'Nova Scotia', 'NT' => 'Northwest Territories',
        'NU' => 'Nunavut', 'ON' => 'Ontario', 'PE' => 'Prince Edward Island', 'QC' => 'Quebec',
        'SK' => 'Saskatchewan', 'YT' => 'Yukon',
    ];
@endphp

<x-app-layout :title="'Checkout: '.$app->name.' — Niagara Inde Apps'">
    <section class="mx-auto max-w-2xl px-4 py-12 sm:px-6 lg:px-8">
        <p class="text-label text-niagara-600">Checkout</p>
        <h1 class="text-h1 mt-1">{{ $app->name }}</h1>
        <p class="text-body mt-2">{{ $app->tagline }}</p>

        <div class="card mt-6 p-5">
            <div class="flex items-center justify-between">
                <span class="text-nav font-semibold">{{ $app->name }}@if ($edition) — {{ $edition->name }}@endif</span>
                @if ($edition)
                    <span class="text-h3">{{ $edition->priceLabel() }}</span>
                @else
                    <x-price :app="$app" />
                @endif
            </div>
            @if ($edition)
                <p class="text-small mt-2">{{ $app->licenseLabel() }}</p>
                <ul class="text-small mt-2 space-y-0.5">
                    @foreach (collect($edition->includedPlatforms())->pluck('platform_name')->unique() as $platformName)
                        <li>✓ {{ $platformName }}</li>
                    @endforeach
                </ul>
            @endif
        </div>

        @if ($errors->any())
            <x-alert type="error" class="mt-6">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </x-alert>
        @endif

        <form method="post" action="{{ $edition ? route('checkout.store.edition', [$app, $edition]) : route('checkout.store', $app) }}" class="mt-6 space-y-5" x-data="{ country: '{{ old('billing_country', 'CA') }}' }">
            @csrf

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="customer_name" class="text-label">Full name</label>
                    <input id="customer_name" name="customer_name" required value="{{ old('customer_name') }}" class="{{ $inputClass }} mt-1.5">
                </div>
                <div>
                    <label for="customer_email" class="text-label">Email</label>
                    <input id="customer_email" name="customer_email" type="email" required value="{{ old('customer_email') }}" class="{{ $inputClass }} mt-1.5">
                </div>
            </div>

            <div>
                <label for="billing_company" class="text-label">Company (optional)</label>
                <input id="billing_company" name="billing_company" value="{{ old('billing_company') }}" class="{{ $inputClass }} mt-1.5">
            </div>

            <div>
                <label for="billing_address" class="text-label">Billing address</label>
                <input id="billing_address" name="billing_address" required value="{{ old('billing_address') }}" class="{{ $inputClass }} mt-1.5">
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="billing_city" class="text-label">City</label>
                    <input id="billing_city" name="billing_city" required value="{{ old('billing_city') }}" class="{{ $inputClass }} mt-1.5">
                </div>
                <div>
                    <label for="billing_postal_code" class="text-label">Postal / ZIP code</label>
                    <input id="billing_postal_code" name="billing_postal_code" required value="{{ old('billing_postal_code') }}" class="{{ $inputClass }} mt-1.5">
                </div>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="billing_country" class="text-label">Country</label>
                    <select id="billing_country" name="billing_country" x-model="country" class="{{ $inputClass }} mt-1.5">
                        <option value="CA">Canada</option>
                        <option value="US">United States</option>
                        <option value="GB">United Kingdom</option>
                        <option value="AU">Australia</option>
                        <option value="OT">Other</option>
                    </select>
                </div>
                <div>
                    <label for="billing_province" class="text-label">Province / State</label>
                    <template x-if="country === 'CA'">
                        <select id="billing_province" name="billing_province" class="{{ $inputClass }} mt-1.5">
                            @foreach ($provinces as $code => $label)
                                <option value="{{ $code }}" @selected(old('billing_province') === $code)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </template>
                    <template x-if="country !== 'CA'">
                        <input id="billing_province_text" name="billing_province" value="{{ old('billing_province') }}" class="{{ $inputClass }} mt-1.5">
                    </template>
                </div>
            </div>

            <x-alert type="info">
                Canadian sales tax (if applicable to your province) is calculated on the next step, before you're
                charged. You'll be redirected to Stripe's secure checkout to complete payment — card details are
                never seen by this site.
            </x-alert>

            <button type="submit" class="btn btn-primary w-full">Continue to Payment</button>
        </form>
    </section>
</x-app-layout>
