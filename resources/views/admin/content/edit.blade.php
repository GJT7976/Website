@php
    $inputClass = 'w-full rounded-lg border border-border-strong px-3 py-2 focus:border-niagara-500 focus:outline-none';
    $sectionsJson = $page->sections->map(fn ($s) => [
        'id' => $s->id, 'heading' => $s->heading, 'body' => $s->body, 'media_id' => $s->media_id,
    ])->values();
@endphp

<x-admin-layout :title="'Edit: '.$page->title">
    <form method="post" action="{{ route('admin.content.update', $page) }}" x-data="{ sections: {{ $sectionsJson }} }">
        @csrf
        @method('PUT')

        <section class="card p-6">
            <h2 class="text-h3">Page Details</h2>
            <div class="mt-4 grid gap-5 sm:grid-cols-2">
                <x-admin.form-field name="title" label="Title">
                    <input id="title" name="title" value="{{ old('title', $page->title) }}" required class="{{ $inputClass }}">
                </x-admin.form-field>
                <label class="flex items-center gap-2 text-nav mt-6">
                    <input type="checkbox" name="published" value="1" @checked(old('published', $page->published)) class="rounded border-border-strong">
                    Published
                </label>
                <x-admin.form-field name="meta_title" label="Meta title">
                    <input id="meta_title" name="meta_title" value="{{ old('meta_title', $page->meta_title) }}" class="{{ $inputClass }}">
                </x-admin.form-field>
                <x-admin.form-field name="meta_description" label="Meta description">
                    <input id="meta_description" name="meta_description" value="{{ old('meta_description', $page->meta_description) }}" class="{{ $inputClass }}">
                </x-admin.form-field>
            </div>
        </section>

        <section class="card mt-6 p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-h3">Sections</h2>
                <button type="button" class="btn btn-secondary" @click="sections.push({ id: null, heading: '', body: '', media_id: null })">+ Add Section</button>
            </div>

            <template x-for="(section, index) in sections" :key="index">
                <div class="mt-4 rounded-xl border border-border p-4">
                    <div class="flex items-center justify-between">
                        <p class="text-label">Section <span x-text="index + 1"></span></p>
                        <button type="button" class="text-small text-red-600 hover:underline" @click="sections.splice(index, 1)">Remove</button>
                    </div>
                    <input type="hidden" :name="`sections[${index}][id]`" x-model="section.id">
                    <div class="mt-2">
                        <label class="text-label">Heading</label>
                        <input type="text" :name="`sections[${index}][heading]`" x-model="section.heading" class="{{ $inputClass }} mt-1">
                    </div>
                    <div class="mt-3">
                        <label class="text-label">Body</label>
                        <textarea :name="`sections[${index}][body]`" x-model="section.body" rows="4" class="{{ $inputClass }} mt-1"></textarea>
                    </div>
                    <input type="hidden" :name="`sections[${index}][sort_order]`" :value="index">
                </div>
            </template>
        </section>

        <div class="mt-6">
            <button type="submit" class="btn btn-primary">Save Page</button>
        </div>
    </form>
</x-admin-layout>
