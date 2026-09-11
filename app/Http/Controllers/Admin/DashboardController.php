<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\App;
use App\Models\Order;
use App\Models\SupportRequest;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Only counts that reflect real, current data — no invented metrics
     * for anything not actually implemented yet (spec §14). Sales figures
     * now reflect real orders (Phase 2); Accounting/Expenses/Audit/Backups
     * remain unimplemented and are not summarized here.
     */
    public function __invoke(): View
    {
        $paidToday = Order::where('payment_status', 'paid')->whereDate('created_at', Carbon::today())->sum('total_cents');
        $paidThisMonth = Order::where('payment_status', 'paid')->where('created_at', '>=', Carbon::now()->startOfMonth())->sum('total_cents');

        return view('admin.dashboard', [
            'publishedCount' => App::where('status', 'published')->count(),
            'draftCount' => App::where('status', 'draft')->count(),
            'archivedCount' => App::where('status', 'archived')->count(),
            'demoEnabledCount' => App::where('demo_enabled', true)->count(),
            'newSupportCount' => SupportRequest::where('status', 'new')->count(),
            'salesTodayCents' => $paidToday,
            'salesMonthCents' => $paidThisMonth,
            'pendingOrderCount' => Order::where('payment_status', 'pending')->count(),
            'recentSupportRequests' => SupportRequest::latest()->limit(5)->get(),
            'recentApps' => App::latest('updated_at')->limit(5)->get(),
            'recentOrders' => Order::latest()->limit(5)->get(),
        ]);
    }
}
