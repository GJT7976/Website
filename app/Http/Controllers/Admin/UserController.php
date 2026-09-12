<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index', [
            'users' => User::orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.form', ['user' => new User]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'unique:users,email'],
            'role' => ['required', 'in:owner,content_editor'],
            'password' => ['required', 'confirmed', Password::min(12)],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'is_active' => true,
            'two_factor_enabled' => false,
            'password' => Hash::make($data['password']),
            'email_verified_at' => now(),
        ]);

        AuditLogger::record('admin.created', $user, null, ['email' => $user->email, 'role' => $user->role]);

        return redirect()->route('admin.users.index')->with('status', 'Administrator created.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', ['user' => $user]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', 'in:owner,content_editor'],
            'is_active' => ['sometimes', 'boolean'],
            'password' => ['nullable', 'confirmed', Password::min(12)],
        ]);

        if ($user->id === Auth::id() && $data['role'] !== 'owner' && $user->role === 'owner') {
            return back()->withErrors(['role' => 'You cannot remove your own owner role.']);
        }

        $roleChanged = $data['role'] !== $user->role;
        $before = $user->only(['name', 'email', 'role', 'is_active']);

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'is_active' => $request->boolean('is_active', true),
            ...(! empty($data['password']) ? ['password' => Hash::make($data['password'])] : []),
        ]);

        AuditLogger::record(
            $roleChanged ? 'admin.role_changed' : 'admin.updated',
            $user,
            $before,
            $user->only(['name', 'email', 'role', 'is_active'])
        );

        return redirect()->route('admin.users.index')->with('status', 'Administrator updated.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->withErrors(['user' => 'You cannot delete your own account.']);
        }

        $before = $user->only(['name', 'email', 'role']);
        $user->delete();

        AuditLogger::record('admin.deleted', null, $before, null, $before['email']);

        return back()->with('status', 'Administrator removed.');
    }

    /**
     * Owner override for the "lost phone, no recovery codes" case — clears
     * another admin's 2FA so they can log in and re-enroll. Enrollment
     * itself stays self-service (TwoFactorSettingsController); the owner
     * can only see/force-disable, never enable-on-behalf-of.
     */
    public function disableTwoFactor(User $user): RedirectResponse
    {
        $user->forceFill([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
            'two_factor_recovery_codes' => null,
        ])->save();

        AuditLogger::record('admin.2fa_disabled_by_owner', $user);

        return back()->with('status', "Two-factor authentication disabled for {$user->name}.");
    }
}
