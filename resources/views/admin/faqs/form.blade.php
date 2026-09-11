@php
    $isEdit = $faq->exists;
    $inputClass = 'w-full rounded-lg border border-border-strong px-3 py-2 focus:border-niagara-500 focus:outline-none';
@endphp

<x-admin-layout :title="$isEdit ? 'Edit FAQ' : 'Add FAQ'">
    <form method="post" action="{{ $isEdit ? route('admin.faqs.update', $faq) : route('admin.faqs.store') }}" class="card max-w-2xl space-y-5 p-6">
        @csrf
        @if ($isEdit) @method('PUT') @endif

        <x-admin.form-field name="app_id" label="App (optional — leave blank for a general/site-wide FAQ)">
            <select name="app_id" class="{{ $inputClass }}">
                <option value="">General</option>
                @foreach ($apps as $app)
                    <option value="{{ $app->id }}" @selected(old('app_id', $faq->app_id) == $app->id)>{{ $app->name }}</option>
                @endforeach
            </select>
        </x-admin.form-field>

        <x-admin.form-field name="question" label="Question">
            <input name="question" value="{{ old('question', $faq->question) }}" required class="{{ $inputClass }}">
        </x-admin.form-field>

        <x-admin.form-field name="answer" label="Answer">
            <textarea name="answer" rows="5" required class="{{ $inputClass }}">{{ old('answer', $faq->answer) }}</textarea>
        </x-admin.form-field>

        <x-admin.form-field name="category" label="Category (optional)">
            <input name="category" value="{{ old('category', $faq->category) }}" class="{{ $inputClass }}">
        </x-admin.form-field>

        <label class="flex items-center gap-2 text-nav">
            <input type="checkbox" name="published" value="1" @checked(old('published', $faq->published ?? true)) class="rounded border-border-strong">
            Published
        </label>

        <div class="flex gap-3">
            <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Save Changes' : 'Add FAQ' }}</button>
            <a href="{{ route('admin.faqs.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</x-admin-layout>
