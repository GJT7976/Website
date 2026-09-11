<?php

namespace App\Services;

/**
 * Small GD-based thumbnail generator. Deliberately avoids adding an extra
 * Composer dependency (e.g. Intervention Image) for a Phase 1 media
 * library — this covers the common raster formats without one.
 */
class ImageService
{
    /**
     * Create a resized copy of $sourcePath (max dimension $maxSize,
     * aspect-ratio preserved) at $destinationPath. Returns false for
     * formats it doesn't understand (e.g. SVG) rather than throwing —
     * callers should fall back to the original file.
     */
    public static function makeThumbnail(string $sourcePath, string $destinationPath, int $maxSize = 480): bool
    {
        $info = @getimagesize($sourcePath);

        if ($info === false) {
            return false;
        }

        [$width, $height, $type] = $info;

        $source = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($sourcePath),
            IMAGETYPE_PNG => @imagecreatefrompng($sourcePath),
            IMAGETYPE_GIF => @imagecreatefromgif($sourcePath),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($sourcePath) : false,
            default => false,
        };

        if (! $source) {
            return false;
        }

        $ratio = min(1, $maxSize / max($width, $height));
        $newWidth = max(1, (int) round($width * $ratio));
        $newHeight = max(1, (int) round($height * $ratio));

        $thumb = imagecreatetruecolor($newWidth, $newHeight);

        if (in_array($type, [IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP], true)) {
            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);
        }

        imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        $saved = match ($type) {
            IMAGETYPE_JPEG => imagejpeg($thumb, $destinationPath, 82),
            IMAGETYPE_PNG => imagepng($thumb, $destinationPath, 6),
            IMAGETYPE_GIF => imagegif($thumb, $destinationPath),
            IMAGETYPE_WEBP => function_exists('imagewebp') ? imagewebp($thumb, $destinationPath, 82) : false,
            default => false,
        };

        imagedestroy($source);
        imagedestroy($thumb);

        return (bool) $saved;
    }
}
