<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\App;
use App\Models\SupportRequest;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Only counts that reflect real, current data — no invented financial
     * or usage metrics before Orders/Sales/Accounting exist in Phase 2
     * (spec §14).
     */
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'publishedCount' => App::where('status', 'published')->count(),
            'draftCount' => App::where('status', 'draft')->count(),
            'archivedCount' => App::where('status', 'archived')->count(),
            'demoEnabledCount' => App::where('demo_enabled', true)->count(),
            'newSupportCount' => SupportRequest::where('status', 'new')->count(),
            'recentSupportRequests' => SupportRequest::latest()->limit(5)->get(),
            'recentApps' => App::latest('updated_at')->limit(5)->get(),
        ]);
    }
}
