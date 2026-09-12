<x-admin-layout title="Recovery Codes">
    <div class="card max-w-lg space-y-5 p-6">
        <div>
            <h2 class="text-h3">Save your recovery codes</h2>
            <p class="text-small mt-1">Each code can be used once, in place of an authenticator app code, if you lose access to your device. <strong>These will not be shown again — save them somewhere safe now.</strong></p>
        </div>

        <div class="grid grid-cols-2 gap-2 rounded-lg bg-mist p-4 font-mono text-sm">
            @foreach ($codes as $code)
                <span>{{ $code }}</span>
            @endforeach
        </div>

        <a href="{{ route('admin.two-factor.edit') }}" class="btn btn-primary">I've saved these codes</a>
    </div>
</x-admin-layout>
