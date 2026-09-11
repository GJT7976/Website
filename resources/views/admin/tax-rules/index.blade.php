<x-admin-layout title="Taxes">
    <x-alert type="info">
        These rates apply automatically to checkout. Verify current Canadian/provincial tax requirements against
        authoritative sources (e.g. the CRA) before relying on this for a real launch — this configuration does not
        by itself guarantee compliance.
    </x-alert>

    <div class="mt-4 flex justify-end">
        <a href="{{ route('admin.tax-rules.create') }}" class="btn btn-primary">+ Add Tax Rule</a>
    </div>

    <x-admin.data-table class="mt-4">
        <table>
            <thead>
                <tr>
                    <th>Country</th>
                    <th>Province</th>
                    <th>Name</th>
                    <th>Rate</th>
                    <th>Effective</th>
                    <th>Expires</th>
                    <th>Active</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($taxRules as $rule)
                    <tr>
                        <td>{{ $rule->country }}</td>
                        <td class="text-small">{{ $rule->province ?? 'Whole country' }}</td>
                        <td class="font-semibold">{{ $rule->tax_name }}</td>
                        <td class="text-small">{{ rtrim(rtrim($rule->percentage, '0'), '.') }}%</td>
                        <td class="text-small">{{ $rule->effective_date->format('M j, Y') }}</td>
                        <td class="text-small">{{ $rule->expiry_date?->format('M j, Y') ?? '—' }}</td>
                        <td><span class="badge {{ $rule->active ? 'badge-brand' : '' }}">{{ $rule->active ? 'Yes' : 'No' }}</span></td>
                        <td class="text-right">
                            <div class="flex justify-end gap-1.5">
                                <a href="{{ route('admin.tax-rules.edit', $rule) }}" class="btn btn-ghost !px-2 !py-1 text-xs">Edit</a>
                                <form method="post" action="{{ route('admin.tax-rules.destroy', $rule) }}" onsubmit="return confirm('Delete this tax rule?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-ghost !px-2 !py-1 text-xs text-red-600">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center py-8 text-small">No tax rules configured — checkout will apply $0 tax until one exists.</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-admin.data-table>
</x-admin-layout>
