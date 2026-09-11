<x-app-layout title="Pricing — Niagara Inde Apps" description="Pricing for Niagara Inde Apps software.">
    <section class="mx-auto max-w-5xl px-4 py-14 sm:px-6 lg:px-8">
        <p class="text-label text-niagara-600">Pricing</p>
        <h1 class="text-h1 mt-1">Simple, per-app pricing</h1>
        <p class="text-body mt-3 max-w-2xl">Every app is priced on its own page — no bundles, no subscriptions to start. Free apps stay free.</p>

        <div class="mt-10 divide-y divide-border rounded-2xl border border-border bg-white">
            @forelse ($apps as $app)
                <a href="{{ route('apps.show', $app) }}" class="flex items-center justify-between gap-4 p-5 transition hover:bg-mist">
                    <div>
                        <p class="text-h3">{{ $app->name }}</p>
                        <p class="text-small">{{ $app->category?->name }}</p>
                    </div>
                    <x-price :app="$app" />
                </a>
            @empty
                <p class="text-body p-8 text-center">Pricing will appear here as apps are published.</p>
            @endforelse
        </div>

        <p class="text-small mt-6">Direct in-browser checkout with automatic Canadian sales tax is coming soon. In the meantime, each app's page links to available purchase options.</p>
    </section>
</x-app-layout>
