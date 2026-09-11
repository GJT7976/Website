<x-admin-layout title="Support Message">
    <a href="{{ route('admin.support.index') }}" class="text-small font-semibold text-niagara-600 hover:underline">&larr; Back to inbox</a>

    <div class="card mt-4 max-w-2xl p-6">
        <div class="flex items-start justify-between">
            <div>
                <h2 class="text-h3">{{ $supportRequest->subject ?: 'No subject' }}</h2>
                <p class="text-small mt-1">From {{ $supportRequest->name }} &lt;{{ $supportRequest->email }}&gt;</p>
                @if ($supportRequest->app)
                    <p class="text-small">Re: {{ $supportRequest->app->name }}</p>
                @endif
                <p class="text-small">{{ $supportRequest->created_at->format('M j, Y g:ia') }}</p>
            </div>
            <span class="badge">{{ $supportRequest->status }}</span>
        </div>

        <p class="text-body mt-5 whitespace-pre-line">{{ $supportRequest->message }}</p>

        <form method="post" action="{{ route('admin.support.update', $supportRequest) }}" class="mt-6 flex items-center gap-2">
            @csrf @method('PUT')
            <select name="status" class="rounded-lg border border-border-strong px-3 py-2 text-sm">
                @foreach (['new', 'read', 'resolved'] as $status)
                    <option value="{{ $status }}" @selected($supportRequest->status === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-secondary">Update Status</button>
            <a href="mailto:{{ $supportRequest->email }}" class="btn btn-primary">Reply by Email</a>
        </form>
    </div>
</x-admin-layout>
