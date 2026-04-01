<?php

namespace Marble\Admin\FieldTypes;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

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

    public function processInput(mixed $oldValue, mixed $newValue, Request $request, int $blueprintFieldId, int $languageId): mixed
    {
        $images = $oldValue ?? [];

        // Handle removals
        if (is_string($newValue) && $newValue !== 'noop' && $newValue !== '') {
            $removeKeys = explode(',', $newValue);
            foreach ($removeKeys as $key) {
                if (isset($images[$key])) {
                    Storage::delete($images[$key]['filename'] ?? '');
                    unset($images[$key]);
                }
            }
            $images = array_values($images);
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
