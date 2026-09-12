<?php

namespace App\Services;

use App\Models\App;
use App\Models\AppRelease;
use App\Models\Platform;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Shared upload logic for platform release files — mirrors
 * App\Services\MediaLibrary's pattern, but stores to the private "local"
 * disk (never web-served) and enforces §3/§23: a hard allow-list of
 * extensions per platform, not just a UI default. A customer-downloadable
 * .aab (or .env, source archive, keystore, anything else) is rejected
 * outright rather than merely discouraged.
 */
class ReleaseLibrary
{
    /**
     * Extensions a *customer-downloadable* release may use, per platform code.
     */
    private const CUSTOMER_EXTENSIONS = [
        'android' => ['apk'],
        'windows' => ['exe', 'msix', 'msixbundle'],
    ];

    /**
     * Extensions allowed only when explicitly marked internal-only
     * (customer_downloadable = false) — e.g. an AAB kept for the admin's
     * own Play Store publishing record.
     */
    private const INTERNAL_ONLY_EXTENSIONS = [
        'android' => ['aab'],
    ];

    public static function storeUploadedFile(
        UploadedFile $file,
        App $app,
        Platform $platform,
        bool $customerDownloadable,
        array $attributes = [],
        ?int $uploadedBy = null,
    ): AppRelease {
        $extension = mb_strtolower($file->getClientOriginalExtension());
        $customerAllowed = self::CUSTOMER_EXTENSIONS[$platform->code] ?? [];
        $internalOnlyAllowed = self::INTERNAL_ONLY_EXTENSIONS[$platform->code] ?? [];

        // Internal-only extensions (e.g. .aab) can never be customer-downloadable,
        // regardless of what the admin form submitted — checked first so
        // the validation below judges the corrected flag, not the raw one.
        if (in_array($extension, $internalOnlyAllowed, true)) {
            $customerDownloadable = false;
        }

        if ($customerDownloadable && ! in_array($extension, $customerAllowed, true)) {
            throw new InvalidArgumentException(
                "\".{$extension}\" files can't be marked customer-downloadable for {$platform->name}. Allowed: ".implode(', ', $customerAllowed)
            );
        }

        if (! $customerDownloadable && ! in_array($extension, [...$customerAllowed, ...$internalOnlyAllowed], true)) {
            throw new InvalidArgumentException("\".{$extension}\" isn't a recognized release file type for {$platform->name}.");
        }

        $directory = "releases/{$app->id}/{$platform->code}";
        $filename = Str::random(20).'.'.$extension;
        $path = $file->storeAs($directory, $filename, 'local');
        $fullPath = Storage::disk('local')->path($path);

        return AppRelease::create([
            'app_id' => $app->id,
            'platform_id' => $platform->id,
            'version' => $attributes['version'],
            'build_number' => $attributes['build_number'] ?? null,
            'release_notes' => $attributes['release_notes'] ?? null,
            'min_os' => $attributes['min_os'] ?? null,
            'released_at' => $attributes['released_at'] ?? now()->toDateString(),
            'is_current' => false,
            'disk' => 'local',
            'path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'checksum_sha256' => hash_file('sha256', $fullPath) ?: null,
            'mime_type' => $file->getMimeType(),
            'customer_downloadable' => $customerDownloadable,
            'uploaded_by' => $uploadedBy,
        ]);
    }

    /**
     * Mark one release as the current authorized one for its app/platform,
     * unsetting any previous current release — same detach-then-set
     * pattern as icon/feature-graphic replacement in Admin\AppController.
     */
    public static function makeCurrent(AppRelease $release): void
    {
        AppRelease::query()
            ->where('app_id', $release->app_id)
            ->where('platform_id', $release->platform_id)
            ->update(['is_current' => false]);

        $release->update(['is_current' => true]);
    }
}
