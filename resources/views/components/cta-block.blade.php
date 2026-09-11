@props(['title', 'description' => null])

<section {{ $attributes->merge(['class' => 'rounded-3xl bg-niagara-700 px-6 py-14 text-center sm:px-12']) }}>
    <h2 class="text-h1 text-white">{{ $title }}</h2>
    @if ($description)
        <p class="text-body mx-auto mt-3 max-w-2xl text-niagara-100">{{ $description }}</p>
    @endif
    <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
        {{ $slot }}
    </div>
</section>
