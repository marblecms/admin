<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function show(Request $request, string $filename)
    {
        if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $filename)) {
            abort(404);
        }

        if (!Storage::exists($filename)) {
            abort(404);
        }

        $mimeType         = Storage::mimeType($filename) ?: 'application/octet-stream';
        $originalFilename = $request->query('name', $filename);

        return response(Storage::get($filename), 200)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'attachment; filename="' . addslashes($originalFilename) . '"')
            ->header('Cache-Control', 'private, max-age=3600');
    }
}
