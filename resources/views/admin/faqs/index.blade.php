<x-admin-layout title="FAQs">
    <div class="flex justify-end">
        <a href="{{ route('admin.faqs.create') }}" class="btn btn-primary">+ Add FAQ</a>
    </div>

    <x-admin.data-table class="mt-4">
        <table>
            <thead>
                <tr>
                    <th>Question</th>
                    <th>App</th>
                    <th>Published</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($faqs as $faq)
                    <tr>
                        <td class="font-semibold">{{ $faq->question }}</td>
                        <td class="text-small">{{ $faq->app?->name ?? 'General' }}</td>
                        <td><span class="badge {{ $faq->published ? 'badge-brand' : '' }}">{{ $faq->published ? 'Yes' : 'No' }}</span></td>
                        <td class="text-right">
                            <div class="flex justify-end gap-1.5">
                                <a href="{{ route('admin.faqs.edit', $faq) }}" class="btn btn-ghost !px-2 !py-1 text-xs">Edit</a>
                                <form method="post" action="{{ route('admin.faqs.destroy', $faq) }}" onsubmit="return confirm('Delete this FAQ?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-ghost !px-2 !py-1 text-xs text-red-600">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center py-8 text-small">No FAQs yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-admin.data-table>

    <div class="mt-6">{{ $faqs->links() }}</div>
</x-admin-layout>
