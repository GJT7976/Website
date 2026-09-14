<x-admin-layout title="Licenses">
    <form method="get" class="flex flex-wrap gap-2">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Email, name, or transaction ID…" class="rounded-lg border border-border-strong px-3 py-2 text-sm">
        <select name="status" onchange="this.form.submit()" class="rounded-lg border border-border-strong px-3 py-2 text-sm">
            <option value="">All statuses</option>
            @foreach (['active', 'revoked', 'refunded', 'chargeback', 'disabled'] as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-secondary">Filter</button>
    </form>

    <x-admin.data-table class="mt-4">
        <table>
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>App</th>
                    <th>Entitlement</th>
                    <th>Devices</th>
                    <th>Status</th>
                    <th>Purchased</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($licenses as $license)
                    <tr>
                        <td class="text-small">{{ $license->customer_name }}<br>{{ $license->customer_email }}</td>
                        <td class="text-small">{{ $license->app->name }}</td>
                        <td class="text-small">{{ str_replace('_', ' ', $license->platform_entitlement) }}</td>
                        <td class="text-small">{{ $license->activeDevices()->count() }} / {{ $license->maximum_devices }}</td>
                        <td><span class="badge {{ $license->status === 'active' ? 'badge-brand' : '' }}">{{ $license->status }}</span></td>
                        <td class="text-small">{{ $license->purchase_date?->format('M j, Y') }}</td>
                        <td class="text-right"><a href="{{ route('admin.licenses.show', $license) }}" class="btn btn-ghost !px-3 !py-1.5 text-xs">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-8 text-small">No licenses yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-admin.data-table>

    <div class="mt-6">{{ $licenses->links() }}</div>
</x-admin-layout>
