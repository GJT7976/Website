<?php

namespace App\Http\Controllers;

use App\Models\App;
use App\Models\Setting;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('home', [
            'hero' => [
                'title' => Setting::get('hero_title', 'Niagara Inde Apps'),
                'subtitle' => Setting::get('hero_subtitle', 'Smart Tools for Real People'),
                'description' => Setting::get('hero_description'),
            ],
            'featuredApps' => App::published()->featured()->with('media')->limit(6)->get(),
            // Homepage "Live Demo" imagery is also gated on is_featured: an
            // app's imagery should only reach the landing page once it has
            // deliberately been marked featured (not merely demo-enabled).
            'demoApps' => App::published()->demoEnabled()->featured()->with('media')->limit(6)->get(),
        ]);
    }
}
