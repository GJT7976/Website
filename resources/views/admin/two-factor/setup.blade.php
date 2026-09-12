<x-admin-layout title="Set Up Two-Factor Authentication">
    <div class="card max-w-lg space-y-5 p-6">
        <div>
            <h2 class="text-h3">Scan this QR code</h2>
            <p class="text-small mt-1">Use an authenticator app (Google Authenticator, Authy, 1Password, etc.) to scan the code below, then enter the 6-digit code it shows.</p>
        </div>

        <div class="flex justify-center rounded-lg bg-white p-4">
            {!! $qrSvg !!}
        </div>

        <div>
            <p class="text-label">Can't scan? Enter this key manually:</p>
            <code class="text-small mt-1 block break-all rounded-lg bg-mist p-2">{{ $secret }}</code>
        </div>

        @if ($errors->any())
            <x-alert type="error">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </x-alert>
        @endif

        <form method="post" action="{{ route('admin.two-factor.confirm') }}" class="space-y-3">
            @csrf
            <x-admin.form-field name="code" label="6-digit code">
                <input name="code" type="text" inputmode="numeric" autocomplete="one-time-code" required autofocus
                       class="w-full rounded-lg border border-border-strong px-3 py-2 text-center text-lg tracking-widest">
            </x-admin.form-field>
            <button type="submit" class="btn btn-primary">Confirm and enable</button>
            <a href="{{ route('admin.two-factor.edit') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</x-admin-layout>
