@props(['features'])

<div {{ $attributes->merge(['class' => 'grid gap-5 sm:grid-cols-2']) }}>
    @foreach ($features as $feature)
        <div class="card flex gap-3 p-5">
            <span class="text-2xl leading-none" aria-hidden="true">{{ $feature->icon ?? '✨' }}</span>
            <div>
                <h3 class="text-h3">{{ $feature->title }}</h3>
                @if ($feature->description)
                    <p class="text-body mt-1">{{ $feature->description }}</p>
                @endif
            </div>
        </div>
    @endforeach
</div>
