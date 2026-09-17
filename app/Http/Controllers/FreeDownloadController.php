<?php

namespace App\Http\Controllers;

use App\Models\App;
use App\Models\Platform;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Public, unauthenticated download for an app whose base install is free
 * (App::is_free === true) — distinct from DownloadController, which always
 * requires a paid CustomerEntitlement. Restricted to $app->is_free apps
 * only, so a paid app's release file can never be reached through this
 * route regardless of how it's linked to.
 *
 * Used by apps like The Stock Pot, where the install itself is free and a
 * separate optional AppEdition purchase (see LICENSE_SYSTEM.md) unlocks an
 * in-app Pro license rather than gating the download.
 */
class FreeDownloadController extends Controller
{
    public function download(App $app, Platform $platform): StreamedResponse
    {
        abort_unless($app->is_free, 404);

        $release = $app->currentRelease($platform);

        abort_unless($release && $release->customer_downloadable, 404);

        $downloadName = $app->slug.'-'.$platform->code.'-'.$release->version.'.'.pathinfo($release->original_filename, PATHINFO_EXTENSION);

        return Storage::disk($release->disk)->download($release->path, $downloadName);
    }
}
