@php
    $icon = $app->icon();
    $featureGraphic = $app->featureGraphic();
    $screenshots = $app->screenshots();
@endphp

<div class="card p-6">
    <h2 class="text-h3">Images</h2>

    <div class="mt-4 space-y-6">
        {{-- Icon --}}
        <div>
            <p class="text-label">Icon</p>
            <div class="mt-2 flex items-center gap-3">
                <div class="flex h-16 w-16 items-center justify-center overflow-hidden rounded-lg border border-border bg-mist">
                    @if ($icon)
                        <img src="{{ $icon->thumbnailUrl() }}" alt="" class="h-full w-full object-cover">
                    @endif
                </div>
                <form method="post" action="{{ route('admin.apps.media.attach', $app) }}" enctype="multipart/form-data" class="flex items-center gap-2">
                    @csrf
                    <input type="hidden" name="type" value="icon">
                    <input type="file" name="file" accept="image/png,image/jpeg,image/gif,image/webp" required class="text-small">
                    <button type="submit" class="btn btn-secondary !px-3 !py-1.5 text-xs">Upload</button>
                </form>
                @if ($icon)
                    <form method="post" action="{{ route('admin.apps.media.detach', [$app, $icon]) }}">
                        @csrf @method('DELETE')
                        <button class="btn btn-ghost !px-2 !py-1 text-xs text-red-600">Remove</button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Feature graphic --}}
        <div>
            <p class="text-label">Feature graphic</p>
            <div class="mt-2 flex items-center gap-3">
                <div class="flex h-16 w-28 items-center justify-center overflow-hidden rounded-lg border border-border bg-mist">
                    @if ($featureGraphic)
                        <img src="{{ $featureGraphic->thumbnailUrl() }}" alt="" class="h-full w-full object-cover">
                    @endif
                </div>
                <form method="post" action="{{ route('admin.apps.media.attach', $app) }}" enctype="multipart/form-data" class="flex items-center gap-2">
                    @csrf
                    <input type="hidden" name="type" value="feature_graphic">
                    <input type="file" name="file" accept="image/png,image/jpeg,image/gif,image/webp" required class="text-small">
                    <button type="submit" class="btn btn-secondary !px-3 !py-1.5 text-xs">Upload</button>
                </form>
                @if ($featureGraphic)
                    <form method="post" action="{{ route('admin.apps.media.detach', [$app, $featureGraphic]) }}">
                        @csrf @method('DELETE')
                        <button class="btn btn-ghost !px-2 !py-1 text-xs text-red-600">Remove</button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Screenshots --}}
        <div>
            <p class="text-label">Screenshots</p>
            <div class="mt-2 flex flex-wrap gap-3">
                @foreach ($screenshots as $shot)
                    <div class="relative">
                        <img src="{{ $shot->thumbnailUrl() }}" alt="" class="h-24 w-auto rounded-lg border border-border object-cover">
                        <form method="post" action="{{ route('admin.apps.media.detach', [$app, $shot]) }}" class="absolute -right-2 -top-2">
                            @csrf @method('DELETE')
                            <button class="flex h-6 w-6 items-center justify-center rounded-full bg-white text-red-600 shadow-soft" title="Remove" aria-label="Remove screenshot">&times;</button>
                        </form>
                    </div>
                @endforeach
            </div>
            <form method="post" action="{{ route('admin.apps.media.attach', $app) }}" enctype="multipart/form-data" class="mt-3 flex items-center gap-2">
                @csrf
                <input type="hidden" name="type" value="screenshot">
                <input type="file" name="file" accept="image/png,image/jpeg,image/gif,image/webp" required class="text-small">
                <button type="submit" class="btn btn-secondary !px-3 !py-1.5 text-xs">Add Screenshot</button>
            </form>
        </div>
    </div>
</div>
