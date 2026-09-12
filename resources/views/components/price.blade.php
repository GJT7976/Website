@props(['app'])

<span {{ $attributes->merge(['class' => 'inline-flex items-baseline gap-2']) }}>
    @if ($app->hasEditions())
        <span class="text-h3">From ${{ number_format($app->lowestEditionPriceCents() / 100, 2) }} {{ $app->currency ?? 'CAD' }}</span>
    @elseif ($app->onSale())
        <span class="text-h3 text-cta-600">${{ number_format($app->sale_price_cents / 100, 2) }} {{ $app->currency }}</span>
        <span class="text-small line-through">${{ number_format($app->price_cents / 100, 2) }}</span>
    @else
        <span class="text-h3">{{ $app->priceLabel() }}</span>
    @endif
</span>
