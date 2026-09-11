<x-app-layout :title="'Checkout cancelled — Niagara Inde Apps'">
    <section class="mx-auto max-w-xl px-4 py-16 text-center sm:px-6 lg:px-8">
        <span class="text-4xl" aria-hidden="true">🛒</span>
        <h1 class="text-h1 mt-4">Checkout cancelled</h1>
        <p class="text-body mt-3">No payment was made. You can pick up where you left off any time.</p>
        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <a href="{{ route('checkout.create', $app) }}" class="btn btn-primary">Try Again</a>
            <a href="{{ route('apps.show', $app) }}" class="btn btn-secondary">Back to {{ $app->name }}</a>
        </div>
    </section>
</x-app-layout>
