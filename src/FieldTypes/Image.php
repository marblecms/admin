<?php

namespace Marble\Admin\FieldTypes;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Marble\Admin\Models\Media;

class Image extends BaseFieldType
{
    public function identifier(): string
    {
        return 'image';
    }

    public function name(): string
    {
        return 'Image';
    }

    public function isStructured(): bool
    {
        return true;
    }

    public function defaultValue(): mixed
    {
        return null;
    }

    /**
     * Return a URL for the stored image.
     * Supports both new format {"media_id":5} and legacy {"filename":"..."}.
     */
    public function process(mixed $raw, int $languageId): mixed
    {
        if (!$raw || !is_array($raw)) {
            return null;
        }

        // New format: media_id reference
        if (isset($raw['media_id'])) {
            $media = Media::find($raw['media_id']);
            return $media ? url('/image/' . $media->filename) : null;
        }

        // Legacy format: inline filename
        if (!empty($raw['filename'])) {
            return url('/image/' . $raw['filename']);
        }

        return null;
    }

    public function processInput(mixed $oldValue, mixed $newValue, Request $request, int $blueprintFieldId, int $languageId): mixed
    {
        // Remove image
        if ($newValue === 'remove') {
            return null;
        }

        // Select from media library: value is "library:{media_id}"
        if (is_string($newValue) && str_starts_with($newValue, 'library:')) {
            $mediaId = (int) substr($newValue, 8);
            if (Media::where('id', $mediaId)->exists()) {
                return ['media_id' => $mediaId];
            }
            return $oldValue;
        }

        $fileKey = "file_{$blueprintFieldId}_{$languageId}";

        // No new file uploaded — keep old value
        if (!$request->hasFile($fileKey)) {
            return $oldValue;
        }

        $request->validate([
            $fileKey => 'nullable|file|mimes:jpeg,jpg,png,gif,webp,svg|max:10240',
        ]);

        $file     = $request->file($fileKey);
        $filename = $file->hashName();

        Storage::put($filename, file_get_contents($file));

        // Create a Media record so the file appears in the library
        $media = Media::create([
            'filename'          => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'disk'              => 'public',
            'mime_type'         => $file->getMimeType(),
            'size'              => $file->getSize(),
        ]);

        return ['media_id' => $media->id];
    }

    public function registerRoutes(): void
    {
        Route::post('field-type/image/transformations', function (Request $request) {
            $itemValue = \Marble\Admin\Models\ItemValue::find($request->input('item_value_id'));

            if (!$itemValue) {
                return response()->json(['error' => 'Not found'], 404);
            }

            $value = json_decode($itemValue->value, true);
            $value['transformations'] = $request->input('transformations', []);
            $itemValue->value = json_encode($value);
            $itemValue->save();

            return response()->json(['success' => true]);
        })->name('marble.field-type.image.transformations');
    }

    public function scripts(): array
    {
        return ['image-edit.js'];
    }
}
