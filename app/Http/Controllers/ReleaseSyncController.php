<?php

namespace App\Http\Controllers;

use App\Models\App;
use App\Models\Platform;
use App\Services\ReleaseLibrary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

/**
 * CLI-triggerable release upload, mirroring Admin\ReleaseController::store()
 * but authenticated by a bearer token instead of an admin browser session —
 * same no-SSH problem as DeploySyncController, one level up: that endpoint
 * syncs schema/data, this one uploads the actual customer-facing binary
 * (§17/§23) so a release can ship end to end without opening the admin UI.
 *
 * Deliberately outside the admin/ route group for the same reason as
 * DeploySyncController (a CLI call has no browser session), and reuses
 * ReleaseLibrary::storeUploadedFile() so the platform/extension allow-list
 * and customer_downloadable rules are identical either way. Uploads made
 * this way are activated (made the app/platform's current release)
 * immediately unless `internal_only` is sent — that's the whole point of
 * calling this instead of the admin form, which requires a second
 * "Activate" click.
 */
class ReleaseSyncController extends Controller
{
    public function __invoke(Request $request, App $app, Platform $platform): JsonResponse
    {
        $expected = config('services.release_sync.token');
        abort_if(blank($expected), 404);

        $provided = (string) $request->bearerToken();
        abort_unless(hash_equals($expected, $provided), 403);

        $data = $request->validate([
            'version' => ['required', 'string', 'max:50'],
            'build_number' => ['nullable', 'string', 'max:50'],
            'release_notes' => ['nullable', 'string', 'max:2000'],
            'min_os' => ['nullable', 'string', 'max:100'],
            'released_at' => ['nullable', 'date'],
            'file' => ['required', 'file', 'max:512000', 'extensions:apk,aab,exe,msix,msixbundle'],
            'internal_only' => ['nullable', 'boolean'],
        ]);

        try {
            $release = ReleaseLibrary::storeUploadedFile(
                $request->file('file'),
                $app,
                $platform,
                customerDownloadable: ! $request->boolean('internal_only'),
                attributes: $data,
                uploadedBy: Auth::id(),
            );
        } catch (InvalidArgumentException $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 422);
        }

        if ($release->customer_downloadable) {
            ReleaseLibrary::makeCurrent($release);
        }

        return response()->json([
            'ok' => true,
            'release_id' => $release->id,
            'version' => $release->version,
            'platform' => $platform->code,
            'customer_downloadable' => $release->customer_downloadable,
            'is_current' => $release->fresh()->is_current,
            'checksum_sha256' => $release->checksum_sha256,
            'file_size' => $release->file_size,
        ]);
    }
}
