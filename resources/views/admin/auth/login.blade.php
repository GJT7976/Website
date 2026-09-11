<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login — Niagara Inde Apps</title>
    <link rel="icon" href="data:,">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-offwhite px-4 antialiased">
    <div class="w-full max-w-sm">
        <div class="mb-6 text-center">
            <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-niagara-500 text-lg font-bold text-white">N</span>
            <h1 class="text-h2 mt-3">Niagara Inde Apps</h1>
            <p class="text-small">Administrator sign in</p>
        </div>

        <div class="card p-6">
            @if ($errors->any())
                <x-alert type="error" class="mb-5">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </x-alert>
            @endif

            <form method="post" action="{{ route('admin.login.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="email" class="text-label">Email</label>
                    <input id="email" name="email" type="email" required autofocus value="{{ old('email') }}"
                           class="mt-1.5 w-full rounded-lg border border-border-strong px-3 py-2 focus:border-niagara-500 focus:outline-none">
                </div>
                <div>
                    <label for="password" class="text-label">Password</label>
                    <input id="password" name="password" type="password" required
                           class="mt-1.5 w-full rounded-lg border border-border-strong px-3 py-2 focus:border-niagara-500 focus:outline-none">
                </div>
                <label class="flex items-center gap-2 text-small">
                    <input type="checkbox" name="remember" class="rounded border-border-strong">
                    Remember me
                </label>
                <button type="submit" class="btn btn-primary w-full">Sign In</button>
            </form>
        </div>

        <p class="text-small mt-6 text-center"><a href="{{ route('home') }}" class="hover:text-niagara-600">&larr; Back to the website</a></p>
    </div>
</body>
</html>
