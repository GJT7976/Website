@php
    $navGroups = [
        'Overview' => [
            ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => '🏠'],
            ['label' => 'Two-Factor Auth', 'route' => 'admin.two-factor.edit', 'icon' => '🔒'],
        ],
        'Catalogue' => [
            ['label' => 'Apps', 'route' => 'admin.apps.index', 'icon' => '📦'],
            ['label' => 'Media Library', 'route' => 'admin.media.index', 'icon' => '🖼️'],
        ],
        'Sales' => [
            ['label' => 'Orders', 'route' => 'admin.orders.index', 'icon' => '🧾'],
            ['label' => 'Sales', 'route' => 'admin.sales.index', 'icon' => '📈'],
            ['label' => 'Licenses', 'route' => 'admin.licenses.index', 'icon' => '🔑'],
        ],
        'Content' => [
            ['label' => 'Pages', 'route' => 'admin.content.index', 'icon' => '📄'],
            ['label' => 'FAQs', 'route' => 'admin.faqs.index', 'icon' => '❓'],
            ['label' => 'Support Inbox', 'route' => 'admin.support.index', 'icon' => '✉️'],
        ],
    ];

    $ownerNavGroups = [
        'Administration' => [
            ['label' => 'Users', 'route' => 'admin.users.index', 'icon' => '👤'],
            ['label' => 'Taxes', 'route' => 'admin.tax-rules.index', 'icon' => '🏛️'],
            ['label' => 'Business Settings', 'route' => 'admin.settings.edit', 'params' => ['group' => 'business'], 'icon' => '🏢'],
            ['label' => 'Site Settings', 'route' => 'admin.settings.edit', 'params' => ['group' => 'site'], 'icon' => '🎨'],
            ['label' => 'Store Settings', 'route' => 'admin.settings.edit', 'params' => ['group' => 'store'], 'icon' => '🛒'],
            ['label' => 'Payments', 'route' => 'admin.settings.edit', 'params' => ['group' => 'payments'], 'icon' => '💳'],
            ['label' => 'Audit Log', 'route' => 'admin.audit-log.index', 'icon' => '📜'],
            ['label' => 'Backups', 'route' => 'admin.backups.index', 'icon' => '💾'],
            ['label' => 'Maintenance Mode', 'route' => 'admin.maintenance.edit', 'icon' => '🚧'],
            ['label' => 'Seed Data', 'route' => 'admin.seed.edit', 'icon' => '🌱'],
        ],
    ];

    $notYetImplemented = ['Accounting', 'Expenses'];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Admin' }} — Niagara Inde Apps</title>
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-offwhite text-navy antialiased" x-data="{ sidebarOpen: false }">
    <div class="flex min-h-screen">
        {{-- SIDEBAR --}}
        <aside class="fixed inset-y-0 left-0 z-30 w-64 -translate-x-full border-r border-border bg-white transition-transform lg:static lg:translate-x-0"
               :class="sidebarOpen && '!translate-x-0'">
            <div class="flex h-16 items-center gap-2 border-b border-border px-5">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-niagara-500 text-white text-sm font-bold">N</span>
                <span class="text-nav font-bold">Niagara Admin</span>
            </div>
            <nav class="space-y-6 overflow-y-auto px-3 py-5" style="height: calc(100vh - 4rem);">
                @foreach ($navGroups as $group => $items)
                    <div>
                        <p class="text-label px-3">{{ $group }}</p>
                        <div class="mt-1 space-y-0.5">
                            @foreach ($items as $item)
                                <a href="{{ route($item['route']) }}" class="flex items-center gap-2 rounded-lg px-3 py-2 text-nav text-navy-soft hover:bg-mist {{ request()->routeIs($item['route'].'*') ? 'bg-niagara-50 font-semibold text-niagara-700' : '' }}">
                                    <span aria-hidden="true">{{ $item['icon'] }}</span> {{ $item['label'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                @auth
                    @if (auth()->user()->isOwner())
                        @foreach ($ownerNavGroups as $group => $items)
                            <div>
                                <p class="text-label px-3">{{ $group }}</p>
                                <div class="mt-1 space-y-0.5">
                                    @foreach ($items as $item)
                                        <a href="{{ route($item['route'], $item['params'] ?? []) }}" class="flex items-center gap-2 rounded-lg px-3 py-2 text-nav text-navy-soft hover:bg-mist {{ request()->routeIs($item['route'].'*') ? 'bg-niagara-50 font-semibold text-niagara-700' : '' }}">
                                            <span aria-hidden="true">{{ $item['icon'] }}</span> {{ $item['label'] }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    @endif
                @endauth

                <div>
                    <p class="text-label px-3">Not Yet Implemented</p>
                    <div class="mt-1 space-y-0.5">
                        @foreach ($notYetImplemented as $label)
                            <span class="flex cursor-not-allowed items-center gap-2 rounded-lg px-3 py-2 text-nav text-ink-muted">
                                <span aria-hidden="true">🔒</span> {{ $label }} <span class="text-small ms-auto">Soon</span>
                            </span>
                        @endforeach
                    </div>
                </div>
            </nav>
        </aside>

        <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" class="fixed inset-0 z-20 bg-navy/40 lg:hidden" style="display:none"></div>

        {{-- MAIN --}}
        <div class="flex min-w-0 flex-1 flex-col">
            <header class="flex h-16 items-center justify-between border-b border-border bg-white px-4 sm:px-6">
                <div class="flex items-center gap-3">
                    <button type="button" @click="sidebarOpen = true" class="rounded-md p-2 text-navy lg:hidden" aria-label="Open menu">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                    </button>
                    <h1 class="text-h3">{{ $title ?? 'Dashboard' }}</h1>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('home') }}" target="_blank" class="text-small hidden font-semibold text-niagara-600 hover:underline sm:inline">View site &rarr;</a>
                    @auth
                        <span class="text-small hidden sm:inline">{{ auth()->user()->name }} &middot; {{ auth()->user()->role === 'owner' ? 'Owner' : 'Content Editor' }}</span>
                        <form method="post" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-ghost">Log out</button>
                        </form>
                    @endauth
                </div>
            </header>

            <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
                @if (app()->isDownForMaintenance())
                    <div class="mb-6 flex flex-wrap items-center justify-between gap-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-small text-amber-900">
                        <span>🚧 Maintenance mode is on — visitors see the maintenance page.</span>
                        @if (auth()->user()?->isOwner() && ! request()->routeIs('admin.maintenance.edit'))
                            <a href="{{ route('admin.maintenance.edit') }}" class="font-semibold underline">Turn it off</a>
                        @endif
                    </div>
                @endif
                @if (session('status'))
                    <x-alert type="success" class="mb-6">{{ session('status') }}</x-alert>
                @endif
                @if ($errors->any())
                    <x-alert type="error" class="mb-6">
                        <ul class="list-inside list-disc space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </x-alert>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
