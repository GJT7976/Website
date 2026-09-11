<div class="card p-6">
    <h2 class="text-h3">Feature Bullets</h2>

    <ul class="mt-4 divide-y divide-border">
        @forelse ($app->features as $feature)
            <li class="flex items-start justify-between gap-3 py-3">
                <div>
                    <p class="font-semibold">{{ $feature->icon }} {{ $feature->title }}</p>
                    @if ($feature->description)
                        <p class="text-small mt-0.5">{{ $feature->description }}</p>
                    @endif
                </div>
                <form method="post" action="{{ route('admin.apps.features.destroy', [$app, $feature]) }}">
                    @csrf @method('DELETE')
                    <button class="btn btn-ghost !px-2 !py-1 text-xs text-red-600">Remove</button>
                </form>
            </li>
        @empty
            <li class="text-small py-3">No features added yet.</li>
        @endforelse
    </ul>

    <form method="post" action="{{ route('admin.apps.features.store', $app) }}" class="mt-4 grid gap-2 sm:grid-cols-[3rem_1fr_1fr_auto]">
        @csrf
        <input type="text" name="icon" placeholder="🌾" maxlength="10" class="rounded-lg border border-border-strong px-2 py-2 text-center">
        <input type="text" name="title" placeholder="Feature title" required class="rounded-lg border border-border-strong px-3 py-2">
        <input type="text" name="description" placeholder="Short description (optional)" class="rounded-lg border border-border-strong px-3 py-2">
        <button type="submit" class="btn btn-secondary">Add</button>
    </form>
</div>
