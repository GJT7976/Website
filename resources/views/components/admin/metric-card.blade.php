@props(['label', 'value', 'icon' => null])

<div {{ $attributes->merge(['class' => 'card p-5']) }}>
    <div class="flex items-center justify-between">
        <p class="text-label">{{ $label }}</p>
        @if ($icon)
            <span class="text-lg" aria-hidden="true">{{ $icon }}</span>
        @endif
    </div>
    <p class="text-display mt-1" style="font-size: 2rem;">{{ $value }}</p>
</div>
