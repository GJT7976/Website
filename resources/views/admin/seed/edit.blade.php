<x-admin-layout title="Seed Data">
    <div class="card max-w-2xl p-6">
        <p class="text-body text-navy-soft">
            Re-runs the database seeders in
            <code>database/seeders/</code> — app categories, platforms, apps
            (name, description, pricing, editions, features, media), pages,
            settings, FAQs, and tax rules.
        </p>

        <p class="mt-4 text-body text-navy-soft">
            Safe to run any time: every seeder uses <code>updateOrCreate()</code>,
            so this only creates or updates the reference/catalogue rows the
            seeders define — it never touches orders, users, licenses, or
            customer entitlements, and it will not duplicate anything.
        </p>

        <p class="mt-4 text-body text-navy-soft">
            It will <strong>not</strong> pick up an app added by hand through
            <a href="{{ route('admin.apps.index') }}" class="text-brand underline">Apps</a>
            rather than through <code>AppSeeder</code> itself.
        </p>

        <div class="mt-6">
            <form method="post" action="{{ route('admin.seed.store') }}" onsubmit="return confirm('Re-run the database seeders now?');">
                @csrf
                <button type="submit" class="btn btn-primary">Run seeders</button>
            </form>
        </div>
    </div>
</x-admin-layout>
