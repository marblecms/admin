<?php

namespace Marble\Admin\FieldTypes;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Marble\Admin\Models\Media;
use Marble\Admin\Models\MediaValue;

class Images extends BaseFieldType
{
    public function identifier(): string
    {
        return 'images';
    }

    public function name(): string
    {
        return 'Image Gallery';
    }

    public function isStructured(): bool
    {
        return true;
    }

    public function defaultValue(): mixed
    {
        return [];
    }

    public function process(mixed $raw, int $languageId): mixed
    {
        if (!$raw || !is_array($raw)) {
            return [];
        }

        return array_values(array_filter(array_map(function ($entry) use ($languageId) {
            $media = null;

            if (isset($entry['media_id'])) {
                $media = Media::find($entry['media_id']);
            } elseif (!empty($entry['filename'])) {
                $media = Media::where('filename', $entry['filename'])->first();
            }

            if (!$media) return null;

            $result = [
                'url'       => $media->isImage() ? url('/image/' . $media->filename) : url('/file/' . $media->filename),
                'thumbnail' => $media->isImage() ? url('/image/200/150/' . $media->filename) : null,
                'filename'  => $media->filename,
                'width'     => $media->width,
                'height'    => $media->height,
                'mime_type' => $media->mime_type,
                'size'      => $media->size,
            ];

            if ($media->blueprint_id) {
                $result = array_merge($result, $media->loadValuesForLanguage($languageId));
            }

            return $result;
        }, $raw)));
    }

    public function processInput(mixed $oldValue, mixed $newValue, Request $request, int $blueprintFieldId, int $languageId): mixed
    {
        $images = $oldValue ?? [];

        // Handle removals
        if (is_string($newValue) && $newValue !== 'noop' && $newValue !== '') {
            $removeKeys = explode(',', $newValue);
            foreach ($removeKeys as $key) {
                if (isset($images[$key])) {
                    // Only delete from storage if not a media library reference
                    if (empty($images[$key]['media_id'])) {
                        Storage::delete($images[$key]['filename'] ?? '');
                    }
                    unset($images[$key]);
                }
            }
            $images = array_values($images);
        }

        // Handle library additions (comma-separated media IDs)
        $libraryIds = $request->input("library_add.{$blueprintFieldId}.{$languageId}", '');
        if ($libraryIds) {
            foreach (explode(',', $libraryIds) as $mediaId) {
                $mediaId = (int) trim($mediaId);
                $media   = $mediaId ? Media::find($mediaId) : null;
                if ($media) {
                    $images[] = [
                        'media_id'          => $media->id,
                        'filename'          => $media->filename,
                        'original_filename' => $media->original_filename,
                        'size'              => $media->size,
                        'mime_type'         => $media->mime_type,
                        'transformations'   => [],
                    ];
                }
            }
        }

        // Handle new upload
        $fileKey = "file_{$blueprintFieldId}_{$languageId}";
        if ($request->hasFile($fileKey)) {
            $file = $request->file($fileKey);
            $filename = $file->hashName();

            Storage::put($filename, file_get_contents($file));

            $images[] = [
                'original_filename' => $file->getClientOriginalName(),
                'filename' => $filename,
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'transformations' => [],
            ];
        }

        return $images;
    }

    public function registerRoutes(): void
    {
        Route::post('field-type/images/sort', function (Request $request) {
            $itemValue = \Marble\Admin\Models\ItemValue::find($request->input('item_value_id'));

            if (!$itemValue) {
                return response()->json(['error' => 'Not found'], 404);
            }

            $images = json_decode($itemValue->value, true);
            $sortOrder = $request->input('sort_order', []);
            $sorted = [];

            foreach ($sortOrder as $index) {
                if (isset($images[$index])) {
                    $sorted[] = $images[$index];
                }
            }

            $itemValue->value = json_encode($sorted);
            $itemValue->save();

            return response()->json(['success' => true]);
        })->name('marble.field-type.images.sort');

        Route::post('field-type/images/transformations', function (Request $request) {
            $itemValue = \Marble\Admin\Models\ItemValue::find($request->input('item_value_id'));

            if (!$itemValue) {
                return response()->json(['error' => 'Not found'], 404);
            }

            $images = json_decode($itemValue->value, true);
            $index = $request->input('index');

            if (isset($images[$index])) {
                $images[$index]['transformations'] = $request->input('transformations', []);
                $itemValue->value = json_encode($images);
                $itemValue->save();
            }

            return response()->json(['success' => true]);
        })->name('marble.field-type.images.transformations');
    }

    public function scripts(): array
    {
        return ['images-edit.js'];
    }

    public function isEmpty(?string $raw): bool
    {
        return $raw === null || $raw === '' || $raw === '[]';
    }
}
