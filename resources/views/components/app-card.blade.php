@props(['app'])

@php
    $icon = $app->icon();
@endphp

<article class="card group flex flex-col overflow-hidden transition hover:-translate-y-0.5 hover:shadow-lift">
    <a href="{{ route('apps.show', $app) }}" class="flex flex-1 flex-col p-5">
        <div class="flex items-start gap-3">
            <div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-border bg-mist">
                @if ($icon)
                    <img src="{{ $icon->url() }}" alt="{{ $icon->alt_text ?? $app->name.' icon' }}" class="h-full w-full object-cover" loading="lazy">
                @else
                    <span class="text-h3 text-niagara-500">{{ Str::substr($app->name, 0, 1) }}</span>
                @endif
            </div>
            <div class="min-w-0">
                <h3 class="text-h3 truncate">{{ $app->name }}</h3>
                @if ($app->tagline)
                    <p class="text-small truncate">{{ $app->tagline }}</p>
                @endif
            </div>
        </div>

        @if ($app->short_description)
            <p class="text-body mt-3 line-clamp-3">{{ $app->short_description }}</p>
        @endif

        <div class="mt-4 flex flex-wrap gap-1.5">
            @foreach ($app->platforms as $platform)
                <x-platform-badge :code="$platform->code" :name="$platform->name" />
            @endforeach
        </div>

        <div class="mt-4 flex items-center justify-between">
            <x-price :app="$app" />
        </div>
    </a>

    <div class="flex divide-x divide-border border-t border-border">
        <a href="{{ route('apps.show', $app) }}" class="flex-1 px-4 py-3 text-center text-nav text-navy-soft transition hover:bg-mist">Learn More</a>
        @if ($app->demo_enabled)
            <a href="{{ route('demos.show', $app) }}" class="flex-1 px-4 py-3 text-center text-nav font-semibold text-niagara-600 transition hover:bg-niagara-50">Try Demo</a>
        @endif
    </div>
</article>
