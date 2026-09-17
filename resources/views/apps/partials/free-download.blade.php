@props(['app'])

{{--
    Free-install section for an app sold as "free app + optional Pro
    unlock" (App::is_free with a single Pro-unlock AppEdition) rather than
    "pay to get the platform build" — see The Stock Pot's seeder comment
    for why this differs from every other app on this site.
--}}
<section id="free-download" class="mx-auto max-w-7xl px-4 pt-14 sm:px-6 lg:px-8">
    <div class="card p-6">
        <h2 class="text-h2">Download {{ $app->name }} — Free</h2>
        <p class="text-body mt-2">The full app is free to install. Every core feature works with no purchase — Pro is an optional one-time unlock below.</p>

        <div class="mt-5 flex flex-wrap gap-3">
            @foreach ($app->platforms as $platform)
                @if (in_array($platform->code, ['android', 'windows'], true))
                    @php($release = $app->currentRelease($platform))
                    @if ($release && $release->customer_downloadable)
                        <a href="{{ route('apps.free-download', [$app, $platform]) }}" class="btn btn-primary">
                            Download for {{ $platform->name }} ({{ $release->fileSizeLabel() }})
                        </a>
                    @endif
                @endif
            @endforeach
        </div>

        @if (in_array($app->android_delivery_mode, ['direct', 'both']))
            <x-alert type="info" class="mt-5">
                <strong>Direct Android Download.</strong> This Android app installs directly from Niagara Inde Apps rather than through Google Play. Android may ask you to allow installation from this source.
                <a href="{{ route('support.install.android') }}" class="font-semibold underline">Learn how to install</a>.
            </x-alert>
        @endif
    </div>
</section>
