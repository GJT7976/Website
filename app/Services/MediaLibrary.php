<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Shared upload logic for the media library — used both by the dedicated
 * Media Library screen and the quick "upload & attach" flow on an app's
 * edit page, so the two never drift apart.
 */
class MediaLibrary
{
    public static function storeUploadedFile(UploadedFile $file, ?int $uploadedBy = null, array $attributes = []): Media
    {
        $path = $file->store('media/uploads', 'public');
        $fullPath = Storage::disk('public')->path($path);

        $thumbnailPath = null;
        $thumbCandidate = 'media/uploads/thumbs/'.Str::random(20).'.jpg';
        Storage::disk('public')->makeDirectory('media/uploads/thumbs');

        if (ImageService::makeThumbnail($fullPath, Storage::disk('public')->path($thumbCandidate))) {
            $thumbnailPath = $thumbCandidate;
        }

        [$width, $height] = @getimagesize($fullPath) ?: [null, null];

        return Media::create([
            'disk' => 'public',
            'path' => $path,
            'thumbnail_path' => $thumbnailPath,
            'mime' => $file->getMimeType(),
            'size' => $file->getSize(),
            'width' => $width,
            'height' => $height,
            'title' => $attributes['title'] ?? $file->getClientOriginalName(),
            'alt_text' => $attributes['alt_text'] ?? null,
            'description' => $attributes['description'] ?? null,
            'uploaded_by' => $uploadedBy,
        ]);
    }
}
