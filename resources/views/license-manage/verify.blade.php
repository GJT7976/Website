<x-app-layout :title="'Manage Your License — Niagara Inde Apps'">
    <section class="mx-auto max-w-lg px-4 py-16 sm:px-6 lg:px-8">
        <p class="text-label text-niagara-600">License Management</p>
        <h1 class="text-h1 mt-1">Enter your code</h1>
        <p class="text-body mt-3">Check your email for a 6-digit management code — it expires in {{ config('licensing.management_code_ttl_minutes') }} minutes.</p>

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

        <form method="post" action="{{ route('license.manage.verify') }}" class="mt-6 flex flex-col gap-3">
            @csrf
            <input type="email" name="email" required placeholder="you@example.com" value="{{ old('email', $email) }}"
                class="w-full rounded-lg border border-border-strong px-3 py-2 focus:border-niagara-500 focus:outline-none">
            <input type="text" name="code" required maxlength="6" placeholder="123456" value="{{ old('code') }}"
                class="w-full rounded-lg border border-border-strong px-3 py-2 font-mono tracking-widest focus:border-niagara-500 focus:outline-none">
            <button type="submit" class="btn btn-primary whitespace-nowrap">Verify code</button>
        </form>
    </section>
</x-app-layout>
