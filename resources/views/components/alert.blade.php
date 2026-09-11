@props(['type' => 'info'])

@php
    $styles = [
        'success' => 'bg-cta-50 border-cta-400 text-cta-600',
        'error' => 'bg-red-50 border-red-300 text-red-700',
        'info' => 'bg-water-50 border-water-100 text-water-600',
    ][$type] ?? 'bg-mist border-border text-navy-soft';
@endphp

<div {{ $attributes->merge(['class' => "rounded-xl border px-4 py-3 text-sm {$styles}"]) }} role="{{ $type === 'error' ? 'alert' : 'status' }}">
    {{ $slot }}
</div>
