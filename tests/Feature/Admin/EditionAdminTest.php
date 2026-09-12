<?php

namespace Tests\Feature\Admin;

use App\Models\App;
use App\Models\AppEdition;
use App\Models\Platform;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditionAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_an_edition(): void
    {
        $admin = User::factory()->owner()->create();
        $app = App::factory()->create();

        $this->actingAs($admin)->post(route('admin.apps.editions.store', $app), [
            'name' => 'Android',
            'price' => '2.99',
            'active' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('app_editions', ['app_id' => $app->id, 'name' => 'Android', 'slug' => 'android', 'price_cents' => 299]);
    }

    public function test_admin_can_set_what_an_edition_includes(): void
    {
        $admin = User::factory()->owner()->create();
        $app = App::factory()->create();
        $android = Platform::create(['code' => 'android', 'name' => 'Android', 'sort_order' => 0]);
        $edition = AppEdition::create(['app_id' => $app->id, 'name' => 'Android', 'slug' => 'android', 'price_cents' => 299, 'currency' => 'CAD', 'active' => true, 'featured' => false, 'sort_order' => 0]);

        $this->actingAs($admin)->post(route('admin.apps.editions.entitlements', [$app, $edition]), [
            'grants' => ["{$android->id}:download"],
        ])->assertRedirect();

        $this->assertDatabaseHas('edition_entitlements', ['app_edition_id' => $edition->id, 'platform_id' => $android->id, 'access_type' => 'download']);
    }

    public function test_admin_can_delete_an_edition(): void
    {
        $admin = User::factory()->owner()->create();
        $app = App::factory()->create();
        $edition = AppEdition::create(['app_id' => $app->id, 'name' => 'Android', 'slug' => 'android', 'price_cents' => 299, 'currency' => 'CAD', 'active' => true, 'featured' => false, 'sort_order' => 0]);

        $this->actingAs($admin)->delete(route('admin.apps.editions.destroy', [$app, $edition]))->assertRedirect();

        $this->assertDatabaseMissing('app_editions', ['id' => $edition->id]);
    }

    public function test_guest_cannot_manage_editions(): void
    {
        $app = App::factory()->create();

        $this->post(route('admin.apps.editions.store', $app), ['name' => 'Android', 'price' => '2.99'])
            ->assertRedirect(route('admin.login'));
    }
}
