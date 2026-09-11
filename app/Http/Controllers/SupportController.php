<?php

namespace App\Http\Controllers;

use App\Models\App;
use App\Models\Faq;
use Illuminate\View\View;

class SupportController extends Controller
{
    public function __invoke(): View
    {
        return view('support', [
            'faqs' => Faq::whereNull('app_id')->where('published', true)->orderBy('sort_order')->get(),
            'apps' => App::published()->orderBy('name')->get(),
        ]);
    }
}
