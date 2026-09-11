<x-app-layout title="Support — Niagara Inde Apps" description="Get help with Niagara Inde Apps software.">
    <section class="mx-auto max-w-4xl px-4 py-14 sm:px-6 lg:px-8">
        <p class="text-label text-niagara-600">Support</p>
        <h1 class="text-h1 mt-1">How can we help?</h1>
        <p class="text-body mt-3 max-w-2xl">Browse frequently asked questions below, or reach out directly and we'll get back to you personally.</p>

        <div class="mt-8 flex flex-wrap gap-3">
            <a href="{{ route('contact') }}" class="btn btn-primary">Contact Support</a>
            <a href="{{ route('apps.index') }}" class="btn btn-secondary">Browse Apps</a>
        </div>

        @if ($faqs->isNotEmpty())
            <div class="mt-12" x-data="{ openIndex: null }">
                <h2 class="text-h2">Frequently Asked Questions</h2>
                <div class="mt-4 divide-y divide-border rounded-xl border border-border bg-white">
                    @foreach ($faqs as $i => $faq)
                        <div class="p-4">
                            <button type="button" class="flex w-full items-center justify-between text-left text-nav font-semibold" @click="openIndex = openIndex === {{ $i }} ? null : {{ $i }}">
                                {{ $faq->question }}
                                <span aria-hidden="true" x-text="openIndex === {{ $i }} ? '−' : '+'"></span>
                            </button>
                            <p class="text-body mt-2" x-show="openIndex === {{ $i }}" x-cloak>{{ $faq->answer }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </section>
</x-app-layout>
