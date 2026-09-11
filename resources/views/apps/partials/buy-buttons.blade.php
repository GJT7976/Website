{{--
    Direct Stripe checkout ("direct_purchase_enabled") is modeled in the
    data but intentionally inert in Phase 1 — no fake "Buy" button that
    goes nowhere. External store links are always safe to show since they
    lead somewhere real.
--}}
@if ($app->google_play_url)
    <a href="{{ $app->google_play_url }}" class="btn btn-secondary" target="_blank" rel="noopener">Get it on Google Play</a>
@endif
@if ($app->microsoft_store_url)
    <a href="{{ $app->microsoft_store_url }}" class="btn btn-secondary" target="_blank" rel="noopener">Get it on Microsoft Store</a>
@endif
@if ($app->apple_url)
    <a href="{{ $app->apple_url }}" class="btn btn-secondary" target="_blank" rel="noopener">Get it on the App Store</a>
@endif

@if (! $app->is_free && ! $app->google_play_url && ! $app->microsoft_store_url && ! $app->apple_url)
    <a href="{{ route('contact', ['app' => $app->slug]) }}" class="btn btn-secondary">Contact to Purchase</a>
@endif
