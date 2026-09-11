<?php

namespace App\Http\Controllers;

use App\Models\App;
use Illuminate\View\View;

class PricingController extends Controller
{
    /**
     * Pricing is set per-app rather than through fixed plans, so this page
     * is an overview that links out to each app's own pricing. A dedicated
     * checkout/pricing engine (tax-aware) arrives in Phase 2.
     */
    public function __invoke(): View
    {
        return view('pricing', [
            'apps' => App::published()->with('category')->orderBy('name')->get(),
        ]);
    }
}
