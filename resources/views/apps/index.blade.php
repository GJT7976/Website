<x-app-layout title="Apps — Niagara Inde Apps" description="Browse every application from Niagara Inde Apps.">
    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <p class="text-label text-niagara-600">Apps</p>
        <h1 class="text-h1 mt-1">All Applications</h1>
        <p class="text-body mt-2 max-w-2xl">Practical software for small businesses, independent creators, and everyday users.</p>

        <form method="get" class="mt-8 flex flex-wrap items-center gap-3 border-b border-border pb-6">
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('apps.index') }}" class="badge {{ ! $activeCategory ? 'badge-brand' : '' }}">All</a>
                @foreach ($categories as $category)
                    <a href="{{ route('apps.index', ['category' => $category->slug]) }}" class="badge {{ $activeCategory === $category->slug ? 'badge-brand' : '' }}">{{ $category->name }}</a>
                @endforeach
            </div>

            <div class="ms-auto flex flex-wrap gap-2">
                <a href="{{ route('apps.index', array_filter(['category' => $activeCategory])) }}" class="badge badge-water {{ ! $activePlatform ? 'font-bold' : '' }}">All platforms</a>
                @foreach ($platforms as $platform)
                    <a href="{{ route('apps.index', array_filter(['category' => $activeCategory, 'platform' => $platform->code])) }}"
                       class="badge badge-water {{ $activePlatform === $platform->code ? 'font-bold' : '' }}">{{ $platform->name }}</a>
                @endforeach
            </div>
        </form>

        @if ($apps->isNotEmpty())
            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($apps as $app)
                    <x-app-card :app="$app" />
                @endforeach
            </div>
            <div class="mt-10">{{ $apps->links() }}</div>
        @else
            <p class="text-body mt-10 rounded-xl border border-dashed border-border-strong p-10 text-center">
                No apps match these filters yet. <a href="{{ route('apps.index') }}" class="font-semibold text-niagara-600 hover:underline">Clear filters</a>.
            </p>
        @endif
    </section>
</x-app-layout>
