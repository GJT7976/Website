@php
    $isEdit = $taxRule->exists;
    $inputClass = 'w-full rounded-lg border border-border-strong px-3 py-2 focus:border-niagara-500 focus:outline-none';
@endphp

<x-admin-layout :title="$isEdit ? 'Edit Tax Rule' : 'Add Tax Rule'">
    <form method="post" action="{{ $isEdit ? route('admin.tax-rules.update', $taxRule) : route('admin.tax-rules.store') }}" class="card max-w-xl space-y-5 p-6">
        @csrf
        @if ($isEdit) @method('PUT') @endif

        <div class="grid gap-5 sm:grid-cols-2">
            <x-admin.form-field name="country" label="Country (ISO 2-letter, e.g. CA)">
                <input name="country" maxlength="2" required value="{{ old('country', $taxRule->country) }}" class="{{ $inputClass }} uppercase">
            </x-admin.form-field>
            <x-admin.form-field name="province" label="Province/state (blank = whole country)">
                <input name="province" value="{{ old('province', $taxRule->province) }}" class="{{ $inputClass }}">
            </x-admin.form-field>
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <x-admin.form-field name="tax_name" label="Tax name (e.g. HST, GST, PST, QST)">
                <input name="tax_name" required value="{{ old('tax_name', $taxRule->tax_name) }}" class="{{ $inputClass }}">
            </x-admin.form-field>
            <x-admin.form-field name="percentage" label="Percentage">
                <input type="number" step="0.001" min="0" max="100" name="percentage" required value="{{ old('percentage', $taxRule->percentage) }}" class="{{ $inputClass }}">
            </x-admin.form-field>
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <x-admin.form-field name="effective_date" label="Effective date">
                <input type="date" name="effective_date" required value="{{ old('effective_date', $taxRule->effective_date?->format('Y-m-d')) }}" class="{{ $inputClass }}">
            </x-admin.form-field>
            <x-admin.form-field name="expiry_date" label="Expiry date (optional)">
                <input type="date" name="expiry_date" value="{{ old('expiry_date', $taxRule->expiry_date?->format('Y-m-d')) }}" class="{{ $inputClass }}">
            </x-admin.form-field>
        </div>

        <label class="flex items-center gap-2 text-nav">
            <input type="checkbox" name="active" value="1" @checked(old('active', $taxRule->active ?? true)) class="rounded border-border-strong">
            Active
        </label>

        <x-admin.form-field name="notes" label="Notes / source (optional)">
            <textarea name="notes" rows="2" class="{{ $inputClass }}">{{ old('notes', $taxRule->notes) }}</textarea>
        </x-admin.form-field>

        <div class="flex gap-3">
            <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Save Changes' : 'Add Tax Rule' }}</button>
            <a href="{{ route('admin.tax-rules.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</x-admin-layout>
