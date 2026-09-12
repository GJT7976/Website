<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TwoFactorAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * The second step of login for a user with hasTwoFactorEnabled() — reached
 * only via the pending-session marker AuthController@store sets after a
 * correct password. No real Auth session exists yet at this point.
 */
class TwoFactorChallengeController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if (! $this->hasPendingChallenge($request)) {
            return redirect()->route('admin.login');
        }

        return view('admin.auth.two-factor-challenge');
    }

    public function store(Request $request, TwoFactorAuthService $twoFactor): RedirectResponse
    {
        if (! $this->hasPendingChallenge($request)) {
            return redirect()->route('admin.login');
        }

        $user = User::findOrFail($request->session()->get('2fa.user_id'));

        $data = $request->validate(['code' => ['required', 'string']]);

        $code = str_replace([' ', '-'], '', $data['code']);
        $ok = $twoFactor->verifyCode($user, $code) || $twoFactor->verifyAndConsumeRecoveryCode($user, $data['code']);

        if (! $ok) {
            throw ValidationException::withMessages([
                'code' => 'That code was not correct.',
            ]);
        }

        $remember = $request->session()->pull('2fa.remember', false);
        $request->session()->forget(['2fa.user_id', '2fa.expires_at']);

        Auth::login($user, $remember);
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    private function hasPendingChallenge(Request $request): bool
    {
        $expiresAt = $request->session()->get('2fa.expires_at');

        return $request->session()->has('2fa.user_id')
            && $expiresAt !== null
            && now()->lt($expiresAt);
    }
}
