<x-admin-layout title="Maintenance Mode">
    <div class="card max-w-2xl p-6">
        <div class="flex items-center gap-3">
            <span class="badge {{ $active ? 'bg-red-50 text-red-700 border-red-200' : 'badge-brand' }}">
                {{ $active ? 'ON — site is down for visitors' : 'OFF — site is live' }}
            </span>
        </div>

        <p class="mt-4 text-body text-navy-soft">
            @if ($active)
                The public site is currently showing the maintenance page to
                every visitor. The admin backend stays reachable so you can
                keep working and turn this off when you're ready.
            @else
                The public site is live. Turning this on immediately shows
                every visitor a maintenance page instead of the site — use it
                while you're making changes you don't want customers to see
                mid-way through.
            @endif
        </p>

        <div class="mt-6">
            @if ($active)
                <form method="post" action="{{ route('admin.maintenance.destroy') }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-primary">Turn maintenance mode off</button>
                </form>
            @else
                <form method="post" action="{{ route('admin.maintenance.store') }}" onsubmit="return confirm('Put the site into maintenance mode? Every visitor will see the maintenance page until you turn it back off.');">
                    @csrf
                    <button type="submit" class="btn btn-secondary">Turn maintenance mode on</button>
                </form>
            @endif
        </div>
    </div>
</x-admin-layout>
