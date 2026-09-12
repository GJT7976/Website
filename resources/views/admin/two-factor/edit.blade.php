<x-admin-layout title="Two-Factor Authentication">
    <div class="card max-w-lg space-y-5 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-h3">Two-factor authentication</h2>
                <p class="text-small mt-1">Adds a second step (an authenticator app code) when you sign in.</p>
            </div>
            @if ($user->hasTwoFactorEnabled())
                <span class="badge badge-brand">Enabled</span>
            @else
                <span class="badge">Disabled</span>
            @endif
        </div>

        @if ($user->hasTwoFactorEnabled())
            <form method="post" action="{{ route('admin.two-factor.disable') }}" class="space-y-3" onsubmit="return confirm('Disable two-factor authentication for your account?');">
                @csrf
                <x-admin.form-field name="password" label="Confirm your password to disable">
                    <input type="password" name="password" required class="w-full rounded-lg border border-border-strong px-3 py-2">
                </x-admin.form-field>
                <button type="submit" class="btn btn-secondary">Disable two-factor authentication</button>
            </form>

            <form method="post" action="{{ route('admin.two-factor.recovery-codes.regenerate') }}" class="space-y-3 border-t border-border pt-5" onsubmit="return confirm('Regenerate recovery codes? Your old codes will stop working.');">
                @csrf
                <x-admin.form-field name="password" label="Confirm your password to regenerate recovery codes">
                    <input type="password" name="password" required class="w-full rounded-lg border border-border-strong px-3 py-2">
                </x-admin.form-field>
                <button type="submit" class="btn btn-secondary">Regenerate recovery codes</button>
            </form>
        @else
            <form method="post" action="{{ route('admin.two-factor.enable') }}">
                @csrf
                <button type="submit" class="btn btn-primary">Set up two-factor authentication</button>
            </form>
        @endif
    </div>
</x-admin-layout>
