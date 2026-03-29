<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Marble\Admin\Models\Media;

class ImageController extends Controller
{
    public function show(Request $request, string $filename)
    {
        if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $filename)) {
            abort(404);
        }

        if (!Storage::exists($filename)) {
            abort(404);
        }

        return response(Storage::get($filename), 200)
            ->header('Content-Type', Storage::mimeType($filename))
            ->header('Cache-Control', 'public, max-age=31536000');
    }

    public function showResized(Request $request, int $width, int $height, string $filename)
    {
        if (!Storage::exists($filename)) {
            abort(404);
        }

        // Cap dimensions
        $width  = min($width, 2000);
        $height = min($height, 2000);

        // Load focal point from DB (default 50/50 = center)
        $media   = Media::where('filename', $filename)->first();
        $focalX  = $media?->focal_x ?? 50;
        $focalY  = $media?->focal_y ?? 50;

        // Include focal point in cache key so changing it invalidates the cache
        $cachePath = "cache/{$width}x{$height}/fp{$focalX}x{$focalY}/{$filename}";

        if (Storage::exists($cachePath)) {
            return response(Storage::get($cachePath), 200)
                ->header('Content-Type', Storage::mimeType($cachePath))
                ->header('Cache-Control', 'public, max-age=31536000');
        }

        $sourcePath = Storage::path($filename);
        $mime       = Storage::mimeType($filename);
        $source     = $this->createImageFromFile($sourcePath, $mime);

        if (!$source) {
            return $this->show($request, $filename);
        }

        $origW = imagesx($source);
        $origH = imagesy($source);

        if ($width > 0 && $height > 0) {
            // Cover crop: scale to cover target, then crop anchored to focal point
            $ratio   = max($width / $origW, $height / $origH);
            $scaledW = (int) round($origW * $ratio);
            $scaledH = (int) round($origH * $ratio);

            // Focal point pixel position on the scaled image
            $fpX = (int) round(($focalX / 100) * $scaledW);
            $fpY = (int) round(($focalY / 100) * $scaledH);

            // Crop offsets — keep focal point centred, clamped to image bounds
            $cropX = max(0, min($scaledW - $width,  (int) round($fpX - $width  / 2)));
            $cropY = max(0, min($scaledH - $height, (int) round($fpY - $height / 2)));

            $canvas = imagecreatetruecolor($width, $height);
            if (in_array($mime, ['image/png', 'image/gif'])) {
                imagealphablending($canvas, false);
                imagesavealpha($canvas, true);
            }

            // Scale first into a temp image, then crop
            $scaled = imagecreatetruecolor($scaledW, $scaledH);
            if (in_array($mime, ['image/png', 'image/gif'])) {
                imagealphablending($scaled, false);
                imagesavealpha($scaled, true);
            }
            imagecopyresampled($scaled, $source, 0, 0, 0, 0, $scaledW, $scaledH, $origW, $origH);
            imagecopy($canvas, $scaled, 0, 0, $cropX, $cropY, $width, $height);
            imagedestroy($scaled);

            $output = $canvas;
        } else {
            // Fit within bounds, maintain aspect ratio
            $ratio = min(
                $width  ? $width  / $origW : PHP_INT_MAX,
                $height ? $height / $origH : PHP_INT_MAX
            );
            $newW = (int) round($origW * $ratio);
            $newH = (int) round($origH * $ratio);

            $output = imagecreatetruecolor($newW, $newH);
            if (in_array($mime, ['image/png', 'image/gif'])) {
                imagealphablending($output, false);
                imagesavealpha($output, true);
            }
            imagecopyresampled($output, $source, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
        }

        ob_start();
        $this->outputImage($output, $mime);
        $data = ob_get_clean();

        imagedestroy($source);
        imagedestroy($output);

        Storage::put($cachePath, $data);

        return response($data, 200)
            ->header('Content-Type', $mime)
            ->header('Cache-Control', 'public, max-age=31536000');
    }

    private function createImageFromFile(string $path, string $mime): ?\GdImage
    {
        return match ($mime) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($path),
            'image/png'               => @imagecreatefrompng($path),
            'image/gif'               => @imagecreatefromgif($path),
            'image/webp'              => @imagecreatefromwebp($path),
            default                   => null,
        };
    }

    private function outputImage(\GdImage $image, string $mime): void
    {
        match ($mime) {
            'image/png'  => imagepng($image),
            'image/gif'  => imagegif($image),
            'image/webp' => imagewebp($image),
            default      => imagejpeg($image, null, 85),
        };
    }
}
