<x-admin-layout title="Users">
    <div class="flex justify-end">
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">+ Add Administrator</a>
    </div>

    <x-admin.data-table class="mt-4">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Active</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td class="font-semibold">{{ $user->name }}</td>
                        <td class="text-small">{{ $user->email }}</td>
                        <td><span class="badge {{ $user->role === 'owner' ? 'badge-brand' : '' }}">{{ $user->role === 'owner' ? 'Owner' : 'Content Editor' }}</span></td>
                        <td class="text-small">{{ $user->is_active ? 'Yes' : 'No' }}</td>
                        <td class="text-right">
                            <div class="flex justify-end gap-1.5">
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-ghost !px-2 !py-1 text-xs">Edit</a>
                                @if ($user->id !== auth()->id())
                                    <form method="post" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Remove {{ $user->name }}?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-ghost !px-2 !py-1 text-xs text-red-600">Remove</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-admin.data-table>
</x-admin-layout>
