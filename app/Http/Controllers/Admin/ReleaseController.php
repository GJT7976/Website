<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\App;
use App\Models\AppRelease;
use App\Models\Platform;
use App\Services\ReleaseLibrary;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class ReleaseController extends Controller
{
    public function store(Request $request, App $app): RedirectResponse
    {
        $data = $request->validate([
            'platform_id' => ['required', 'exists:platforms,id'],
            'version' => ['required', 'string', 'max:50'],
            'build_number' => ['nullable', 'string', 'max:50'],
            'release_notes' => ['nullable', 'string', 'max:2000'],
            'min_os' => ['nullable', 'string', 'max:100'],
            'released_at' => ['nullable', 'date'],
            // §17/§23: only recognized platform installers/packages — the
            // "extensions" rule checks the actual file extension directly
            // rather than a MIME-type map (apk/msix/msixbundle aren't in
            // the standard MIME registry the "mimes" rule relies on).
            // Enforced again, harder, per-platform in ReleaseLibrary itself.
            'file' => ['required', 'file', 'max:512000', 'extensions:apk,aab,exe,msix,msixbundle'],
            'internal_only' => ['nullable', 'boolean'],
        ]);

        $platform = Platform::findOrFail($data['platform_id']);

        try {
            ReleaseLibrary::storeUploadedFile(
                $request->file('file'),
                $app,
                $platform,
                customerDownloadable: ! $request->boolean('internal_only'),
                attributes: $data,
                uploadedBy: Auth::id(),
            );
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['file' => $e->getMessage()]);
        }

        return back()->with('status', "Release {$data['version']} uploaded for {$platform->name}.");
    }

    public function activate(App $app, AppRelease $release): RedirectResponse
    {
        abort_unless($release->app_id === $app->id, 404);
        abort_unless($release->customer_downloadable, 422, 'A developer/store-only release can\'t be made the current customer download.');

        ReleaseLibrary::makeCurrent($release);

        return back()->with('status', "{$release->version} is now the current release for {$release->platform->name}.");
    }

    public function destroy(App $app, AppRelease $release): RedirectResponse
    {
        abort_unless($release->app_id === $app->id, 404);

        Storage::disk($release->disk)->delete($release->path);
        $release->delete();

        return back()->with('status', 'Release deleted.');
    }
}
