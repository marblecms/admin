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
        'focal_x',
        'focal_y',
        'transformations',
        'media_folder_id',
    ];

    protected $casts = [
        'transformations' => 'array',
        'size'    => 'integer',
        'focal_x' => 'integer',
        'focal_y' => 'integer',
    ];

    public function folder(): BelongsTo
    {
        return $this->belongsTo(MediaFolder::class, 'media_folder_id');
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
