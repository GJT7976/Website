@php
    $icon = $app->icon();
    $featureGraphic = $app->featureGraphic();
    $screenshots = $app->screenshots();
@endphp

<x-app-layout :title="($app->seo_title ?: $app->name).' — Niagara Inde Apps'" :description="$app->seo_description ?: $app->short_description">
    {{-- HERO --}}
    <section class="border-b border-border bg-white">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <nav class="text-small mb-6" aria-label="Breadcrumb">
                <a href="{{ route('apps.index') }}" class="hover:text-niagara-600">Apps</a>
                <span aria-hidden="true"> / </span>
                <span>{{ $app->name }}</span>
            </nav>

            <div class="grid gap-10 lg:grid-cols-[auto_1fr_auto] lg:items-center">
                <div class="flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-border bg-mist">
                    @if ($icon)
                        <img src="{{ $icon->url() }}" alt="{{ $icon->alt_text ?? $app->name }}" class="h-full w-full object-cover">
                    @else
                        <span class="text-display text-niagara-500">{{ Str::substr($app->name, 0, 1) }}</span>
                    @endif
                </div>

                <div>
                    <h1 class="text-h1">{{ $app->name }}</h1>
                    @if ($app->tagline)
                        <p class="text-h3 mt-1 font-normal text-navy-soft">{{ $app->tagline }}</p>
                    @endif
                    <div class="mt-3 flex flex-wrap gap-1.5">
                        @foreach ($app->platforms as $platform)
                            <x-platform-badge :code="$platform->code" :name="$platform->name" />
                        @endforeach
                    </div>
                </div>

                <div class="flex flex-col items-start gap-3 lg:items-end">
                    <x-price :app="$app" />
                    <div class="flex flex-wrap gap-2">
                        @if ($app->demo_enabled)
                            <a href="{{ route('demos.show', $app) }}" class="btn btn-primary">Try Live Demo</a>
                        @endif
                        @include('apps.partials.buy-buttons', ['app' => $app])
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if ($featureGraphic)
        <section class="mx-auto max-w-7xl px-4 pt-10 sm:px-6 lg:px-8">
            <img src="{{ $featureGraphic->url() }}" alt="{{ $featureGraphic->alt_text ?? $app->name.' feature graphic' }}" class="w-full rounded-2xl border border-border object-cover">
        </section>
    @endif

    <div class="mx-auto grid max-w-7xl gap-12 px-4 py-14 sm:px-6 lg:grid-cols-[2fr_1fr] lg:px-8">
        <div class="space-y-14">
            {{-- OVERVIEW --}}
            @if ($app->long_description)
                <section>
                    <h2 class="text-h2">Overview</h2>
                    <div class="text-body prose-p:mt-3 mt-3 max-w-none whitespace-pre-line">{{ $app->long_description }}</div>
                </section>
            @endif

            {{-- FEATURES --}}
            @if ($app->features->isNotEmpty())
                <section>
                    <h2 class="text-h2">Features</h2>
                    <x-feature-list :features="$app->features" class="mt-5" />
                </section>
            @endif

            {{-- SCREENSHOTS --}}
            @if ($screenshots->isNotEmpty())
                <section>
                    <h2 class="text-h2">Screenshots</h2>
                    <x-screenshot-gallery :screenshots="$screenshots" class="mt-5" />
                </section>
            @endif

            {{-- WHAT IT DOES / WHO IT'S FOR --}}
            @if ($app->short_description)
                <section>
                    <h2 class="text-h2">What It Does</h2>
                    <p class="text-body mt-3">{{ $app->short_description }}</p>
                </section>
            @endif

            {{-- REQUIREMENTS --}}
            @if ($app->system_requirements)
                <section>
                    <h2 class="text-h2">Requirements</h2>
                    <p class="text-body mt-3 whitespace-pre-line">{{ $app->system_requirements }}</p>
                </section>
            @endif

            {{-- PRIVACY --}}
            <section>
                <h2 class="text-h2">Privacy</h2>
                @if ($app->privacy_policy_url)
                    <p class="text-body mt-3"><a href="{{ $app->privacy_policy_url }}" class="font-semibold text-niagara-600 hover:underline">Read this app's privacy information &rarr;</a></p>
                @else
                    <p class="text-body mt-3">This app follows the site-wide <a href="{{ route('privacy') }}" class="font-semibold text-niagara-600 hover:underline">Privacy Policy</a>. No app-specific privacy page has been published yet.</p>
                @endif
            </section>

            {{-- SUPPORT --}}
            <section>
                <h2 class="text-h2">Support</h2>
                <p class="text-body mt-3 whitespace-pre-line">{{ $app->support_info ?? 'Use the Contact page for help with this app.' }}</p>
                <a href="{{ route('contact', ['app' => $app->slug]) }}" class="btn btn-secondary mt-4">Get Support</a>
            </section>

            {{-- FAQ --}}
            @if ($app->faqs->isNotEmpty())
                <section x-data="{ openIndex: null }">
                    <h2 class="text-h2">FAQ</h2>
                    <div class="mt-4 divide-y divide-border rounded-xl border border-border">
                        @foreach ($app->faqs as $i => $faq)
                            <div class="p-4">
                                <button type="button" class="flex w-full items-center justify-between text-left text-nav font-semibold" @click="openIndex = openIndex === {{ $i }} ? null : {{ $i }}">
                                    {{ $faq->question }}
                                    <span aria-hidden="true" x-text="openIndex === {{ $i }} ? '−' : '+'"></span>
                                </button>
                                <p class="text-body mt-2" x-show="openIndex === {{ $i }}" x-cloak>{{ $faq->answer }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- REVIEWS: disabled until legitimate reviews exist (spec §10) --}}
        </div>

        {{-- SIDEBAR --}}
        <aside class="space-y-6">
            <div class="card p-6">
                <h2 class="text-h3">App Details</h2>
                <dl class="text-small mt-4 space-y-3">
                    @if ($app->version)
                        <div class="flex justify-between"><dt>Version</dt><dd class="font-semibold text-navy">{{ $app->version }}</dd></div>
                    @endif
                    @if ($app->category)
                        <div class="flex justify-between"><dt>Category</dt><dd class="font-semibold text-navy">{{ $app->category->name }}</dd></div>
                    @endif
                    @if ($app->release_date)
                        <div class="flex justify-between"><dt>Released</dt><dd class="font-semibold text-navy">{{ $app->release_date->format('M Y') }}</dd></div>
                    @endif
                    @if ($app->updated_on)
                        <div class="flex justify-between"><dt>Updated</dt><dd class="font-semibold text-navy">{{ $app->updated_on->format('M j, Y') }}</dd></div>
                    @endif
                    <div class="flex justify-between"><dt>Platforms</dt><dd class="font-semibold text-navy text-right">{{ $app->platforms->pluck('name')->implode(', ') ?: '—' }}</dd></div>
                </dl>
                @if ($app->documentation_url)
                    <a href="{{ $app->documentation_url }}" class="text-small mt-4 block font-semibold text-niagara-600 hover:underline">Documentation &rarr;</a>
                @endif
            </div>
        </aside>
    </div>
</x-app-layout>
