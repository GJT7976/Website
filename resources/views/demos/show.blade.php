<x-app-layout :title="'Demo: '.$app->name.' — Niagara Inde Apps'" :description="'Try '.$app->name.' live, right in your browser.'">
    <section class="border-b border-border bg-white">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-4 py-5 sm:px-6 lg:px-8">
            <div class="flex items-center gap-3">
                <a href="{{ route('apps.show', $app) }}" class="text-small font-semibold text-niagara-600 hover:underline">&larr; Back to {{ $app->name }}</a>
                <span class="badge badge-water">🧪 Demo Mode</span>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ $app->demo_url }}" target="_blank" rel="noopener" class="btn btn-secondary">Full Screen Demo</a>
                <a href="{{ route('apps.show', $app) }}#buy" class="btn btn-primary">Get {{ $app->name }}</a>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-[1fr_280px]">
            <div class="overflow-hidden rounded-2xl border border-border bg-white shadow-soft">
                <iframe
                    src="{{ $app->demo_url }}"
                    title="{{ $app->name }} live demo"
                    class="h-[75vh] w-full"
                    loading="lazy"
                ></iframe>
            </div>

            <aside class="space-y-4">
                <div class="card p-5">
                    <h2 class="text-h3">About this demo</h2>
                    <p class="text-body mt-2">{{ $app->demo_instructions ?? 'Explore the app freely — nothing you do here affects any real account or data.' }}</p>
                </div>

                @if ($app->demo_warning)
                    <x-alert type="info">{{ $app->demo_warning }}</x-alert>
                @endif

                <div class="card p-5">
                    <h2 class="text-h3">Reset demo</h2>
                    <p class="text-body mt-2">{{ $app->demo_reset_mode ?? 'Reload this page to reset the demo.' }}</p>
                </div>

                @if ($app->demo_version)
                    <p class="text-small">Demo version {{ $app->demo_version }}</p>
                @endif
            </aside>
        </div>
    </section>
</x-app-layout>
