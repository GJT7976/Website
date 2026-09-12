<x-admin-layout title="Backups">
    <div class="card flex flex-wrap gap-3 p-4">
        @foreach (['database' => 'Back up database', 'media' => 'Back up media', 'full' => 'Full backup'] as $type => $label)
            <form method="post" action="{{ route('admin.backups.store') }}">
                @csrf
                <input type="hidden" name="type" value="{{ $type }}">
                <button type="submit" class="btn btn-secondary">{{ $label }}</button>
            </form>
        @endforeach
    </div>

    <div class="mt-6">
        <x-admin.data-table>
            <table>
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Filename</th>
                        <th>Size</th>
                        <th>Checksum</th>
                        <th>Created by</th>
                        <th>Created</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($backups as $backup)
                        <tr>
                            <td><span class="badge badge-brand">{{ ucfirst($backup->type) }}</span></td>
                            <td>{{ $backup->filename }}</td>
                            <td>{{ $backup->humanSize() }}</td>
                            <td><code class="text-small" title="{{ $backup->checksum_sha256 }}">{{ substr($backup->checksum_sha256, 0, 12) }}…</code></td>
                            <td>{{ $backup->creator?->name ?? 'System (CLI)' }}</td>
                            <td class="whitespace-nowrap">{{ $backup->created_at->format('Y-m-d H:i') }}</td>
                            <td class="text-right">
                                <div class="flex justify-end gap-3">
                                    <a href="{{ route('admin.backups.download', $backup) }}" class="text-small text-niagara-600 hover:underline">Download</a>
                                    <form method="post" action="{{ route('admin.backups.destroy', $backup) }}" onsubmit="return confirm('Delete this backup? This cannot be undone.');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-small text-red-600 hover:underline">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center">No backups yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-admin.data-table>
    </div>
</x-admin-layout>
