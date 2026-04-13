<?php

namespace Marble\Admin\FieldTypes;

use Illuminate\Http\Request;
use Marble\Admin\Models\Media;
use Marble\Admin\Models\MediaFolder as MediaFolderModel;

class MediaFolder extends BaseFieldType
{
    public function identifier(): string
    {
        return 'media_folder';
    }

    public function name(): string
    {
        return 'Media Folder';
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
            'file_type' => 'all',
            'recursive' => false,
        ];
    }

    public function defaultConfig(): array
    {
        return [
            'file_type' => 'all',
            'recursive' => false,
        ];
    }

    public function configComponent(): ?string
    {
        return 'marble::field-types.media_folder-config';
    }

    /**
     * Returns an array of media items from the selected folder,
     * same structure as the `images` field type for template compatibility.
     */
    public function process(mixed $raw, int $languageId): mixed
    {
        if (!$raw || !is_array($raw) || empty($raw['folder_id'])) {
            return [];
        }

        $folderId  = (int) $raw['folder_id'];
        $fileType  = $raw['file_type'] ?? 'all';
        $recursive = !empty($raw['recursive']);

        $folderIds = [$folderId];
        if ($recursive) {
            $folderIds = array_merge($folderIds, $this->descendantFolderIds($folderId));
        }

        $query = Media::whereIn('media_folder_id', $folderIds)
            ->orderBy('original_filename');

        if ($fileType === 'image') {
            $query->where('mime_type', 'like', 'image/%');
        } elseif ($fileType === 'pdf') {
            $query->where('mime_type', 'application/pdf');
        }

        return $query->get()->map(fn($m) => [
            'id'                => $m->id,
            'url'               => $m->isImage() ? url('/image/' . $m->filename) : url('/file/' . $m->filename . '?name=' . rawurlencode($m->original_filename)),
            'thumbnail'         => $m->isImage() ? url('/image/200/150/' . $m->filename) : null,
            'filename'          => $m->filename,
            'original_filename' => $m->original_filename,
            'mime_type'         => $m->mime_type,
            'size'              => $m->size,
            'width'             => $m->width,
            'height'            => $m->height,
        ])->values()->all();
    }

    public function processInput(mixed $oldValue, mixed $newValue, Request $request, int $blueprintFieldId, int $languageId): mixed
    {
        if ($newValue === 'remove') {
            return null;
        }

        // Value comes in as "folder:{id}" from the picker button
        if (is_string($newValue) && str_starts_with($newValue, 'folder:')) {
            $folderId = (int) substr($newValue, 7);
            if (!MediaFolderModel::where('id', $folderId)->exists()) {
                return $oldValue;
            }

            $config    = $request->input("configuration.{$blueprintFieldId}", []);
            $fileType  = $config['file_type'] ?? ($oldValue['file_type'] ?? 'all');
            $recursive = isset($config['recursive']) ? (bool) $config['recursive'] : ($oldValue['recursive'] ?? false);

            return [
                'folder_id' => $folderId,
                'file_type' => $fileType,
                'recursive' => $recursive,
            ];
        }

        return $oldValue;
    }

    public function isEmpty(?string $raw): bool
    {
        return $raw === null || $raw === '' || $raw === 'null';
    }

    private function descendantFolderIds(int $folderId): array
    {
        $ids      = [];
        $children = MediaFolderModel::where('parent_id', $folderId)->pluck('id')->all();
        foreach ($children as $childId) {
            $ids[] = $childId;
            $ids   = array_merge($ids, $this->descendantFolderIds($childId));
        }
        return $ids;
    }
}
