<?php

namespace Tests\Feature\Admin;

use App\Models\App;
use App\Models\AppRelease;
use App\Models\Platform;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReleaseAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_an_apk_release(): void
    {
        Storage::fake('local');
        $admin = User::factory()->owner()->create();
        $app = App::factory()->create();
        $android = Platform::create(['code' => 'android', 'name' => 'Android', 'sort_order' => 0]);

        $this->actingAs($admin)->post(route('admin.apps.releases.store', $app), [
            'platform_id' => $android->id,
            'version' => '1.0.0',
            'released_at' => now()->toDateString(),
            'file' => UploadedFile::fake()->create('app.apk', 500),
        ])->assertRedirect();

        $this->assertDatabaseHas('app_releases', [
            'app_id' => $app->id,
            'platform_id' => $android->id,
            'version' => '1.0.0',
            'customer_downloadable' => true,
        ]);
    }

    public function test_uploading_an_aab_is_forced_internal_only_even_if_not_requested(): void
    {
        Storage::fake('local');
        $admin = User::factory()->owner()->create();
        $app = App::factory()->create();
        $android = Platform::create(['code' => 'android', 'name' => 'Android', 'sort_order' => 0]);

        $this->actingAs($admin)->post(route('admin.apps.releases.store', $app), [
            'platform_id' => $android->id,
            'version' => '1.0.0',
            'released_at' => now()->toDateString(),
            'file' => UploadedFile::fake()->create('app.aab', 500),
            // Deliberately NOT checking internal_only — the .aab extension
            // itself must force customer_downloadable=false regardless.
        ])->assertRedirect();

        $this->assertDatabaseHas('app_releases', [
            'app_id' => $app->id,
            'customer_downloadable' => false,
        ]);
    }

    public function test_an_unrecognized_extension_is_rejected_outright(): void
    {
        Storage::fake('local');
        $admin = User::factory()->owner()->create();
        $app = App::factory()->create();
        $android = Platform::create(['code' => 'android', 'name' => 'Android', 'sort_order' => 0]);

        $this->actingAs($admin)->post(route('admin.apps.releases.store', $app), [
            'platform_id' => $android->id,
            'version' => '1.0.0',
            'released_at' => now()->toDateString(),
            'file' => UploadedFile::fake()->create('secrets.env', 10),
        ])->assertSessionHasErrors('file');

        $this->assertDatabaseCount('app_releases', 0);
    }

    public function test_admin_can_make_a_release_current(): void
    {
        Storage::fake('local');
        $admin = User::factory()->owner()->create();
        $app = App::factory()->create();
        $android = Platform::create(['code' => 'android', 'name' => 'Android', 'sort_order' => 0]);

        $release = AppRelease::create([
            'app_id' => $app->id, 'platform_id' => $android->id, 'version' => '1.0.0',
            'released_at' => now()->toDateString(), 'is_current' => false, 'disk' => 'local',
            'path' => 'releases/x/android/test.apk', 'original_filename' => 'app.apk',
            'file_size' => 100, 'customer_downloadable' => true,
        ]);

        $this->actingAs($admin)->post(route('admin.apps.releases.activate', [$app, $release]))->assertRedirect();

        $this->assertTrue($release->refresh()->is_current);
    }
}
