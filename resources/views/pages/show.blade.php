<x-app-layout :title="($page->meta_title ?: $page->title).' — Niagara Inde Apps'" :description="$page->meta_description">
    <section class="mx-auto max-w-3xl px-4 py-14 sm:px-6 lg:px-8">
        <h1 class="text-h1">{{ $page->title }}</h1>

        <div class="mt-8 space-y-10">
            @foreach ($page->sections as $section)
                <div>
                    @if ($section->heading)
                        <h2 class="text-h2">{{ $section->heading }}</h2>
                    @endif
                    @if ($section->media)
                        <img src="{{ $section->media->url() }}" alt="{{ $section->media->alt_text }}" class="mt-3 w-full rounded-xl border border-border">
                    @endif
                    @if ($section->body)
                        <div class="text-body mt-3 max-w-none whitespace-pre-line">{{ $section->body }}</div>
                    @endif
                </div>
            @endforeach
        </div>
    </section>
</x-app-layout>
