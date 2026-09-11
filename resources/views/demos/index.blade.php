<x-app-layout title="Live Demos — Niagara Inde Apps" description="Try Niagara Inde Apps software directly in your browser.">
    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <p class="text-label text-niagara-600">Try It Before You Buy It</p>
        <h1 class="text-h1 mt-1">Live Demos</h1>
        <p class="text-body mt-2 max-w-2xl">Every demo below uses sample data only, runs entirely in your browser, and never charges a real payment card.</p>

        @if ($apps->isNotEmpty())
            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($apps as $app)
                    <x-app-card :app="$app" />
                @endforeach
            </div>
        @else
            <p class="text-body mt-10 rounded-xl border border-dashed border-border-strong p-10 text-center">No live demos are available right now. Check back soon.</p>
        @endif
    </section>
</x-app-layout>
