<x-admin-layout title="Support Inbox">
    <form method="get" class="flex gap-2">
        <select name="status" onchange="this.form.submit()" class="rounded-lg border border-border-strong px-3 py-2 text-sm">
            <option value="">All statuses</option>
            @foreach (['new', 'read', 'resolved'] as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
    </form>

    <x-admin.data-table class="mt-4">
        <table>
            <thead>
                <tr>
                    <th>From</th>
                    <th>Subject</th>
                    <th>App</th>
                    <th>Status</th>
                    <th>Received</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($requests as $request)
                    <tr>
                        <td>
                            <p class="font-semibold">{{ $request->name }}</p>
                            <p class="text-small">{{ $request->email }}</p>
                        </td>
                        <td class="text-small">{{ $request->subject ?: '—' }}</td>
                        <td class="text-small">{{ $request->app?->name ?? '—' }}</td>
                        <td><span class="badge {{ $request->status === 'new' ? 'badge-brand' : '' }}">{{ $request->status }}</span></td>
                        <td class="text-small">{{ $request->created_at->diffForHumans() }}</td>
                        <td class="text-right"><a href="{{ route('admin.support.show', $request) }}" class="btn btn-ghost !px-3 !py-1.5 text-xs">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-8 text-small">No support requests yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-admin.data-table>

    <div class="mt-6">{{ $requests->links() }}</div>
</x-admin-layout>
