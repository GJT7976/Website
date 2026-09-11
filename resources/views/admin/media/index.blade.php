<x-admin-layout title="Media Library">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <form method="get" class="flex gap-2">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search by title or alt text…" class="rounded-lg border border-border-strong px-3 py-2 text-sm focus:border-niagara-500 focus:outline-none">
            <button type="submit" class="btn btn-secondary">Search</button>
        </form>
    </div>

    <div class="card mt-6 p-6">
        <h2 class="text-h3">Upload new image</h2>
        <form method="post" action="{{ route('admin.media.store') }}" enctype="multipart/form-data" class="mt-4 grid gap-4 sm:grid-cols-2">
            @csrf
            <x-admin.form-field name="file" label="Image file" class="sm:col-span-2">
                <input type="file" name="file" required accept="image/png,image/jpeg,image/gif,image/webp" class="w-full rounded-lg border border-border-strong px-3 py-2">
            </x-admin.form-field>
            <x-admin.form-field name="title" label="Title">
                <input type="text" name="title" class="w-full rounded-lg border border-border-strong px-3 py-2">
            </x-admin.form-field>
            <x-admin.form-field name="alt_text" label="Alt text">
                <input type="text" name="alt_text" class="w-full rounded-lg border border-border-strong px-3 py-2">
            </x-admin.form-field>
            <div class="sm:col-span-2">
                <button type="submit" class="btn btn-primary">Upload</button>
            </div>
        </form>
    </div>

    <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
        @forelse ($media as $item)
            <div class="card p-3">
                <div class="aspect-square overflow-hidden rounded-lg bg-mist">
                    <img src="{{ $item->thumbnailUrl() }}" alt="{{ $item->alt_text }}" class="h-full w-full object-cover">
                </div>
                <p class="text-small mt-2 truncate" title="{{ $item->title }}">{{ $item->title ?: 'Untitled' }}</p>
                <p class="text-small">{{ number_format($item->size / 1024, 0) }} KB</p>
                <form method="post" action="{{ route('admin.media.destroy', $item) }}" class="mt-2" onsubmit="return confirm('Delete this image?');">
                    @csrf @method('DELETE')
                    <button class="text-small text-red-600 hover:underline">Delete</button>
                </form>
            </div>
        @empty
            <p class="text-small col-span-full py-8 text-center">No images uploaded yet.</p>
        @endforelse
    </div>

    <div class="mt-6">{{ $media->links() }}</div>
</x-admin-layout>
