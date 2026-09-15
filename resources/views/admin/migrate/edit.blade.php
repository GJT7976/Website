<x-admin-layout title="Database Migrations">
    <div class="card max-w-2xl p-6">
        @if (empty($pending))
            <p class="text-body text-navy-soft">
                No pending migrations — the database schema is up to date
                with the code.
            </p>
        @else
            <p class="text-body text-navy-soft">
                The following migrations have not run against this
                database yet:
            </p>
            <pre class="mt-4 rounded-lg bg-mist p-4 text-small">{{ implode("\n", $pending) }}</pre>

            <p class="mt-4 text-body text-navy-soft">
                Running this only adds new tables/columns the code already
                expects — it never deletes data. Still, a schema change is
                harder to undo than a reseed, so double-check the pending
                list above before running it.
            </p>

            <div class="mt-6">
                <form method="post" action="{{ route('admin.migrate.store') }}" onsubmit="return confirm('Run the pending migrations listed above now?');">
                    @csrf
                    <button type="submit" class="btn btn-primary">Run migrations</button>
                </form>
            </div>
        @endif

        @if (session('migrateOutput'))
            <pre class="mt-6 rounded-lg bg-mist p-4 text-small">{{ session('migrateOutput') }}</pre>
        @endif
    </div>
</x-admin-layout>
