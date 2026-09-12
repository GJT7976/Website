<?php

namespace App\Http\Controllers;

use App\Mail\MyDownloadsLink;
use App\Models\CustomerEntitlement;
use App\Models\Order;
use App\Services\EntitlementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

/**
 * "My Downloads" (§19) — the site has no customer accounts (guest
 * checkout only, ARCHITECTURE.md), so access is a permanent signed link
 * mailed to the order email rather than a login. The link itself never
 * expires (customers may legitimately need to reinstall software, §34);
 * only the individual download buttons it renders are short-lived.
 */
class MyDownloadsController extends Controller
{
    public function __construct(private EntitlementService $entitlements) {}

    public function create(): View
    {
        return view('my-downloads.request');
    }

    public function sendLink(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email']]);
        $email = mb_strtolower(trim($data['email']));

        $hasPurchases = Order::query()->where('customer_email', $email)->where('payment_status', 'paid')->exists()
            || CustomerEntitlement::query()->forEmail($email)->exists();

        if ($hasPurchases) {
            $url = URL::signedRoute('my-downloads.show', ['email' => $email]);
            Mail::to($email)->send(new MyDownloadsLink($email, $url));
        }

        // Deliberately the same message either way — don't reveal whether
        // an email has ever purchased anything.
        return back()->with('status', "If {$email} has any purchases with us, a download link is on its way.");
    }

    public function show(string $email): View
    {
        $email = mb_strtolower(trim($email));

        $entitlements = $this->entitlements->resolveForEmail($email)
            ->groupBy('app_id');

        $downloads = $entitlements->map(function ($group) {
            $app = $group->first()->app;

            $byEdition = $group->groupBy('app_edition_id')->map(function ($items) {
                return [
                    'edition' => $items->first()->edition,
                    'entitlements' => $items->map(function (CustomerEntitlement $entitlement) {
                        return [
                            'entitlement' => $entitlement,
                            'release' => $entitlement->currentRelease(),
                            'download_url' => $entitlement->access_type === 'download' && $entitlement->currentRelease()
                                ? URL::temporarySignedRoute('downloads.show', now()->addMinutes(10), ['entitlement' => $entitlement->id])
                                : null,
                        ];
                    }),
                ];
            });

            return ['app' => $app, 'editions' => $byEdition];
        })->values();

        return view('my-downloads.show', ['email' => $email, 'downloads' => $downloads]);
    }
}
