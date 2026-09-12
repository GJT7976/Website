<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TwoFactorAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_without_2fa_logs_in_normally(): void
    {
        $user = User::factory()->owner()->create(['password' => bcrypt('password123456')]);

        $response = $this->post(route('admin.login.store'), [
            'email' => $user->email,
            'password' => 'password123456',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_with_2fa_is_redirected_to_challenge_and_stays_a_guest(): void
    {
        $user = User::factory()->owner()->withTwoFactor()->create(['password' => bcrypt('password123456')]);

        $response = $this->post(route('admin.login.store'), [
            'email' => $user->email,
            'password' => 'password123456',
        ]);

        $response->assertRedirect(route('admin.two-factor.challenge'));
        $this->assertGuest();
    }

    public function test_correct_totp_code_completes_login(): void
    {
        $user = User::factory()->owner()->withTwoFactor()->create(['password' => bcrypt('password123456')]);

        $this->post(route('admin.login.store'), ['email' => $user->email, 'password' => 'password123456']);

        $code = (new Google2FA)->getCurrentOtp($user->two_factor_secret);

        $response = $this->post(route('admin.two-factor.challenge.store'), ['code' => $code]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_incorrect_totp_code_is_rejected_and_user_stays_guest(): void
    {
        $user = User::factory()->owner()->withTwoFactor()->create(['password' => bcrypt('password123456')]);

        $this->post(route('admin.login.store'), ['email' => $user->email, 'password' => 'password123456']);

        $response = $this->post(route('admin.two-factor.challenge.store'), ['code' => '000000']);

        $response->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_recovery_code_can_be_used_once(): void
    {
        $user = User::factory()->owner()->withTwoFactor()->create(['password' => bcrypt('password123456')]);
        $code = 'ABCD-EFGH';
        $user->forceFill(['two_factor_recovery_codes' => [bcrypt($code)]])->save();

        $this->post(route('admin.login.store'), ['email' => $user->email, 'password' => 'password123456']);
        $response = $this->post(route('admin.two-factor.challenge.store'), ['code' => $code]);
        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);

        // Reuse should fail now that it's consumed.
        auth()->logout();
        $this->post(route('admin.login.store'), ['email' => $user->email, 'password' => 'password123456']);
        $response = $this->post(route('admin.two-factor.challenge.store'), ['code' => $code]);
        $response->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_challenge_route_unreachable_without_a_pending_session(): void
    {
        $response = $this->get(route('admin.two-factor.challenge'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_enable_2fa_for_own_account(): void
    {
        $user = User::factory()->owner()->create();

        $setup = $this->actingAs($user)->post(route('admin.two-factor.enable'));
        $setup->assertOk();

        $secret = $user->fresh()->two_factor_secret;
        $code = (new Google2FA)->getCurrentOtp($secret);

        $response = $this->actingAs($user)->post(route('admin.two-factor.confirm'), ['code' => $code]);

        $response->assertRedirect(route('admin.two-factor.recovery-codes'));
        $this->assertDatabaseHas('users', ['id' => $user->id, 'two_factor_enabled' => true]);
        $this->assertTrue($user->fresh()->hasTwoFactorEnabled());
    }

    public function test_disabling_2fa_requires_current_password(): void
    {
        $user = User::factory()->owner()->withTwoFactor()->create(['password' => bcrypt('password123456')]);

        $response = $this->actingAs($user)->post(route('admin.two-factor.disable'), ['password' => 'wrong-password']);

        $response->assertSessionHasErrors('password');
        $this->assertTrue($user->fresh()->hasTwoFactorEnabled());
    }

    public function test_owner_can_force_disable_another_admins_2fa(): void
    {
        $owner = User::factory()->owner()->create();
        $editor = User::factory()->contentEditor()->withTwoFactor()->create();

        $response = $this->actingAs($owner)->post(route('admin.users.two-factor.disable', $editor));

        $response->assertRedirect();
        $this->assertFalse($editor->fresh()->hasTwoFactorEnabled());
    }

    public function test_content_editor_cannot_force_disable_another_admins_2fa(): void
    {
        $editor = User::factory()->contentEditor()->create();
        $owner = User::factory()->owner()->withTwoFactor()->create();

        $response = $this->actingAs($editor)->post(route('admin.users.two-factor.disable', $owner));

        $response->assertForbidden();
    }
}
