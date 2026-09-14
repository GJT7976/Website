<x-app-layout :title="'Manage Your License — Niagara Inde Apps'">
    <section class="mx-auto max-w-2xl px-4 py-16 sm:px-6 lg:px-8">
        <p class="text-label text-niagara-600">License Management</p>
        <h1 class="text-h1 mt-1">{{ $license->app->name }}</h1>
        <p class="text-body mt-2">
            {{ $license->activeDevices()->count() }} of {{ $license->maximum_devices }} devices activated.
        </p>

        @if (session('status'))
            <x-alert type="success" class="mt-6">{{ session('status') }}</x-alert>
        @endif

        @if ($errors->any())
            <x-alert type="error" class="mt-6">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </x-alert>
        @endif

        <div class="mt-6 space-y-3">
            @forelse ($devices as $row)
                @php $device = $row['device']; @endphp
                <div class="flex items-center justify-between gap-4 rounded-lg border border-border p-4">
                    <div>
                        <p class="font-semibold">{{ $device->label ?? ucfirst($device->platform) }}</p>
                        <p class="text-small">
                            {{ $device->status === 'active' ? 'Activated' : 'Deactivated' }}
                            {{ ($device->status === 'active' ? $device->activated_at : $device->deactivated_at)?->format('F j, Y') }}
                        </p>
                    </div>
                    @if ($row['deactivate_url'])
                        <form method="post" action="{{ $row['deactivate_url'] }}" onsubmit="return confirm('Deactivate this device? You can activate a new one in its place.');">
                            @csrf
                            <button type="submit" class="btn btn-secondary whitespace-nowrap">Deactivate</button>
                        </form>
                    @endif
                </div>
            @empty
                <p class="text-body">No devices have been activated on this license yet.</p>
            @endforelse
        </div>

        <p class="text-small mt-6 text-navy-soft">Deactivating a device frees up a slot to activate a new one — enter your license key in the app on the new device afterward.</p>
    </section>
</x-app-layout>
