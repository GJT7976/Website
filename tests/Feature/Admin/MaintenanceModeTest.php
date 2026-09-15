<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class MaintenanceModeTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        // Artisan::call('down') really does write storage/framework/down
        // and storage/framework/maintenance.php to disk (it's not faked by
        // RefreshDatabase, which only covers the database) — a test that
        // fails partway through, or between the store/destroy assertions
        // below, could otherwise leave the real dev environment stuck in
        // maintenance mode. UpCommand is a safe no-op when already up.
        Artisan::call('up');

        parent::tearDown();
    }

    public function test_owner_can_view_the_maintenance_mode_screen(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)->get(route('admin.maintenance.edit'))->assertOk();
    }

    public function test_content_editor_cannot_access_maintenance_mode(): void
    {
        $editor = User::factory()->contentEditor()->create();

        $this->actingAs($editor)->get(route('admin.maintenance.edit'))->assertForbidden();
        $this->actingAs($editor)->post(route('admin.maintenance.store'))->assertForbidden();
    }

    public function test_owner_can_turn_maintenance_mode_on(): void
    {
        $owner = User::factory()->owner()->create();

        $response = $this->actingAs($owner)->post(route('admin.maintenance.store'));

        $response->assertRedirect(route('admin.maintenance.edit'));
        $this->assertTrue(app()->isDownForMaintenance());
        $this->assertDatabaseHas('audit_logs', ['action' => 'maintenance.enabled', 'user_id' => $owner->id]);
    }

    public function test_a_public_page_shows_the_maintenance_view_while_on(): void
    {
        $owner = User::factory()->owner()->create();
        $this->actingAs($owner)->post(route('admin.maintenance.store'));

        $this->get('/')->assertStatus(503);
    }

    public function test_admin_backend_stays_reachable_while_maintenance_mode_is_on(): void
    {
        $owner = User::factory()->owner()->create();
        $this->actingAs($owner)->post(route('admin.maintenance.store'));

        // The whole point of gating this behind bootstrap/app.php's
        // preventRequestsDuringMaintenance(except: ['admin*']) — without
        // it, turning maintenance mode on would also lock the owner out
        // of the one screen that turns it back off again.
        $this->actingAs($owner)->get(route('admin.dashboard'))->assertOk();
    }

    public function test_owner_can_turn_maintenance_mode_off(): void
    {
        $owner = User::factory()->owner()->create();
        Artisan::call('down', ['--render' => 'errors.503']);

        $response = $this->actingAs($owner)->delete(route('admin.maintenance.destroy'));

        $response->assertRedirect(route('admin.maintenance.edit'));
        $this->assertFalse(app()->isDownForMaintenance());
        $this->assertDatabaseHas('audit_logs', ['action' => 'maintenance.disabled', 'user_id' => $owner->id]);
    }
}
