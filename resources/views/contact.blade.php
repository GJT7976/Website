<x-app-layout title="Contact — Niagara Inde Apps" description="Get in touch with Niagara Inde Apps.">
    <section class="mx-auto max-w-2xl px-4 py-14 sm:px-6 lg:px-8">
        <p class="text-label text-niagara-600">Contact</p>
        <h1 class="text-h1 mt-1">Get in touch</h1>
        <p class="text-body mt-3">Questions, feedback, or an idea for a new app &mdash; send a message and we'll reply personally.</p>

        @if (session('status'))
            <x-alert type="success" class="mt-6">{{ session('status') }}</x-alert>
        @endif

        @if ($errors->any())
            <x-alert type="error" class="mt-6">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif

        <form method="post" action="{{ route('contact.store') }}" class="mt-8 space-y-5">
            @csrf

            {{-- Honeypot: hidden from real users, bots often fill every field --}}
            <div class="hidden" aria-hidden="true">
                <label for="website">Leave this field blank</label>
                <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="name" class="text-label">Name</label>
                    <input id="name" name="name" type="text" required value="{{ old('name') }}"
                           class="mt-1.5 w-full rounded-lg border border-border-strong px-3 py-2 focus:border-niagara-500 focus:outline-none">
                </div>
                <div>
                    <label for="email" class="text-label">Email</label>
                    <input id="email" name="email" type="email" required value="{{ old('email') }}"
                           class="mt-1.5 w-full rounded-lg border border-border-strong px-3 py-2 focus:border-niagara-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label for="subject" class="text-label">Subject</label>
                <input id="subject" name="subject" type="text" value="{{ old('subject') }}"
                       class="mt-1.5 w-full rounded-lg border border-border-strong px-3 py-2 focus:border-niagara-500 focus:outline-none">
            </div>

            <div>
                <label for="app_id" class="text-label">Which app is this about? (optional)</label>
                <select id="app_id" name="app_id" class="mt-1.5 w-full rounded-lg border border-border-strong px-3 py-2 focus:border-niagara-500 focus:outline-none">
                    <option value="">Not app-specific</option>
                    @foreach ($apps as $app)
                        <option value="{{ $app->id }}" @selected(old('app_id', request('app')) == $app->id || old('app_id', request('app')) === $app->slug)>{{ $app->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="message" class="text-label">Message</label>
                <textarea id="message" name="message" rows="6" required
                          class="mt-1.5 w-full rounded-lg border border-border-strong px-3 py-2 focus:border-niagara-500 focus:outline-none">{{ old('message') }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary">Send Message</button>
        </form>
    </section>
</x-app-layout>
