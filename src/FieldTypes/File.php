<?php

namespace Marble\Admin\FieldTypes;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Marble\Admin\Models\BlueprintField;

class File extends BaseFieldType
{
    public function identifier(): string
    {
        return 'file';
    }

    public function name(): string
    {
        return 'File';
    }

    public function isStructured(): bool
    {
        return true;
    }

    public function defaultValue(): mixed
    {
        return null;
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
        return 'marble::field-types.file-config';
    }

    /**
     * Returns a public download URL for the stored file.
     */
    public function process(mixed $raw, int $languageId): mixed
    {
        if (!$raw || !is_array($raw) || empty($raw['filename'])) {
            return null;
        }

        return [
            'url'               => url('/file/' . $raw['filename'] . '?name=' . rawurlencode($raw['original_filename'] ?? $raw['filename'])),
            'original_filename' => $raw['original_filename'] ?? $raw['filename'],
            'mime_type'         => $raw['mime_type'] ?? '',
            'size'              => $raw['size'] ?? 0,
        ];
    }

    public function processInput(mixed $oldValue, mixed $newValue, Request $request, int $blueprintFieldId, int $languageId): mixed
    {
        if ($newValue === 'remove') {
            if ($oldValue && isset($oldValue['filename'])) {
                Storage::delete($oldValue['filename']);
            }
            return null;
        }

        $fileKey = "file_{$blueprintFieldId}_{$languageId}";

        if (!$request->hasFile($fileKey)) {
            return $oldValue;
        }

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

        // Delete old file
        if ($oldValue && isset($oldValue['filename'])) {
            Storage::delete($oldValue['filename']);
        }

        return [
            'filename'          => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type'         => $file->getMimeType(),
            'size'              => $file->getSize(),
        ];
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
}
