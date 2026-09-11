<?php

namespace App\Http\Controllers;

use App\Models\App;
use App\Models\AppCategory;
use App\Models\Platform;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppController extends Controller
{
    public function index(Request $request): View
    {
        $query = App::published()->with(['category', 'media'])->orderBy('name');

        if ($category = $request->string('category')->toString()) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $category));
        }

        if ($platform = $request->string('platform')->toString()) {
            $query->whereHas('platforms', fn ($q) => $q->where('code', $platform));
        }

        return view('apps.index', [
            'apps' => $query->paginate(12)->withQueryString(),
            'categories' => AppCategory::orderBy('sort_order')->get(),
            'platforms' => Platform::orderBy('sort_order')->get(),
            'activeCategory' => $category,
            'activePlatform' => $platform,
        ]);
    }

    public function show(App $app): View
    {
        abort_unless($app->status === 'published', 404);

        $app->load(['category', 'media', 'features', 'platforms', 'faqs']);

        return view('apps.show', [
            'app' => $app,
        ]);
    }
}
