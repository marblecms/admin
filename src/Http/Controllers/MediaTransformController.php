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
        $w    = (int) $request->query('w', 0);
        $h    = (int) $request->query('h', 0);
        $crop = (bool) $request->query('crop', false);
        $fx   = max(0, min(100, (int) $request->query('fx', $media->focal_x ?? 50)));
        $fy   = max(0, min(100, (int) $request->query('fy', $media->focal_y ?? 50)));

        if (!$w && !$h) {
            return redirect($media->url());
        }

        $sourcePath = $media->path();

        if (!file_exists($sourcePath)) {
            abort(404);
        }

        $mime = $media->mime_type ?? mime_content_type($sourcePath);
        [$origW, $origH] = getimagesize($sourcePath);

        // Calculate target dimensions for proportional resize
        if ($w && !$h) {
            $h = (int) round($origH * $w / $origW);
        } elseif ($h && !$w) {
            $w = (int) round($origW * $h / $origH);
        }

        // Cache key includes crop parameters
        $cacheDir  = storage_path('app/public/marble-transforms');
        $suffix    = $crop ? "_{$w}x{$h}_crop{$fx}x{$fy}" : "_{$w}x{$h}";
        $cacheFile = $cacheDir . '/' . pathinfo($media->filename, PATHINFO_FILENAME) . $suffix . '.' . pathinfo($media->filename, PATHINFO_EXTENSION);

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

            if ($crop && $w && $h) {
                // Focal-point-aware cover crop:
                // 1. Scale so the image covers the target box (cover, not contain).
                // 2. Translate so the focal point lands at the center of the crop box.
                $scaleW = $w / $origW;
                $scaleH = $h / $origH;
                $scale  = max($scaleW, $scaleH);

                $scaledW = (int) round($origW * $scale);
                $scaledH = (int) round($origH * $scale);

                // Focal point in scaled pixels
                $focalPxX = (int) round($fx / 100 * $scaledW);
                $focalPxY = (int) round($fy / 100 * $scaledH);

                // Crop origin: center crop box on focal point, clamped to image bounds
                $srcX = max(0, min($scaledW - $w, $focalPxX - (int) round($w / 2)));
                $srcY = max(0, min($scaledH - $h, $focalPxY - (int) round($h / 2)));

                // Intermediate scaled canvas
                $scaled = imagecreatetruecolor($scaledW, $scaledH);
                if (str_contains($mime, 'png') || str_contains($mime, 'gif')) {
                    imagealphablending($scaled, false);
                    imagesavealpha($scaled, true);
                    $t = imagecolorallocatealpha($scaled, 0, 0, 0, 127);
                    imagefilledrectangle($scaled, 0, 0, $scaledW, $scaledH, $t);
                }
                imagecopyresampled($scaled, $src, 0, 0, 0, 0, $scaledW, $scaledH, $origW, $origH);

                // Copy cropped region into destination
                imagecopy($dst, $scaled, 0, 0, $srcX, $srcY, $w, $h);
                imagedestroy($scaled);
            } else {
                imagecopyresampled($dst, $src, 0, 0, 0, 0, $w, $h, $origW, $origH);
            }

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
