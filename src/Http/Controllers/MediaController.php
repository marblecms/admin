<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Marble\Admin\Models\Media;
use Marble\Admin\Models\MediaBlueprintRule;
use Marble\Admin\Models\MediaFolder;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        $currentFolder = $request->query('folder') ? MediaFolder::find($request->query('folder')) : null;
        $folders       = MediaFolder::where('parent_id', $currentFolder?->id ?? null)->orderBy('name')->get();
        $allFolders    = MediaFolder::orderBy('name')->get();
        $media         = Media::where('media_folder_id', $currentFolder?->id ?? null)
                              ->orderByDesc('created_at')->paginate(40);

        $breadcrumb = [];
        if ($currentFolder) {
            $breadcrumb = $currentFolder->ancestors();
            $breadcrumb[] = $currentFolder;
        }

        return view('marble::media.index', compact('media', 'folders', 'currentFolder', 'allFolders', 'breadcrumb'));
    }

    public function createFolder(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        MediaFolder::create([
            'name'      => $request->input('name'),
            'parent_id' => $request->input('parent_id') ?: null,
        ]);
        return redirect()->route('marble.media.index', ['folder' => $request->input('parent_id') ?: null]);
    }

    public function renameFolder(Request $request, MediaFolder $folder)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $folder->update(['name' => $request->input('name')]);
        return redirect()->route('marble.media.index', ['folder' => $folder->parent_id ?: null]);
    }

    public function deleteFolder(MediaFolder $folder)
    {
        // Move contents to parent folder
        Media::where('media_folder_id', $folder->id)->update(['media_folder_id' => $folder->parent_id]);
        MediaFolder::where('parent_id', $folder->id)->update(['parent_id' => $folder->parent_id]);
        $folder->delete();
        return redirect()->route('marble.media.index', ['folder' => $folder->parent_id ?: null]);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:51200',
        ]);

        $file     = $request->file('file');
        $filename = $file->hashName();

        Storage::put($filename, file_get_contents($file));

        $width = null;
        $height = null;
        if (str_starts_with($file->getMimeType(), 'image/') && $file->getMimeType() !== 'image/svg+xml') {
            $size = @getimagesize($file->getRealPath());
            if ($size) {
                $width  = $size[0];
                $height = $size[1];
            }
        }

        $blueprint = MediaBlueprintRule::resolveForMime($file->getMimeType());

        $media = Media::create([
            'filename'          => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'disk'              => 'public',
            'mime_type'         => $file->getMimeType(),
            'size'              => $file->getSize(),
            'width'             => $width,
            'height'            => $height,
            'media_folder_id'   => $request->input('folder_id') ?: null,
            'blueprint_id'      => $blueprint?->id,
        ]);

        if ($request->wantsJson()) {
            return response()->json($media);
        }

        // If a blueprint was assigned, redirect to the field editor
        if ($blueprint) {
            return redirect()->route('marble.media.fields.edit', $media);
        }

        return redirect()->route('marble.media.index');
    }

    public function delete(Media $media)
    {
        $media->delete();

        return redirect()->route('marble.media.index');
    }

    public function json()
    {
        return response()->json(
            Media::orderByDesc('created_at')->get()->map(fn($m) => [
                'id'                => $m->id,
                'filename'          => $m->filename,
                'original_filename' => $m->original_filename,
                'mime_type'         => $m->mime_type,
                'size'              => $m->size,
                'width'             => $m->width,
                'height'            => $m->height,
                'url'               => $m->isImage() ? url('/image/' . $m->filename) : url('/file/' . $m->filename),
                'thumbnail'         => $m->isImage() ? url('/image/120/90/' . $m->filename) : null,
            ])
        );
    }

    public function pickerJson(Request $request)
    {
        $folderId = $request->query('folder') ? (int) $request->query('folder') : null;
        $q        = trim($request->query('q', ''));
        $type     = $request->query('type', 'all'); // 'image' or 'all'

        // Folders (only shown when not searching)
        $folders = [];
        if (!$q) {
            $folders = MediaFolder::where('parent_id', $folderId)->orderBy('name')->get()
                ->map(fn($f) => ['id' => $f->id, 'name' => $f->name])
                ->values();
        }

        $mediaQuery = Media::query();

        if ($q) {
            $mediaQuery->where('original_filename', 'like', '%' . addcslashes($q, '%_\\') . '%');
        } else {
            $mediaQuery->where('media_folder_id', $folderId);
        }

        if ($type === 'image') {
            $mediaQuery->where('mime_type', 'like', 'image/%');
        }

        $media = $mediaQuery->orderByDesc('created_at')->limit(80)->get()
            ->map(fn($m) => [
                'id'                => $m->id,
                'original_filename' => $m->original_filename,
                'mime_type'         => $m->mime_type,
                'size'              => $m->size,
                'url'               => $m->isImage() ? url('/image/' . $m->filename) : url('/file/' . $m->filename),
                'thumbnail'         => $m->isImage() ? url('/image/120/90/' . $m->filename) : null,
                'filename'          => $m->filename,
            ]);

        $breadcrumb     = [];
        $parentFolderId = null;
        if ($folderId) {
            $currentFolder = MediaFolder::find($folderId);
            if ($currentFolder) {
                $parentFolderId = $currentFolder->parent_id ?? 'root';
                foreach ($currentFolder->ancestors() as $ancestor) {
                    $breadcrumb[] = ['id' => $ancestor->id, 'name' => $ancestor->name];
                }
                $breadcrumb[] = ['id' => $currentFolder->id, 'name' => $currentFolder->name];
            }
        }

        return response()->json([
            'folders'           => $folders,
            'media'             => $media,
            'current_folder_id' => $folderId,
            'parent_folder_id'  => $parentFolderId,
            'breadcrumb'        => $breadcrumb,
        ]);
    }

    public function ckeditorUpload(Request $request)
    {
        $funcNum = $request->query('CKEditorFuncNum', 0);

        $request->validate(['upload' => 'required|file|image|max:51200']);

        $file     = $request->file('upload');
        $filename = $file->hashName();

        Storage::put($filename, file_get_contents($file));

        $size = @getimagesize($file->getRealPath());

        Media::create([
            'filename'          => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'disk'              => 'public',
            'mime_type'         => $file->getMimeType(),
            'size'              => $file->getSize(),
            'width'             => $size[0] ?? null,
            'height'            => $size[1] ?? null,
        ]);

        $url = url('/image/' . $filename);

        return response('<script>window.parent.CKEDITOR.tools.callFunction(' . (int)$funcNum . ', ' . json_encode($url) . ', "");</script>')
            ->header('Content-Type', 'text/html');
    }

    public function saveFocalPoint(Request $request, Media $media)
    {
        $request->validate([
            'focal_x' => 'required|integer|min:0|max:100',
            'focal_y' => 'required|integer|min:0|max:100',
        ]);

        $media->update([
            'focal_x' => $request->integer('focal_x'),
            'focal_y' => $request->integer('focal_y'),
        ]);

        // Bust cached crops for this file
        $cacheFiles = \Illuminate\Support\Facades\Storage::allFiles('cache');
        foreach ($cacheFiles as $cached) {
            if (str_ends_with($cached, '/' . $media->filename)) {
                \Illuminate\Support\Facades\Storage::delete($cached);
            }
        }

        return response()->json(['focal_x' => $media->focal_x, 'focal_y' => $media->focal_y]);
    }
}
