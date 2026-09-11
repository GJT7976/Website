@props(['code', 'name' => null])

@php
    $labels = ['android' => 'Android', 'windows' => 'Windows', 'web' => 'Web', 'pwa' => 'PWA', 'ios' => 'iOS', 'macos' => 'macOS'];
@endphp

<span {{ $attributes->merge(['class' => 'badge']) }}>{{ $name ?? ($labels[$code] ?? ucfirst($code)) }}</span>
