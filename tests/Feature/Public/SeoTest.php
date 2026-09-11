<?php

namespace Tests\Feature\Public;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_renders_valid_xml(): void
    {
        $response = $this->get(route('sitemap'));

        $response->assertOk();
        $this->assertStringContainsString('application/xml', $response->headers->get('Content-Type'));
        $response->assertSee('<urlset', false);
        $response->assertSee(route('home'), false);
    }

    public function test_robots_txt_exists_as_a_static_public_file(): void
    {
        // robots.txt is served directly by the webserver from public/, not
        // through a Laravel route, so the HTTP test client can't fetch it
        // (same reasoning as the public/demo-builds static assets) — assert
        // its presence on disk instead.
        $this->assertFileExists(public_path('robots.txt'));
    }
}
