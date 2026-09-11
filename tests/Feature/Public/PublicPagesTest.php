<?php

namespace Tests\Feature\Public;

use App\Models\App;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_renders(): void
    {
        $this->get(route('home'))->assertOk()->assertSee('Niagara Inde Apps');
    }

    public function test_home_page_never_shows_a_non_featured_apps_media(): void
    {
        // Regression test for the requirement that an app's imagery must
        // not appear on the homepage/landing page unless it has been
        // deliberately marked featured.
        $app = App::factory()->create([
            'name' => 'Unfeatured Test App',
            'status' => 'published',
            'is_featured' => false,
            'demo_enabled' => true,
            'demo_url' => '/demo-builds/unfeatured-test-app/index.html',
        ]);

        $this->get(route('home'))->assertOk()->assertDontSee('Unfeatured Test App');
    }

    public function test_featured_published_app_appears_on_home_page(): void
    {
        $app = App::factory()->create([
            'name' => 'Featured Test App',
            'status' => 'published',
            'is_featured' => true,
        ]);

        $this->get(route('home'))->assertOk()->assertSee('Featured Test App');
    }

    public function test_apps_index_lists_published_apps_only(): void
    {
        App::factory()->create(['name' => 'Published App', 'status' => 'published']);
        App::factory()->create(['name' => 'Draft App', 'status' => 'draft']);

        $response = $this->get(route('apps.index'));

        $response->assertOk()->assertSee('Published App')->assertDontSee('Draft App');
    }

    public function test_app_detail_page_renders_for_published_app(): void
    {
        $app = App::factory()->create(['name' => 'Detail Test App', 'status' => 'published']);

        $this->get(route('apps.show', $app))->assertOk()->assertSee('Detail Test App');
    }

    public function test_draft_app_detail_page_is_not_publicly_reachable(): void
    {
        $app = App::factory()->create(['status' => 'draft']);

        $this->get(route('apps.show', $app))->assertNotFound();
    }

    public function test_demos_index_route_does_not_collide_with_the_public_demo_builds_directory(): void
    {
        // Regression test: /demos (the catalogue route) must not be
        // shadowed by the public/demo-builds/ static asset directory.
        $this->get(route('demos.index'))->assertOk();
    }

    public function test_demo_wrapper_renders_for_a_demo_enabled_app(): void
    {
        $app = App::factory()->create([
            'status' => 'published',
            'demo_enabled' => true,
            'demo_url' => '/demo-builds/some-app/index.html',
        ]);

        $this->get(route('demos.show', $app))
            ->assertOk()
            ->assertSee('Demo Mode')
            ->assertSee($app->demo_url, false);
    }

    public function test_demo_wrapper_404s_when_demo_is_not_enabled(): void
    {
        $app = App::factory()->create(['status' => 'published', 'demo_enabled' => false]);

        $this->get(route('demos.show', $app))->assertNotFound();
    }

    #[DataProvider('legalPageSlugs')]
    public function test_legal_and_info_pages_render(string $routeName, string $slug): void
    {
        Page::factory()->create(['slug' => $slug, 'title' => ucfirst($slug), 'published' => true]);

        $this->get(route($routeName))->assertOk();
    }

    public static function legalPageSlugs(): array
    {
        return [
            'about' => ['about', 'about'],
            'privacy' => ['privacy', 'privacy'],
            'terms' => ['terms', 'terms'],
            'refunds' => ['refunds', 'refunds'],
        ];
    }

    public function test_pricing_and_support_pages_render(): void
    {
        $this->get(route('pricing'))->assertOk();
        $this->get(route('support'))->assertOk();
    }
}
