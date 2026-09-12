<x-admin-layout title="Audit Log">
    <form method="get" class="card flex flex-wrap items-end gap-4 p-4">
        <x-admin.form-field name="user_id" label="Administrator">
            <select name="user_id" class="w-full rounded-lg border border-border-strong px-3 py-2 text-sm">
                <option value="">All</option>
                @foreach ($admins as $admin)
                    <option value="{{ $admin->id }}" @selected(request('user_id') == $admin->id)>{{ $admin->name }}</option>
                @endforeach
            </select>
        </x-admin.form-field>
        <x-admin.form-field name="action" label="Action contains">
            <input type="text" name="action" value="{{ request('action') }}" placeholder="e.g. app, refund…" class="w-full rounded-lg border border-border-strong px-3 py-2 text-sm">
        </x-admin.form-field>
        <x-admin.form-field name="from" label="From">
            <input type="date" name="from" value="{{ request('from') }}" class="rounded-lg border border-border-strong px-3 py-2 text-sm">
        </x-admin.form-field>
        <x-admin.form-field name="to" label="To">
            <input type="date" name="to" value="{{ request('to') }}" class="rounded-lg border border-border-strong px-3 py-2 text-sm">
        </x-admin.form-field>
        <button type="submit" class="btn btn-secondary">Filter</button>
        @if (request()->anyFilled(['user_id', 'action', 'from', 'to']))
            <a href="{{ route('admin.audit-log.index') }}" class="text-small text-navy-soft hover:underline">Clear</a>
        @endif
    </form>

    <div class="mt-6" x-data="{ open: null }">
        <x-admin.data-table>
            <table>
                <thead>
                    <tr>
                        <th>When</th>
                        <th>Administrator</th>
                        <th>Action</th>
                        <th>Resource</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td class="whitespace-nowrap">{{ $log->created_at->format('Y-m-d H:i') }}</td>
                            <td>{{ $log->administrator?->name ?? 'System' }}</td>
                            <td><code class="text-small">{{ $log->action }}</code></td>
                            <td>{{ $log->resource_label ?? '—' }}</td>
                            <td class="text-right">
                                @if ($log->before || $log->after)
                                    <button type="button" @click="open = open === {{ $log->id }} ? null : {{ $log->id }}" class="text-small text-niagara-600 hover:underline">
                                        <span x-text="open === {{ $log->id }} ? 'Hide' : 'Details'"></span>
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @if ($log->before || $log->after)
                            <tr x-show="open === {{ $log->id }}" x-cloak>
                                <td colspan="5" class="bg-mist/40">
                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <div>
                                            <p class="text-label">Before</p>
                                            <pre class="text-small mt-1 overflow-x-auto rounded-lg bg-white p-3">{{ json_encode($log->before, JSON_PRETTY_PRINT) ?: '—' }}</pre>
                                        </div>
                                        <div>
                                            <p class="text-label">After</p>
                                            <pre class="text-small mt-1 overflow-x-auto rounded-lg bg-white p-3">{{ json_encode($log->after, JSON_PRETTY_PRINT) ?: '—' }}</pre>
                                        </div>
                                    </div>
                                    <p class="text-small mt-2 text-navy-soft">IP: {{ $log->ip_address ?? '—' }}</p>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center">No audit log entries yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-admin.data-table>
    </div>

    <div class="mt-6">{{ $logs->links() }}</div>
</x-admin-layout>
