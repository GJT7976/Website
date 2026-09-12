{{--
    Direct Stripe checkout: real and functional now (Phase 2) whenever an
    app has direct_purchase_enabled + a price. External store links are
    always shown too when set, since they lead somewhere real either way.
--}}
@if ($app->hasEditions())
    {{-- Platform/edition selection is required before Buy Now — §13. --}}
    <a href="#choose-version" class="btn btn-primary">Choose Your Version</a>
@elseif (! $app->is_free && $app->direct_purchase_enabled && $app->effectivePriceCents() !== null)
    <a href="{{ route('checkout.create', $app) }}" class="btn btn-primary">Buy Now</a>
@endif

@if ($app->google_play_url && in_array($app->android_delivery_mode, ['play', 'both']))
    <a href="{{ $app->google_play_url }}" class="btn btn-secondary" target="_blank" rel="noopener">Get it on Google Play</a>
@endif
@if ($app->microsoft_store_url && in_array($app->windows_delivery_mode, ['store', 'both']))
    <a href="{{ $app->microsoft_store_url }}" class="btn btn-secondary" target="_blank" rel="noopener">Get it on Microsoft Store</a>
@endif
@if ($app->apple_url)
    <a href="{{ $app->apple_url }}" class="btn btn-secondary" target="_blank" rel="noopener">Get it on the App Store</a>
@endif

@if (! $app->hasEditions() && ! $app->is_free && ! $app->direct_purchase_enabled && ! $app->google_play_url && ! $app->microsoft_store_url && ! $app->apple_url)
    <a href="{{ route('contact', ['app' => $app->slug]) }}" class="btn btn-secondary">Contact to Purchase</a>
@endif
