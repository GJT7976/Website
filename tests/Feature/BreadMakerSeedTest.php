<?php

namespace Tests\Feature;

use App\Models\App;
use Database\Seeders\AppCategorySeeder;
use Database\Seeders\AppSeeder;
use Database\Seeders\PlatformSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Exercises the real production seed data end-to-end, since Bread Maker is
 * the first live app shipped with this site.
 */
class BreadMakerSeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_bread_maker_seeds_correctly_and_is_reachable(): void
    {
        $this->seed(AppCategorySeeder::class);
        $this->seed(PlatformSeeder::class);
        $this->seed(AppSeeder::class);

        $app = App::where('slug', 'bread-maker')->firstOrFail();

        $this->assertTrue($app->status === 'published');
        $this->assertFalse($app->is_featured, 'Bread Maker must not be featured, so its imagery stays off the homepage.');
        $this->assertTrue($app->demo_enabled);

        // The demo build file it points to must actually exist on disk, and
        // must not live under public/demos/ (collides with the /demos route).
        $this->assertStringStartsNotWith('/demos/', $app->demo_url);
        $this->assertFileExists(public_path(ltrim($app->demo_url, '/')));

        $this->get(route('apps.show', $app))->assertOk()->assertSee('Bread Maker');
        $this->get(route('demos.show', $app))->assertOk();
    }
}
