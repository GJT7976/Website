<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(): View
    {
        return view('admin.auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Those credentials don\'t match an administrator account.',
            ]);
        }

        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => 'This administrator account has been deactivated.',
            ]);
        }

        if ($user->hasTwoFactorEnabled()) {
            // Password was correct, but don't persist a real authenticated
            // session yet — only a short-lived pending marker, resolved by
            // TwoFactorChallengeController once a valid code is entered.
            Auth::logout();

            $request->session()->put('2fa.user_id', $user->id);
            $request->session()->put('2fa.remember', $request->boolean('remember'));
            $request->session()->put('2fa.expires_at', now()->addMinutes(5));

            return redirect()->route('admin.two-factor.challenge');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
