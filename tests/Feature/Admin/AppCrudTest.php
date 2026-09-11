<?php

namespace Tests\Feature\Admin;

use App\Models\App;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_an_app(): void
    {
        $admin = User::factory()->owner()->create();

        $response = $this->actingAs($admin)->post(route('admin.apps.store'), [
            'name' => 'Test Bakery App',
            'status' => 'draft',
            'is_free' => '1',
            'currency' => 'CAD',
        ]);

        $this->assertDatabaseHas('apps', ['name' => 'Test Bakery App', 'slug' => 'test-bakery-app']);
        $response->assertRedirect();
    }

    public function test_creating_an_app_requires_a_name(): void
    {
        $admin = User::factory()->owner()->create();

        $this->actingAs($admin)
            ->post(route('admin.apps.store'), ['status' => 'draft'])
            ->assertSessionHasErrors('name');
    }

    public function test_admin_can_update_an_app(): void
    {
        $admin = User::factory()->owner()->create();
        $app = App::factory()->create(['name' => 'Original Name', 'status' => 'draft']);

        $this->actingAs($admin)->put(route('admin.apps.update', $app), [
            'name' => 'Updated Name',
            'status' => 'draft',
            'is_free' => '1',
            'currency' => 'CAD',
        ]);

        $this->assertDatabaseHas('apps', ['id' => $app->id, 'name' => 'Updated Name']);
    }

    public function test_unchecking_is_free_and_setting_a_price_actually_sets_the_price(): void
    {
        // Regression test: an app created as free, then edited to add a
        // real price without "is_free" also being unchecked, must not
        // silently keep price_cents null (this happened to a real app —
        // the form now disables price fields while "is_free" is checked,
        // but the server-side logic must independently be correct too).
        $admin = User::factory()->owner()->create();
        $app = App::factory()->create(['is_free' => true, 'price_cents' => null, 'status' => 'published']);

        $this->actingAs($admin)->put(route('admin.apps.update', $app), [
            'name' => $app->name,
            'status' => 'published',
            'price' => '2.99',
            'currency' => 'USD',
            // no 'is_free' key at all — an unchecked checkbox is omitted
        ]);

        $app->refresh();
        $this->assertFalse($app->is_free);
        $this->assertSame(299, $app->price_cents);
        $this->assertSame('USD', $app->currency);
    }

    public function test_admin_can_publish_and_unpublish_an_app(): void
    {
        $admin = User::factory()->owner()->create();
        $app = App::factory()->create(['status' => 'draft']);

        $this->actingAs($admin)->post(route('admin.apps.publish', $app));
        $this->assertSame('published', $app->fresh()->status);

        $this->actingAs($admin)->post(route('admin.apps.unpublish', $app));
        $this->assertSame('draft', $app->fresh()->status);
    }

    public function test_admin_can_archive_and_soft_delete_an_app(): void
    {
        $admin = User::factory()->owner()->create();
        $app = App::factory()->create(['status' => 'published']);

        $this->actingAs($admin)->delete(route('admin.apps.destroy', $app));

        $this->assertSoftDeleted('apps', ['id' => $app->id]);
    }

    public function test_admin_can_toggle_featured_status(): void
    {
        $admin = User::factory()->owner()->create();
        $app = App::factory()->create(['is_featured' => false]);

        $this->actingAs($admin)->post(route('admin.apps.feature', $app));
        $this->assertTrue($app->fresh()->is_featured);
    }

    public function test_admin_can_duplicate_an_app(): void
    {
        $admin = User::factory()->owner()->create();
        $app = App::factory()->create(['name' => 'Original App', 'status' => 'published']);

        $this->actingAs($admin)->post(route('admin.apps.duplicate', $app));

        $this->assertDatabaseHas('apps', ['name' => 'Original App (Copy)', 'status' => 'draft']);
    }

    public function test_guest_cannot_manage_apps(): void
    {
        $this->get(route('admin.apps.index'))->assertRedirect(route('admin.login'));
    }
}
