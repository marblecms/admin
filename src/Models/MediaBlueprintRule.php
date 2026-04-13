<?php

namespace Marble\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaBlueprintRule extends Model
{
    protected $fillable = ['mime_pattern', 'blueprint_id', 'sort_order'];

    public function blueprint(): BelongsTo
    {
        return $this->belongsTo(Blueprint::class);
    }

    /**
     * Find the best-matching blueprint for a given MIME type.
     * Exact match wins over wildcard. Returns null if no rule and no default.
     */
    public static function resolveForMime(string $mimeType): ?Blueprint
    {
        $rules = static::with('blueprint')->orderBy('sort_order')->get();

        $exact    = null;
        $wildcard = null;

        foreach ($rules as $rule) {
            $pattern = $rule->mime_pattern;

            if ($pattern === $mimeType) {
                $exact = $rule->blueprint;
                break;
            }

            if ($wildcard === null && str_ends_with($pattern, '/*')) {
                $prefix = substr($pattern, 0, -2);
                if (str_starts_with($mimeType, $prefix . '/')) {
                    $wildcard = $rule->blueprint;
                }
            }
        }

        if ($exact) return $exact;
        if ($wildcard) return $wildcard;

        // Fall back to default blueprint from settings
        $defaultId = MarbleSetting::get('media_default_blueprint_id');
        if ($defaultId) {
            return Blueprint::find((int) $defaultId);
        }

        return null;
    }
}
