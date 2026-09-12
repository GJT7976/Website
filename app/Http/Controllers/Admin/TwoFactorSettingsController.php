<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use App\Services\TwoFactorAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * Self-service 2FA enrollment — any authenticated admin manages their own
 * account here (routes live in the `auth` group, not `role:owner`; the
 * spec only recommends 2FA for Owner/Admin, it doesn't mandate it). Owner
 * override for someone else's account (lost phone, no recovery codes) is
 * UserController::disableTwoFactor(), gated role:owner.
 */
class TwoFactorSettingsController extends Controller
{
    public function edit(): View
    {
        return view('admin.two-factor.edit', ['user' => auth()->user()]);
    }

    public function enable(TwoFactorAuthService $twoFactor): View
    {
        $secret = $twoFactor->generateSecretKey();

        // Pending until confirm() — two_factor_enabled stays false, so an
        // abandoned setup never affects login. forceFill, not update():
        // two_factor_secret is deliberately excluded from #[Fillable].
        auth()->user()->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => null,
        ])->save();

        return view('admin.two-factor.setup', [
            'qrSvg' => $twoFactor->qrCodeSvg(auth()->user(), $secret),
            'secret' => $secret,
        ]);
    }

    public function confirm(Request $request, TwoFactorAuthService $twoFactor): RedirectResponse
    {
        $data = $request->validate(['code' => ['required', 'string']]);

        abort_unless($twoFactor->verifyCode(auth()->user(), str_replace(' ', '', $data['code'])), 422, 'That code was not correct.');

        $codes = $twoFactor->generateRecoveryCodes();

        auth()->user()->forceFill([
            'two_factor_enabled' => true,
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => array_map(fn ($code) => Hash::make($code), $codes),
        ])->save();

        AuditLogger::record('admin.2fa_enabled', auth()->user());

        return redirect()->route('admin.two-factor.recovery-codes')->with('recoveryCodes', $codes);
    }

    public function disable(Request $request): RedirectResponse
    {
        $request->validate(['password' => ['required', 'current_password:web']]);

        auth()->user()->forceFill([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
            'two_factor_recovery_codes' => null,
        ])->save();

        AuditLogger::record('admin.2fa_disabled', auth()->user());

        return redirect()->route('admin.two-factor.edit')->with('status', 'Two-factor authentication disabled.');
    }

    public function regenerateRecoveryCodes(Request $request, TwoFactorAuthService $twoFactor): RedirectResponse
    {
        $request->validate(['password' => ['required', 'current_password:web']]);

        $codes = $twoFactor->generateRecoveryCodes();

        auth()->user()->forceFill([
            'two_factor_recovery_codes' => array_map(fn ($code) => Hash::make($code), $codes),
        ])->save();

        return redirect()->route('admin.two-factor.recovery-codes')->with('recoveryCodes', $codes);
    }

    public function recoveryCodes(): View|RedirectResponse
    {
        if (! session()->has('recoveryCodes')) {
            return redirect()->route('admin.two-factor.edit');
        }

        return view('admin.two-factor.recovery-codes', ['codes' => session('recoveryCodes')]);
    }
}
