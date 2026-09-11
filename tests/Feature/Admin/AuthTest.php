<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_visiting_the_dashboard(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_log_in_with_correct_credentials(): void
    {
        $user = User::factory()->owner()->create(['password' => bcrypt('correct-password-123')]);

        $response = $this->post(route('admin.login.store'), [
            'email' => $user->email,
            'password' => 'correct-password-123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = User::factory()->create(['password' => bcrypt('correct-password-123')]);

        $response = $this->from(route('admin.login'))->post(route('admin.login.store'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_deactivated_admin_cannot_log_in(): void
    {
        $user = User::factory()->inactive()->create(['password' => bcrypt('correct-password-123')]);

        $response = $this->post(route('admin.login.store'), [
            'email' => $user->email,
            'password' => 'correct-password-123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_attempts_are_throttled(): void
    {
        $user = User::factory()->create(['password' => bcrypt('correct-password-123')]);

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('admin.login.store'), [
                'email' => $user->email,
                'password' => 'wrong-password',
            ]);
        }

        $response = $this->post(route('admin.login.store'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(429);
    }

    public function test_admin_can_log_out(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('admin.logout'))->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }
}
