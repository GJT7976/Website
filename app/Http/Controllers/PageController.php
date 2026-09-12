<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\View\View;

class PageController extends Controller
{
    public function about(): View
    {
        return $this->renderBySlug('about');
    }

    public function privacy(): View
    {
        return $this->renderBySlug('privacy');
    }

    public function terms(): View
    {
        return $this->renderBySlug('terms');
    }

    public function refunds(): View
    {
        return $this->renderBySlug('refunds');
    }

    public function installAndroid(): View
    {
        return $this->renderBySlug('install-android');
    }

    public function installWindows(): View
    {
        return $this->renderBySlug('install-windows');
    }

    private function renderBySlug(string $slug): View
    {
        $page = Page::where('slug', $slug)->where('published', true)->firstOrFail();
        $page->load('sections');

        return view('pages.show', ['page' => $page]);
    }
}
