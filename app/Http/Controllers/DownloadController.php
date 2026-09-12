<?php

namespace App\Http\Controllers;

use App\Models\CustomerEntitlement;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Protected downloads (§18) — never a public /downloads/App.apk-style
 * path. Every request arrives via a short-lived signed URL generated from
 * My Downloads, and is re-verified here rather than trusted on sight:
 * entitlement still active, its order (when there is one) still paid, and
 * the resolved current release is still marked customer-downloadable.
 */
class DownloadController extends Controller
{
    public function download(CustomerEntitlement $entitlement): StreamedResponse
    {
        abort_unless($entitlement->status === 'active', 403);
        abort_if($entitlement->order && $entitlement->order->payment_status !== 'paid', 403);
        abort_unless($entitlement->access_type === 'download', 404);

        $release = $entitlement->currentRelease();

        abort_unless($release && $release->customer_downloadable, 404);

        $downloadName = $entitlement->app->slug.'-'.$entitlement->platform->code.'-'.$release->version.'.'.pathinfo($release->original_filename, PATHINFO_EXTENSION);

        return Storage::disk($release->disk)->download($release->path, $downloadName);
    }
}
