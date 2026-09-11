<x-admin-layout title="Apps">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <form method="get" class="flex flex-wrap items-center gap-2">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search apps…" class="rounded-lg border border-border-strong px-3 py-2 text-sm focus:border-niagara-500 focus:outline-none">
            <select name="status" onchange="this.form.submit()" class="rounded-lg border border-border-strong px-3 py-2 text-sm focus:border-niagara-500 focus:outline-none">
                <option value="">All statuses</option>
                @foreach (['draft', 'published', 'archived'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-secondary">Filter</button>
        </form>
        <a href="{{ route('admin.apps.create') }}" class="btn btn-primary">+ Add App</a>
    </div>

    <x-admin.data-table class="mt-6">
        <table>
            <thead>
                <tr>
                    <th>App</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Featured</th>
                    <th>Demo</th>
                    <th>Price</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($apps as $app)
                    <tr>
                        <td class="font-semibold text-navy">{{ $app->name }}</td>
                        <td class="text-small">{{ $app->category?->name ?? '—' }}</td>
                        <td><span class="badge {{ $app->status === 'published' ? 'badge-brand' : '' }}">{{ $app->status }}</span></td>
                        <td class="text-small">{{ $app->is_featured ? 'Yes' : 'No' }}</td>
                        <td class="text-small">{{ $app->demo_enabled ? 'Yes' : 'No' }}</td>
                        <td class="text-small">{{ $app->priceLabel() }}</td>
                        <td>
                            <div class="flex flex-wrap justify-end gap-1.5">
                                <a href="{{ route('admin.apps.edit', $app) }}" class="btn btn-ghost !px-2 !py-1 text-xs">Edit</a>
                                @if ($app->status !== 'published')
                                    <form method="post" action="{{ route('admin.apps.publish', $app) }}">@csrf<button class="btn btn-ghost !px-2 !py-1 text-xs">Publish</button></form>
                                @else
                                    <form method="post" action="{{ route('admin.apps.unpublish', $app) }}">@csrf<button class="btn btn-ghost !px-2 !py-1 text-xs">Unpublish</button></form>
                                @endif
                                <form method="post" action="{{ route('admin.apps.feature', $app) }}">@csrf<button class="btn btn-ghost !px-2 !py-1 text-xs">{{ $app->is_featured ? 'Unfeature' : 'Feature' }}</button></form>
                                <form method="post" action="{{ route('admin.apps.duplicate', $app) }}">@csrf<button class="btn btn-ghost !px-2 !py-1 text-xs">Duplicate</button></form>
                                <form method="post" action="{{ route('admin.apps.destroy', $app) }}" onsubmit="return confirm('Archive/soft-delete {{ $app->name }}?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-ghost !px-2 !py-1 text-xs text-red-600">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-8 text-small">No apps found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-admin.data-table>

    <div class="mt-6">{{ $apps->links() }}</div>
</x-admin-layout>
