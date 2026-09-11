@props(['name', 'label' => null, 'hint' => null])

<div {{ $attributes->merge(['class' => '']) }}>
    @if ($label)
        <label for="{{ $name }}" class="text-label">{{ $label }}</label>
    @endif
    <div class="{{ $label ? 'mt-1.5' : '' }}">
        {{ $slot }}
    </div>
    @if ($hint)
        <p class="text-small mt-1">{{ $hint }}</p>
    @endif
    @error($name)
        <p class="text-small mt-1 text-red-600">{{ $message }}</p>
    @enderror
</div>
