<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Marble\Admin\Models\Media;
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
            'file' => 'required|file|mimes:jpeg,jpg,png,gif,webp,svg|max:20480',
        ]);

        $file     = $request->file('file');
        $filename = $file->hashName();

        Storage::put($filename, file_get_contents($file));

        $media = Media::create([
            'filename'          => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'disk'              => 'public',
            'mime_type'         => $file->getMimeType(),
            'size'              => $file->getSize(),
            'media_folder_id'   => $request->input('folder_id') ?: null,
        ]);

        if ($request->wantsJson()) {
            return response()->json($media);
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
                'url'               => url('/image/' . $m->filename),
                'thumbnail'         => url('/image/120/90/' . $m->filename),
            ])
        );
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
