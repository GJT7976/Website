<x-app-layout :title="'Manage Your License — Niagara Inde Apps'">
    <section class="mx-auto max-w-lg px-4 py-16 sm:px-6 lg:px-8">
        <p class="text-label text-niagara-600">License Management</p>
        <h1 class="text-h1 mt-1">Manage your devices</h1>
        <p class="text-body mt-3">Enter your email address and license key and we'll send you a code to manage the devices activated on it.</p>

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

        <form method="post" action="{{ route('license.manage.send-code') }}" class="mt-6 flex flex-col gap-3">
            @csrf
            <input type="email" name="email" required placeholder="you@example.com" value="{{ old('email') }}"
                class="w-full rounded-lg border border-border-strong px-3 py-2 focus:border-niagara-500 focus:outline-none">
            <input type="text" name="license_key" required placeholder="XXXX-XXXX-XXXX-XXXX" value="{{ old('license_key') }}"
                class="w-full rounded-lg border border-border-strong px-3 py-2 font-mono uppercase focus:border-niagara-500 focus:outline-none">
            <button type="submit" class="btn btn-primary whitespace-nowrap">Send me a code</button>
        </form>
    </section>
</x-app-layout>
