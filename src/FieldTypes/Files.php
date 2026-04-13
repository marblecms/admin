<?php

namespace Marble\Admin\FieldTypes;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Marble\Admin\Models\BlueprintField;
use Marble\Admin\Models\Media;

class Files extends BaseFieldType
{
    public function identifier(): string
    {
        return 'files';
    }

    public function name(): string
    {
        return 'Files';
    }

    public function isStructured(): bool
    {
        return true;
    }

    public function defaultValue(): mixed
    {
        return [];
    }

    public function configSchema(): array
    {
        return [
            'allowed_filetypes' => '',
        ];
    }

    public function defaultConfig(): array
    {
        return [
            'allowed_filetypes' => '',
        ];
    }

    public function configComponent(): ?string
    {
        return 'marble::field-types.files-config';
    }

    /**
     * Returns an array of file info arrays with public download URLs.
     */
    public function process(mixed $raw, int $languageId): mixed
    {
        if (!$raw || !is_array($raw)) {
            return [];
        }

        return array_map(function ($entry) {
            return [
                'url'               => url('/file/' . $entry['filename'] . '?name=' . rawurlencode($entry['original_filename'] ?? $entry['filename'])),
                'original_filename' => $entry['original_filename'] ?? $entry['filename'],
                'mime_type'         => $entry['mime_type'] ?? '',
                'size'              => $entry['size'] ?? 0,
            ];
        }, array_values($raw));
    }

    public function processInput(mixed $oldValue, mixed $newValue, Request $request, int $blueprintFieldId, int $languageId): mixed
    {
        $files = $oldValue ?? [];

        // Collect removed indices and delete their storage files
        $removeKeys = [];
        if (is_string($newValue) && $newValue !== 'noop' && $newValue !== '') {
            $removeKeys = array_map('trim', explode(',', $newValue));
            foreach ($removeKeys as $key) {
                if (isset($files[$key])) {
                    Storage::delete($files[$key]['filename'] ?? '');
                }
            }
        }

        // Apply order input (original indices in new order, removals already excluded by JS)
        $orderStr = $request->input("fields_order.{$blueprintFieldId}.{$languageId}");
        if ($orderStr !== null && $orderStr !== '') {
            $orderIndices = array_map('intval', explode(',', $orderStr));
            $reordered = [];
            foreach ($orderIndices as $idx) {
                if (isset($files[$idx]) && !in_array((string)$idx, $removeKeys)) {
                    $reordered[] = $files[$idx];
                }
            }
            $files = $reordered;
        } else {
            foreach ($removeKeys as $key) {
                unset($files[$key]);
            }
            $files = array_values($files);
        }

        // Handle library additions (comma-separated media IDs)
        $libraryIds = $request->input("library_add.{$blueprintFieldId}.{$languageId}", '');
        if ($libraryIds) {
            foreach (explode(',', $libraryIds) as $mediaId) {
                $mediaId = (int) trim($mediaId);
                $media   = $mediaId ? Media::find($mediaId) : null;
                if ($media) {
                    $files[] = [
                        'media_id'          => $media->id,
                        'filename'          => $media->filename,
                        'original_filename' => $media->original_filename,
                        'mime_type'         => $media->mime_type,
                        'size'              => $media->size,
                    ];
                }
            }
        }

        // Handle new upload
        $fileKey = "file_{$blueprintFieldId}_{$languageId}";
        if ($request->hasFile($fileKey)) {
            $fieldModel       = BlueprintField::find($blueprintFieldId);
            $config           = $fieldModel?->configuration ?? [];
            $allowedFiletypes = trim($config['allowed_filetypes'] ?? '');
            $mimeRule         = $this->buildMimeRule($allowedFiletypes);

            $rules = ['nullable', 'file', 'max:51200'];
            if ($mimeRule) {
                $rules[] = $mimeRule;
            }

            $request->validate([$fileKey => $rules]);

            $file     = $request->file($fileKey);
            $filename = $file->hashName();

            Storage::put($filename, file_get_contents($file));

            $files[] = [
                'filename'          => $filename,
                'original_filename' => $file->getClientOriginalName(),
                'mime_type'         => $file->getMimeType(),
                'size'              => $file->getSize(),
            ];
        }

        return $files;
    }

    private function buildMimeRule(string $allowedFiletypes): ?string
    {
        if ($allowedFiletypes === '') {
            return null;
        }

        $extensions = array_map('trim', explode(',', $allowedFiletypes));
        $extensions = array_filter($extensions);

        return empty($extensions) ? null : 'mimes:' . implode(',', $extensions);
    }

    public function isEmpty(?string $raw): bool
    {
        return $raw === null || $raw === '' || $raw === '[]';
    }
}
