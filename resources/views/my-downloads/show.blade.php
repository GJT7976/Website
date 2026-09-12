<x-app-layout :title="'My Apps — Niagara Inde Apps'">
    <section class="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8">
        <p class="text-label text-niagara-600">My Downloads</p>
        <h1 class="text-h1 mt-1">My Apps</h1>
        <p class="text-body mt-2">Purchases for {{ $email }}.</p>

        @if (session('status'))
            <x-alert type="success" class="mt-6">{{ session('status') }}</x-alert>
        @endif

        @forelse ($downloads as $item)
            @php $app = $item['app']; @endphp
            <div class="card mt-6 p-6">
                <h2 class="text-h3">{{ $app->name }}</h2>

                @foreach ($item['editions'] as $group)
                    @if ($group['edition'])
                        <p class="text-label mt-5">{{ $group['edition']->name }}</p>
                    @endif

                    <div class="mt-3 space-y-4">
                        @foreach ($group['entitlements'] as $row)
                            @php $entitlement = $row['entitlement']; @endphp

                            @if ($entitlement->access_type === 'web_access')
                                <div class="flex items-center justify-between gap-4 rounded-lg border border-border p-4">
                                    <div>
                                        <p class="font-semibold">Web App</p>
                                        <p class="text-small">Status: Active</p>
                                    </div>
                                    <a href="{{ $app->web_app_url }}" target="_blank" rel="noopener" class="btn btn-primary whitespace-nowrap">Launch Web App</a>
                                </div>
                            @else
                                <div class="flex items-center justify-between gap-4 rounded-lg border border-border p-4">
                                    <div>
                                        <p class="font-semibold">{{ $entitlement->platform->name }}</p>
                                        @if ($row['release'])
                                            <p class="text-small">Version {{ $row['release']->version }} &middot; Updated {{ $row['release']->released_at->format('F j, Y') }}</p>
                                        @else
                                            <p class="text-small">No release available yet — check back soon.</p>
                                        @endif
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        @if ($row['download_url'])
                                            <a href="{{ $row['download_url'] }}" class="btn btn-primary whitespace-nowrap">Download for {{ $entitlement->platform->name }}</a>
                                        @endif
                                        @if ($entitlement->platform->code === 'android')
                                            <a href="{{ route('support.install.android') }}" class="btn btn-secondary whitespace-nowrap">Installation Help</a>
                                        @elseif ($entitlement->platform->code === 'windows')
                                            <a href="{{ route('support.install.windows') }}" class="btn btn-secondary whitespace-nowrap">Installation Help</a>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endforeach
            </div>
        @empty
            <div class="card mt-6 p-6 text-center">
                <p class="text-body">We couldn't find any purchases for this email.</p>
                <p class="text-small mt-2">If you think this is a mistake, please <a href="{{ route('contact') }}" class="font-semibold text-niagara-600 hover:underline">contact us</a>.</p>
            </div>
        @endforelse
    </section>
</x-app-layout>
