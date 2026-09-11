<?php

namespace App\Http\Controllers;

use App\Models\App;
use Illuminate\View\View;

class DemoController extends Controller
{
    public function index(): View
    {
        return view('demos.index', [
            'apps' => App::published()->demoEnabled()->with('media')->orderBy('name')->get(),
        ]);
    }

    public function show(App $app): View
    {
        abort_unless($app->status === 'published' && $app->demo_enabled, 404);

        return view('demos.show', [
            'app' => $app,
        ]);
    }
}
