<x-app-layout :title="($hero['title'] ?? config('app.name')).' — '.($hero['subtitle'] ?? '')">
    {{-- HERO --}}
    <section class="relative overflow-hidden border-b border-border bg-gradient-to-b from-water-50 via-offwhite to-offwhite">
        <svg class="pointer-events-none absolute inset-x-0 bottom-0 h-40 w-full text-water-100" viewBox="0 0 1440 200" preserveAspectRatio="none" aria-hidden="true">
            <path fill="currentColor" d="M0,120 C240,180 480,40 720,80 C960,120 1200,180 1440,100 L1440,200 L0,200 Z" />
        </svg>
        <div class="relative mx-auto grid grid-cols-1 max-w-7xl gap-12 px-4 py-16 sm:px-6 lg:grid-cols-2 lg:items-center lg:px-8 lg:py-24">
            <div class="min-w-0">
                <p class="text-label text-niagara-600">Independent &middot; Canadian &middot; Niagara-built</p>
                <h1 class="text-display mt-3">{{ $hero['title'] }}</h1>
                <p class="text-h2 mt-2 text-niagara-600">{{ $hero['subtitle'] }}</p>
                <p class="text-body mt-5 max-w-xl">{{ $hero['description'] }}</p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('apps.index') }}" class="btn btn-primary">Explore Our Apps</a>
                    <a href="{{ route('demos.index') }}" class="btn btn-secondary">Try a Live Demo</a>
                    <a href="{{ route('about') }}" class="btn btn-ghost">About Niagara Inde Apps</a>
                </div>
            </div>

            <div class="relative mx-auto w-full max-w-md">
                {{-- Abstract device-mockup composition (no stock photography) --}}
                <div class="card relative aspect-[4/3] overflow-hidden p-0">
                    <div class="absolute inset-0 bg-gradient-to-br from-niagara-500 via-niagara-600 to-water-600"></div>
                    <svg class="absolute inset-x-0 bottom-0 h-2/3 w-full text-white/10" viewBox="0 0 400 200" preserveAspectRatio="none" aria-hidden="true">
                        <path fill="currentColor" d="M0,60 C100,120 200,0 400,60 L400,200 L0,200 Z" />
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="w-40 rounded-2xl border border-white/30 bg-white/95 p-3 shadow-lift">
                            <div class="h-2 w-10 rounded-full bg-niagara-200"></div>
                            <div class="mt-3 space-y-2">
                                <div class="h-3 w-full rounded bg-mist"></div>
                                <div class="h-3 w-4/5 rounded bg-mist"></div>
                                <div class="h-8 w-full rounded-lg bg-cta-500/90"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <p class="text-small mt-3 text-center">Practical tools, built for real work &mdash; on desktop, tablet, and phone.</p>
            </div>
        </div>
    </section>

    {{-- TRUST / PRODUCT PRINCIPLES --}}
    <section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['icon' => '🍁', 'title' => 'Canadian Focus', 'body' => 'Built with Canadian businesses in mind.'],
                ['icon' => '⚙️', 'title' => 'Simple & Powerful', 'body' => 'Useful software without unnecessary complexity.'],
                ['icon' => '📶', 'title' => 'Offline & Online', 'body' => 'Applications designed for real-world conditions.'],
                ['icon' => '🤝', 'title' => 'Real Support', 'body' => 'Software created and maintained by an independent developer.'],
            ] as $point)
                <div class="card p-6">
                    <span class="text-2xl" aria-hidden="true">{{ $point['icon'] }}</span>
                    <h3 class="text-h3 mt-3">{{ $point['title'] }}</h3>
                    <p class="text-body mt-1">{{ $point['body'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- FEATURED APPS --}}
    <section class="border-y border-border bg-white py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="text-label text-niagara-600">Featured</p>
                    <h2 class="text-h1 mt-1">Featured Applications</h2>
                </div>
                <a href="{{ route('apps.index') }}" class="text-nav font-semibold text-niagara-600 hover:underline">Browse all apps &rarr;</a>
            </div>

            @if ($featuredApps->isNotEmpty())
                <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($featuredApps as $app)
                        <x-app-card :app="$app" />
                    @endforeach
                </div>
            @else
                <p class="text-body mt-8 rounded-xl border border-dashed border-border-strong p-8 text-center">
                    Featured apps will appear here as they're published. Take a look at the full <a href="{{ route('apps.index') }}" class="font-semibold text-niagara-600 hover:underline">apps directory</a> in the meantime.
                </p>
            @endif
        </div>
    </section>

    {{-- LIVE DEMO SECTION --}}
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="text-center">
            <p class="text-label text-niagara-600">Try It Before You Buy It</p>
            <h2 class="text-h1 mt-1">Interactive Web Demos</h2>
            <p class="text-body mx-auto mt-3 max-w-2xl">Every demo runs on sample data only &mdash; explore the real interface with nothing to install and nothing at risk.</p>
        </div>

        @if ($demoApps->isNotEmpty())
            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($demoApps as $app)
                    <x-app-card :app="$app" />
                @endforeach
            </div>
        @else
            <p class="text-body mt-8 rounded-xl border border-dashed border-border-strong p-8 text-center">
                A curated live demo will be featured here soon. See every available demo on the <a href="{{ route('demos.index') }}" class="font-semibold text-niagara-600 hover:underline">Live Demos</a> page.
            </p>
        @endif
    </section>

    {{-- WHY NIAGARA INDE APPS --}}
    <section class="border-y border-border bg-niagara-50/50 py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h2 class="text-h1 text-center">Why Niagara Inde Apps</h2>
            <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['title' => 'Practical software', 'body' => 'Built to solve real problems, not to impress a features list.'],
                    ['title' => 'Independent development', 'body' => 'One independent Canadian developer, directly accountable for what ships.'],
                    ['title' => 'Privacy conscious', 'body' => 'The least data necessary, no advertising trackers by default.'],
                    ['title' => 'Sensible pricing', 'body' => 'Clear, one-time pricing where possible &mdash; no surprise complexity.'],
                ] as $item)
                    <div>
                        <h3 class="text-h3">{{ $item['title'] }}</h3>
                        <p class="text-body mt-1">{{ $item['body'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- BUSINESS TYPES --}}
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <h2 class="text-h1 text-center">Built for Businesses Like Yours</h2>
        <div class="mt-8 flex flex-wrap justify-center gap-3">
            @foreach (['Retail Shops', 'Cafés', 'Restaurants', 'Market Vendors', 'Hobby Farms', 'Food Trucks', 'Trades', 'Service Businesses', 'Home Businesses', 'Independent Operators'] as $type)
                <span class="badge badge-brand">{{ $type }}</span>
            @endforeach
        </div>
    </section>

    {{-- NIAGARA / CANADA STORY --}}
    <section class="border-y border-border bg-navy py-16 text-white">
        <div class="mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
            <p class="text-label text-niagara-300">Local Roots. Global Ideas.</p>
            <h2 class="text-h1 mt-1 text-white">Built in Niagara, Ontario</h2>
            <p class="text-body mt-4 text-navy-soft" style="color:#c7d3d8">
                Every app here starts from the same place: a real problem, seen up close, in the Niagara region. The goal isn't to look big &mdash; it's to be useful, wherever a copy of it ends up running.
            </p>
        </div>
    </section>

    {{-- SUPPORT / CTA --}}
    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <x-cta-block title="Have a question, or an idea for an app?" description="We read every message personally.">
            <a href="{{ route('contact') }}" class="btn bg-white text-niagara-700 hover:bg-niagara-50">Get Support</a>
            <a href="{{ route('apps.index') }}" class="btn border border-white/40 text-white hover:bg-white/10">Explore Apps</a>
        </x-cta-block>
    </div>
</x-app-layout>
