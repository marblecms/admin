<?php

namespace Marble\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItemVariant extends Model
{
    protected $fillable = ['item_id', 'name', 'traffic_split', 'is_active', 'impressions_a', 'impressions_b', 'conversions_a', 'conversions_b'];

    protected $casts = [
        'is_active'     => 'boolean',
        'traffic_split' => 'integer',
        'impressions_a' => 'integer',
        'impressions_b' => 'integer',
        'conversions_a' => 'integer',
        'conversions_b' => 'integer',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(ItemVariantValue::class, 'variant_id');
    }

    /**
     * Load all field values for this variant for a given language.
     * Fields not set in the variant fall back to the main item's values.
     */
    public function loadValuesForLanguage(int $languageId): array
    {
        $item   = $this->item()->with('blueprint')->first();
        $fields = $item->blueprint->allFields();

        $variantValues = $this->values()
            ->where('language_id', $languageId)
            ->get()
            ->keyBy('blueprint_field_id');

        $baseValues = ItemValue::where('item_id', $item->id)
            ->where('language_id', $languageId)
            ->get()
            ->keyBy('blueprint_field_id');

        $result = [];
        foreach ($fields as $field) {
            $variantVal = $variantValues->get($field->id);
            $baseVal    = $baseValues->get($field->id);
            // Use variant value if explicitly set and non-empty, else fall back to base
            $raw = ($variantVal && $variantVal->value !== null && $variantVal->value !== '')
                ? $variantVal->value
                : ($baseVal?->value ?? null);

            $result[$field->identifier] = $raw;
        }

        return $result;
    }

    public function winnerLabel(): string
    {
        $total = $this->impressions_a + $this->impressions_b;
        if (!$total) return '—';
        $pctA = round($this->impressions_a / $total * 100);
        $pctB = round($this->impressions_b / $total * 100);
        return "A {$pctA}% / B {$pctB}%";
    }
}
