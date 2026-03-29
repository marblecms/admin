<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;

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
        $width = min($width, 2000);
        $height = min($height, 2000);

        $cachePath = "cache/{$width}x{$height}/{$filename}";

        if (Storage::exists($cachePath)) {
            return response(Storage::get($cachePath), 200)
                ->header('Content-Type', Storage::mimeType($cachePath))
                ->header('Cache-Control', 'public, max-age=31536000');
        }

        $sourcePath = Storage::path($filename);
        $mime = Storage::mimeType($filename);
        $source = $this->createImageFromFile($sourcePath, $mime);

        if (!$source) {
            return $this->show($request, $filename);
        }

        $origW = imagesx($source);
        $origH = imagesy($source);

        // Fit within bounds, maintain aspect ratio
        $ratio = min($width / $origW, $height / $origH);
        $newW = (int) round($origW * $ratio);
        $newH = (int) round($origH * $ratio);

        $resized = imagecreatetruecolor($newW, $newH);

        // Preserve transparency for PNG/GIF
        if (in_array($mime, ['image/png', 'image/gif'])) {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
        }

        imagecopyresampled($resized, $source, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

        ob_start();
        $this->outputImage($resized, $mime);
        $data = ob_get_clean();

        imagedestroy($source);
        imagedestroy($resized);

        Storage::put($cachePath, $data);

        return response($data, 200)
            ->header('Content-Type', $mime)
            ->header('Cache-Control', 'public, max-age=31536000');
    }

    private function createImageFromFile(string $path, string $mime): ?\GdImage
    {
        return match ($mime) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/gif' => @imagecreatefromgif($path),
            'image/webp' => @imagecreatefromwebp($path),
            default => null,
        };
    }

    private function outputImage(\GdImage $image, string $mime): void
    {
        match ($mime) {
            'image/png' => imagepng($image),
            'image/gif' => imagegif($image),
            'image/webp' => imagewebp($image),
            default => imagejpeg($image, null, 85),
        };
    }
}
