@php
    $isEdit = $user->exists;
    $inputClass = 'w-full rounded-lg border border-border-strong px-3 py-2 focus:border-niagara-500 focus:outline-none';
@endphp

<x-admin-layout :title="$isEdit ? 'Edit Administrator' : 'Add Administrator'">
    <form method="post" action="{{ $isEdit ? route('admin.users.update', $user) : route('admin.users.store') }}" class="card max-w-lg space-y-5 p-6">
        @csrf
        @if ($isEdit) @method('PUT') @endif

        <x-admin.form-field name="name" label="Name">
            <input name="name" value="{{ old('name', $user->name) }}" required class="{{ $inputClass }}">
        </x-admin.form-field>

        <x-admin.form-field name="email" label="Email">
            <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="{{ $inputClass }}">
        </x-admin.form-field>

        <x-admin.form-field name="role" label="Role">
            <select name="role" class="{{ $inputClass }}">
                <option value="content_editor" @selected(old('role', $user->role ?? 'content_editor') === 'content_editor')>Content Editor</option>
                <option value="owner" @selected(old('role', $user->role ?? '') === 'owner')>Owner (full access)</option>
            </select>
        </x-admin.form-field>

        @if ($isEdit)
            <label class="flex items-center gap-2 text-nav">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active)) class="rounded border-border-strong">
                Active (can sign in)
            </label>
        @endif

        <x-admin.form-field name="password" :label="$isEdit ? 'New password (leave blank to keep current)' : 'Password'" hint="Minimum 12 characters.">
            <input type="password" name="password" {{ $isEdit ? '' : 'required' }} class="{{ $inputClass }}">
        </x-admin.form-field>

        <x-admin.form-field name="password_confirmation" label="Confirm password">
            <input type="password" name="password_confirmation" {{ $isEdit ? '' : 'required' }} class="{{ $inputClass }}">
        </x-admin.form-field>

        <div class="flex gap-3">
            <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Save Changes' : 'Create Administrator' }}</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</x-admin-layout>
