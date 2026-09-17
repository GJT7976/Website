@props(['app'])

{{--
    Single-edition "unlock Pro" card for an app sold as "free app + optional
    Pro unlock" — replaces <x-edition-picker> for this case, which is built
    around choosing between platform-priced editions and doesn't fit a
    single always-included unlock well. The edition itself still exists on
    the backend (Stripe checkout, license issuance) exactly as any other
    AppEdition — only the customer-facing framing differs here.
--}}
@php($edition = $app->editions->first())

@if ($edition)
    <section id="unlock-pro" class="mx-auto max-w-7xl px-4 pt-14 sm:px-6 lg:px-8">
        <div class="card border-niagara-500 p-6 ring-1 ring-niagara-500">
            <h2 class="text-h2">Unlock {{ $app->name }} Pro</h2>
            <p class="text-body mt-2">{{ $edition->description }}</p>
            <p class="text-h3 mt-4">{{ $edition->priceLabel() }} <span class="text-small font-normal text-navy-soft">one time, up to 2 devices</span></p>
            <div class="mt-5 flex flex-wrap gap-3">
                <a href="{{ route('checkout.create.edition', [$app, $edition]) }}" class="btn btn-primary">Unlock Pro — {{ $edition->priceLabel() }}</a>
            </div>
            <p class="text-small mt-4 text-navy-soft">After purchase, enter your license key in the app's Settings to unlock Pro on up to 2 of your devices.</p>
        </div>
    </section>
@endif
