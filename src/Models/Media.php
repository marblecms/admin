<?php

namespace Marble\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    protected $fillable = [
        'filename',
        'original_filename',
        'disk',
        'mime_type',
        'size',
        'width',
        'height',
        'focal_x',
        'focal_y',
        'transformations',
        'media_folder_id',
        'blueprint_id',
    ];

    protected $casts = [
        'transformations' => 'array',
        'size'    => 'integer',
        'width'   => 'integer',
        'height'  => 'integer',
        'focal_x' => 'integer',
        'focal_y' => 'integer',
    ];

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(MediaFolder::class, 'media_folder_id');
    }

    public function blueprint(): BelongsTo
    {
        return $this->belongsTo(Blueprint::class);
    }

    public function values(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MediaValue::class);
    }

    /**
     * Load all field values for this media item for a given language,
     * keyed by field identifier. Falls back to primary language for empty values.
     */
    public function loadValuesForLanguage(int $languageId): array
    {
        if (!$this->blueprint_id) return [];

        $primaryLanguageId = \Marble\Admin\Facades\Marble::primaryLanguageId();
        $fields = $this->blueprint->allFields();

        $allValues = $this->values()
            ->whereIn('language_id', array_unique([$languageId, $primaryLanguageId]))
            ->get()
            ->groupBy('blueprint_field_id');

        $result = [];
        foreach ($fields as $field) {
            $fieldValues = $allValues->get($field->id, collect());
            $forLang     = $fieldValues->firstWhere('language_id', $languageId);
            $forPrimary  = $fieldValues->firstWhere('language_id', $primaryLanguageId);

            $raw = $forLang?->value ?? $forPrimary?->value ?? null;
            $result[$field->identifier] = $raw;
        }

        return $result;
    }

    /**
     * Get the processed value for a field identifier (runs through FieldType::process()).
     */
    public function fieldValue(string $identifier, int $languageId): mixed
    {
        if (!$this->blueprint_id) return null;

        $field = $this->blueprint->allFields()->firstWhere('identifier', $identifier);
        if (!$field) return null;

        $raw = MediaValue::where('media_id', $this->id)
            ->where('blueprint_field_id', $field->id)
            ->where('language_id', $languageId)
            ->value('value');

        $ft = $field->fieldTypeInstance();
        $decoded = $ft->isStructured() ? json_decode($raw, true) : $raw;
        return $ft->process($decoded, $languageId);
    }

    /**
     * Get the full URL to the file.
     * Pass width and/or height to get a resized version via the transform endpoint.
     *
     * Usage:
     *   $media->url()              // original
     *   $media->url(800)           // resize to 800px wide, proportional
     *   $media->url(800, 600)      // resize to 800×600 (cover crop)
     *   $media->url(0, 400)        // resize to 400px tall, proportional
     */
    public function url(int $width = 0, int $height = 0): string
    {
        if (!$width && !$height) {
            return Storage::disk($this->disk)->url($this->filename);
        }

        return route('marble.media.transform', $this->id) . '?' . http_build_query(
            array_filter(['w' => $width ?: null, 'h' => $height ?: null])
        );
    }

    /**
     * Get a focal-point-aware smart crop URL for a named preset.
     *
     * Usage:  $media->crop('hero')   // returns URL or null if preset not found
     */
    public function crop(string $presetName): ?string
    {
        $preset = CropPreset::where('name', $presetName)->first();
        if (!$preset) return null;

        return route('marble.media.transform', $this->id) . '?' . http_build_query([
            'w'    => $preset->width,
            'h'    => $preset->height,
            'crop' => 1,
            'fx'   => $this->focal_x ?? 50,
            'fy'   => $this->focal_y ?? 50,
        ]);
    }

    /**
     * Get the full path on disk.
     */
    public function path(): string
    {
        return Storage::disk($this->disk)->path($this->filename);
    }

    /**
     * Delete the file from storage when the model is deleted.
     */
    protected static function booted(): void
    {
        static::deleting(function (Media $media) {
            Storage::disk($media->disk)->delete($media->filename);
        });
    }
}
