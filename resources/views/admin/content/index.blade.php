<x-admin-layout title="Content Pages">
    <x-admin.data-table>
        <table>
            <thead>
                <tr>
                    <th>Page</th>
                    <th>Slug</th>
                    <th>Sections</th>
                    <th>Published</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pages as $page)
                    <tr>
                        <td class="font-semibold">{{ $page->title }}</td>
                        <td class="text-small">/{{ $page->slug }}</td>
                        <td class="text-small">{{ $page->sections()->count() }}</td>
                        <td><span class="badge {{ $page->published ? 'badge-brand' : '' }}">{{ $page->published ? 'Published' : 'Unpublished' }}</span></td>
                        <td class="text-right"><a href="{{ route('admin.content.edit', $page) }}" class="btn btn-ghost !px-3 !py-1.5 text-xs">Edit</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-admin.data-table>
</x-admin-layout>
