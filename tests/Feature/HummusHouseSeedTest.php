<?php

namespace Tests\Feature;

use App\Models\App;
use Database\Seeders\AppCategorySeeder;
use Database\Seeders\AppSeeder;
use Database\Seeders\PlatformSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Exercises the real Hummus House seed data end-to-end, same pattern as
 * BreadMakerSeedTest.
 */
class HummusHouseSeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_hummus_house_seeds_correctly_and_is_reachable(): void
    {
        $this->seed(AppCategorySeeder::class);
        $this->seed(PlatformSeeder::class);
        $this->seed(AppSeeder::class);

        $app = App::where('slug', 'hummus-house')->firstOrFail();

        $this->assertTrue($app->status === 'published');
        $this->assertFalse($app->is_free);
        $this->assertSame(299, $app->price_cents);
        $this->assertSame('USD', $app->currency);
        $this->assertTrue($app->direct_purchase_enabled);
        $this->assertTrue($app->demo_enabled);

        // Flutter web builds default base href to "/" — must be patched
        // for a subdirectory deploy, and must not live under public/demos/
        // (collides with the /demos route, same as Bread Maker).
        $this->assertStringStartsNotWith('/demos/', $app->demo_url);
        $indexPath = public_path(ltrim($app->demo_url, '/'));
        $this->assertFileExists($indexPath);
        $this->assertStringContainsString(
            'base href="/demo-builds/hummus-house/"',
            file_get_contents($indexPath),
        );

        $this->get(route('apps.show', $app))->assertOk()->assertSee('Hummus House');
        $this->get(route('demos.show', $app))->assertOk();
        $this->get(route('checkout.create', $app))->assertOk()->assertSee('$2.99');
    }
}
