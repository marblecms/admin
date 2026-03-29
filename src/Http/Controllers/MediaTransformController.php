<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Marble\Admin\Models\Media;

/**
 * Serves resized/transformed versions of media files using PHP GD.
 *
 * Route: GET /marble/media/transform/{media}?w=800&h=600
 *
 * - w and h are optional. If only one is given the image is scaled proportionally.
 * - Results are cached as files in storage/app/public/marble-transforms/.
 */
class MediaTransformController extends Controller
{
    public function __invoke(Request $request, Media $media)
    {
        $w = (int) $request->query('w', 0);
        $h = (int) $request->query('h', 0);

        if (!$w && !$h) {
            return redirect($media->url());
        }

        $sourcePath = $media->path();

        if (!file_exists($sourcePath)) {
            abort(404);
        }

        // Determine output mime type
        $mime = $media->mime_type ?? mime_content_type($sourcePath);
        [$origW, $origH] = getimagesize($sourcePath);

        // Calculate target dimensions
        if ($w && !$h) {
            $h = (int) round($origH * $w / $origW);
        } elseif ($h && !$w) {
            $w = (int) round($origW * $h / $origH);
        }

        // Cache key
        $cacheDir  = storage_path('app/public/marble-transforms');
        $cacheFile = $cacheDir . '/' . pathinfo($media->filename, PATHINFO_FILENAME) . "_{$w}x{$h}." . pathinfo($media->filename, PATHINFO_EXTENSION);

        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        if (!file_exists($cacheFile)) {
            $src = match (true) {
                str_contains($mime, 'png')  => imagecreatefrompng($sourcePath),
                str_contains($mime, 'gif')  => imagecreatefromgif($sourcePath),
                str_contains($mime, 'webp') => imagecreatefromwebp($sourcePath),
                default                     => imagecreatefromjpeg($sourcePath),
            };

            $dst = imagecreatetruecolor($w, $h);

            // Preserve transparency for PNG/GIF
            if (str_contains($mime, 'png') || str_contains($mime, 'gif')) {
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
                $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
                imagefilledrectangle($dst, 0, 0, $w, $h, $transparent);
            }

            imagecopyresampled($dst, $src, 0, 0, 0, 0, $w, $h, $origW, $origH);

            match (true) {
                str_contains($mime, 'png')  => imagepng($dst, $cacheFile),
                str_contains($mime, 'gif')  => imagegif($dst, $cacheFile),
                str_contains($mime, 'webp') => imagewebp($dst, $cacheFile, 85),
                default                     => imagejpeg($dst, $cacheFile, 85),
            };

            imagedestroy($src);
            imagedestroy($dst);
        }

        return response()->file($cacheFile, [
            'Content-Type'  => $mime,
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }
}
