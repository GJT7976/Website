<?php

namespace App\Http\Controllers;

use App\Models\App;
use App\Models\Page;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = collect([
            ['loc' => route('home'), 'priority' => '1.0'],
            ['loc' => route('apps.index'), 'priority' => '0.9'],
            ['loc' => route('demos.index'), 'priority' => '0.8'],
            ['loc' => route('pricing'), 'priority' => '0.6'],
            ['loc' => route('about'), 'priority' => '0.6'],
            ['loc' => route('support'), 'priority' => '0.5'],
            ['loc' => route('contact'), 'priority' => '0.5'],
        ]);

        foreach (App::published()->get() as $app) {
            $urls->push([
                'loc' => route('apps.show', $app),
                'lastmod' => $app->updated_at->toAtomString(),
                'priority' => $app->is_featured ? '0.9' : '0.7',
            ]);

            if ($app->demo_enabled) {
                $urls->push(['loc' => route('demos.show', $app), 'priority' => '0.6']);
            }
        }

        foreach (Page::where('published', true)->get() as $page) {
            $urls->push([
                'loc' => match ($page->slug) {
                    'about' => null, // already added above
                    'install-android' => route('support.install.android'),
                    'install-windows' => route('support.install.windows'),
                    default => route($page->slug),
                },
                'lastmod' => $page->updated_at->toAtomString(),
                'priority' => '0.3',
            ]);
        }

        $urls = $urls->filter(fn ($u) => ! empty($u['loc']))->unique('loc');

        $xml = view('sitemap', ['urls' => $urls])->render();

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
