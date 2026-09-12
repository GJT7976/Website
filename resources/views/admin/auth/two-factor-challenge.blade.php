<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Two-Factor Verification — Niagara Inde Apps</title>
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-offwhite px-4 antialiased">
    <div class="w-full max-w-sm">
        <div class="mb-6 text-center">
            <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-niagara-500 text-lg font-bold text-white">N</span>
            <h1 class="text-h2 mt-3">Two-Factor Verification</h1>
            <p class="text-small">Enter the 6-digit code from your authenticator app, or a recovery code.</p>
        </div>

        <div class="card p-6">
            @if ($errors->any())
                <x-alert type="error" class="mb-5">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </x-alert>
            @endif

            <form method="post" action="{{ route('admin.two-factor.challenge.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="code" class="text-label">Code</label>
                    <input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" required autofocus
                           class="mt-1.5 w-full rounded-lg border border-border-strong px-3 py-2 text-center text-lg tracking-widest focus:border-niagara-500 focus:outline-none">
                </div>
                <button type="submit" class="btn btn-primary w-full">Verify</button>
            </form>
        </div>

        <p class="text-small mt-6 text-center"><a href="{{ route('admin.login') }}" class="hover:text-niagara-600">&larr; Back to sign in</a></p>
    </div>
</body>
</html>
