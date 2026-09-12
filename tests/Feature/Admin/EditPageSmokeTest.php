<?php
namespace Tests\Feature\Admin;

use App\Models\App;
use App\Models\AppEdition;
use App\Models\EditionEntitlement;
use App\Models\Platform;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditPageSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_app_edit_page_renders_edition_release_and_entitlement_partials(): void
    {
        $admin = User::factory()->owner()->create();
        $app = App::factory()->create();
        $android = Platform::create(['code' => 'android', 'name' => 'Android', 'sort_order' => 0]);
        $edition = AppEdition::create(['app_id' => $app->id, 'name' => 'Android', 'slug' => 'android', 'price_cents' => 299, 'currency' => 'CAD', 'active' => true, 'featured' => false, 'sort_order' => 0]);
        EditionEntitlement::create(['app_edition_id' => $edition->id, 'platform_id' => $android->id, 'access_type' => 'download']);

        $response = $this->actingAs($admin)->get(route('admin.apps.edit', $app));

        $response->assertOk();
        $response->assertSee('Editions');
        $response->assertSee('Releases');
        $response->assertSee('Customer Access');
        $response->assertSee('Platform Delivery, License &amp; Updates', false);
    }
}
