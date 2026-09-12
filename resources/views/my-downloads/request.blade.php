<x-app-layout :title="'My Downloads — Niagara Inde Apps'">
    <section class="mx-auto max-w-lg px-4 py-16 sm:px-6 lg:px-8">
        <p class="text-label text-niagara-600">My Downloads</p>
        <h1 class="text-h1 mt-1">Get your download links</h1>
        <p class="text-body mt-3">Enter the email address you used at checkout and we'll send you a link to your purchases — no account or password needed.</p>

        @if (session('status'))
            <x-alert type="success" class="mt-6">{{ session('status') }}</x-alert>
        @endif

        @if ($errors->any())
            <x-alert type="error" class="mt-6">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </x-alert>
        @endif

        <form method="post" action="{{ route('my-downloads.send-link') }}" class="mt-6 flex flex-col gap-3 sm:flex-row">
            @csrf
            <input type="email" name="email" required placeholder="you@example.com" value="{{ old('email') }}"
                class="w-full rounded-lg border border-border-strong px-3 py-2 focus:border-niagara-500 focus:outline-none">
            <button type="submit" class="btn btn-primary whitespace-nowrap">Email my link</button>
        </form>
    </section>
</x-app-layout>
