@props(['screenshots'])

@if ($screenshots->isNotEmpty())
    <div x-data="{ open: false, index: 0, items: {{ $screenshots->map(fn ($m) => ['url' => $m->url(), 'alt' => $m->alt_text ?? '']) }} }">
        <div class="flex gap-4 overflow-x-auto pb-2" style="scrollbar-width: thin;">
            @foreach ($screenshots as $i => $shot)
                <button type="button" @click="open = true; index = {{ $i }}"
                        class="shrink-0 overflow-hidden rounded-xl border border-border bg-mist focus-visible:outline focus-visible:outline-2 focus-visible:outline-cta-500">
                    <img src="{{ $shot->url() }}" alt="{{ $shot->alt_text ?? 'Screenshot '.($i + 1) }}" class="h-64 w-auto object-cover" loading="lazy">
                </button>
            @endforeach
        </div>

        <div x-show="open" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-navy/90 p-4"
             role="dialog" aria-modal="true" aria-label="Screenshot viewer"
             @keydown.escape.window="open = false"
             @keydown.arrow-right.window="index = (index + 1) % items.length"
             @keydown.arrow-left.window="index = (index - 1 + items.length) % items.length"
             style="display:none">
            <button type="button" @click="open = false" class="absolute right-4 top-4 rounded-full bg-white/10 p-2 text-white hover:bg-white/20" aria-label="Close">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
            <button type="button" @click="index = (index - 1 + items.length) % items.length" class="absolute left-2 rounded-full bg-white/10 p-2 text-white hover:bg-white/20 sm:left-6" aria-label="Previous screenshot">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            </button>
            <img :src="items[index].url" :alt="items[index].alt" class="max-h-[85vh] max-w-full rounded-xl object-contain" @click.stop>
            <button type="button" @click="index = (index + 1) % items.length" class="absolute right-2 rounded-full bg-white/10 p-2 text-white hover:bg-white/20 sm:right-6" aria-label="Next screenshot">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </button>
        </div>
    </div>
@endif
