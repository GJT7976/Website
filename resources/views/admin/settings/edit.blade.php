@php
    $inputClass = 'w-full rounded-lg border border-border-strong px-3 py-2 focus:border-niagara-500 focus:outline-none';

    $fieldsByGroup = [
        'business' => [
            'business_name' => 'Business name',
            'legal_name' => 'Legal name (if different)',
            'address' => 'Street address',
            'city' => 'City',
            'province' => 'Province',
            'postal_code' => 'Postal code',
            'country' => 'Country',
            'email' => 'Business email',
            'telephone' => 'Telephone',
        ],
        'site' => [
            'hero_title' => 'Hero title',
            'hero_subtitle' => 'Hero subtitle',
            'hero_description' => 'Hero description',
            'footer_tagline' => 'Footer tagline',
            'seo_default_title' => 'Default SEO title',
            'seo_default_description' => 'Default SEO description',
        ],
        'store' => [
            'default_currency' => 'Default currency',
            'order_prefix' => 'Order number prefix',
        ],
    ][$group] ?? [];

    $groupLabels = ['business' => 'Business Settings', 'site' => 'Site Settings', 'store' => 'Store Settings'];
@endphp

<x-admin-layout :title="$groupLabels[$group] ?? 'Settings'">
    <div class="flex gap-2">
        @foreach (['business', 'site', 'store'] as $g)
            <a href="{{ route('admin.settings.edit', $g) }}" class="badge {{ $group === $g ? 'badge-brand' : '' }}">{{ $groupLabels[$g] }}</a>
        @endforeach
    </div>

    <form method="post" action="{{ route('admin.settings.update', $group) }}" class="card mt-6 max-w-2xl space-y-5 p-6">
        @csrf
        @method('PUT')

        @foreach ($fieldsByGroup as $key => $label)
            <x-admin.form-field :name="$key" :label="$label">
                <input name="{{ $key }}" value="{{ old($key, $values[$key] ?? '') }}" class="{{ $inputClass }}">
            </x-admin.form-field>
        @endforeach

        <button type="submit" class="btn btn-primary">Save Settings</button>
    </form>
</x-admin-layout>
