<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

/**
 * Picks the picture to hand to og:image and structured data.
 *
 * The site stores a jpg next to every webp it generates. Messengers preview
 * jpg far more reliably, so the twin is preferred wherever one exists, and the
 * URL is absolute because a share has no page to resolve a relative one
 * against.
 */
class PreviewImage
{
    public static function url(?string $path): ?string
    {
        $path = trim((string) $path);

        if ($path === '') {
            return null;
        }

        $jpgPath = pathinfo($path, PATHINFO_DIRNAME)
            .'/'.pathinfo($path, PATHINFO_FILENAME).'.jpg';

        if (Storage::disk(config('app.images_disk_default'))->exists($jpgPath)) {
            $path = $jpgPath;
        }

        return url(Storage::url($path));
    }
}
