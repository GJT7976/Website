@php
    $navLinks = [
        ['label' => 'Home', 'route' => 'home'],
        ['label' => 'Apps', 'route' => 'apps.index'],
        ['label' => 'Live Demos', 'route' => 'demos.index'],
        ['label' => 'Pricing', 'route' => 'pricing'],
        ['label' => 'About', 'route' => 'about'],
        ['label' => 'Support', 'route' => 'support'],
        ['label' => 'Contact', 'route' => 'contact'],
    ];
@endphp

<header x-data="{ open: false }" class="sticky top-0 z-40 border-b border-border bg-paper/90 backdrop-blur">
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" class="flex items-center gap-2 text-nav font-bold text-navy">
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-niagara-500 text-white">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-5 w-5" aria-hidden="true">
                    <path d="M4 15c2-4 4-6 8-6s6 2 8 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M4 19c2-3 4-4.5 8-4.5s6 1.5 8 4.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" opacity=".6"/>
                </svg>
            </span>
            <span class="leading-tight">
                <span class="block text-[15px]">Niagara Inde Apps</span>
            </span>
        </a>

        <nav class="hidden items-center gap-1 lg:flex" aria-label="Primary">
            @foreach ($navLinks as $link)
                <a href="{{ route($link['route']) }}"
                   class="text-nav rounded-md px-3 py-2 text-navy-soft transition hover:bg-mist hover:text-navy {{ request()->routeIs($link['route']) ? 'text-niagara-600 font-semibold' : '' }}">
                    {{ $link['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="hidden items-center gap-3 lg:flex">
            <form action="{{ route('apps.index') }}" method="get" role="search" class="relative">
                <label for="nav-search" class="sr-only">Search apps</label>
                <input id="nav-search" type="search" name="q" placeholder="Search apps…"
                       class="w-48 rounded-full border border-border-strong bg-white py-1.5 pl-3 pr-3 text-sm text-navy placeholder:text-ink-muted focus:border-niagara-500 focus:outline-none">
            </form>
            <a href="{{ route('demos.index') }}" class="btn btn-primary">Try a Live Demo</a>
        </div>

        <button type="button" @click="open = !open" class="inline-flex items-center justify-center rounded-md p-2 text-navy lg:hidden" aria-label="Toggle menu" :aria-expanded="open">
            <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
            <svg x-show="open" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display:none"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
    </div>

    <div x-show="open" x-cloak x-transition class="border-t border-border bg-white lg:hidden" style="display:none">
        <nav class="flex flex-col gap-1 px-4 py-3" aria-label="Primary mobile">
            @foreach ($navLinks as $link)
                <a href="{{ route($link['route']) }}" class="text-nav rounded-md px-3 py-2.5 text-navy-soft hover:bg-mist">{{ $link['label'] }}</a>
            @endforeach
            <a href="{{ route('demos.index') }}" class="btn btn-primary mt-2 w-full">Try a Live Demo</a>
        </nav>
    </div>
</header>
