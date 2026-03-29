<?php

namespace Marble\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Marble\Admin\Facades\Marble;
use Marble\Admin\Traits\HasPath;

class Item extends Model
{
    use SoftDeletes, HasPath;

    protected $fillable = [
        'blueprint_id',
        'parent_id',
        'sort_order',
        'status',
        'show_in_nav',
        'published_at',
        'expires_at',
    ];

    protected $casts = [
        'sort_order'   => 'integer',
        'show_in_nav'  => 'boolean',
        'published_at' => 'datetime',
        'expires_at'   => 'datetime',
    ];

    /**
     * Cache for loaded values per language.
     */
    protected array $valueCache = [];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function blueprint(): BelongsTo
    {
        return $this->belongsTo(Blueprint::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Item::class, 'parent_id')->orderBy('sort_order');
    }

    public function itemValues(): HasMany
    {
        return $this->hasMany(ItemValue::class);
    }

    public function itemLock(): HasOne
    {
        return $this->hasOne(ItemLock::class);
    }

    // -------------------------------------------------------------------------
    // Content Locking
    // -------------------------------------------------------------------------

    public function acquireLock(int $userId): void
    {
        ItemLock::updateOrCreate(
            ['item_id' => $this->id],
            ['user_id' => $userId, 'locked_at' => now()]
        );
    }

    public function releaseLock(int $userId): void
    {
        ItemLock::where('item_id', $this->id)
            ->where('user_id', $userId)
            ->delete();
    }

    /**
     * Returns the active (non-expired) lock, or null.
     */
    public function activeLock(): ?ItemLock
    {
        $lock = $this->itemLock()->with('user')->first();
        if (!$lock) {
            return null;
        }
        if ($lock->isExpired()) {
            $lock->delete();
            return null;
        }
        return $lock;
    }

    // -------------------------------------------------------------------------
    // Reverse Relations
    // -------------------------------------------------------------------------

    /**
     * Find all published items that reference this item via ObjectRelation or ObjectRelationList fields.
     */
    public function usedBy(): \Illuminate\Support\Collection
    {
        $id = (string) $this->id;

        // ObjectRelation stores plain integer string: "5"
        $direct = ItemValue::where('value', $id)
            ->with('item.blueprint')
            ->get()
            ->pluck('item')
            ->filter();

        // ObjectRelationList stores JSON: "[3,5,12]" — match with LIKE
        $inList = ItemValue::where('value', 'like', '%' . $id . '%')
            ->where('value', 'not like', $id) // exclude plain matches already found
            ->with('item.blueprint')
            ->get()
            ->filter(function ($iv) use ($id) {
                // Verify the ID actually appears as an element, not a substring
                $decoded = json_decode($iv->value, true);
                return is_array($decoded) && in_array((int) $id, $decoded);
            })
            ->pluck('item')
            ->filter();

        return $direct->merge($inList)->unique('id')->values();
    }

    // -------------------------------------------------------------------------
    // Value API
    // -------------------------------------------------------------------------

    /**
     * Get a processed value by field identifier.
     *
     * Usage:
     *   $item->value('content')          // current locale
     *   $item->value('content', 'de')    // specific language code
     *   $item->value('content', 2)       // specific language ID
     */
    public function value(string $fieldIdentifier, string|int|null $language = null): mixed
    {
        $languageId = $this->resolveLanguageId($language);
        $raw = $this->rawValue($fieldIdentifier, $languageId);

        $field = $this->resolveField($fieldIdentifier);
        if (!$field) {
            return null;
        }

        $fieldType = $field->fieldTypeInstance();

        return $fieldType->process($raw, $languageId);
    }

    /**
     * Get the raw stored value (deserialized but not processed).
     */
    public function rawValue(string $fieldIdentifier, string|int|null $language = null): mixed
    {
        $languageId = $this->resolveLanguageId($language);

        $this->loadValuesForLanguage($languageId);

        $field = $this->resolveField($fieldIdentifier);
        if (!$field) {
            return null;
        }

        $itemValue = $this->valueCache[$languageId][$field->id] ?? null;

        if (!$itemValue) {
            return $field->fieldTypeInstance()->defaultValue();
        }

        $fieldType = $field->fieldTypeInstance();

        if ($fieldType->isStructured() && $itemValue->value !== null) {
            return $fieldType->deserialize($itemValue->value);
        }

        return $itemValue->value;
    }

    /**
     * Get all values for this item as an associative array.
     *
     * Returns: ['name' => 'Frontpage', 'content' => '<h1>...</h1>', ...]
     */
    public function values(string|int|null $language = null): array
    {
        $languageId = $this->resolveLanguageId($language);
        $result = [];

        foreach ($this->blueprint->fields as $field) {
            $result[$field->identifier] = $this->value($field->identifier, $languageId);
        }

        return $result;
    }

    /**
     * Get all raw values for all languages.
     *
     * Returns: ['name' => [1 => 'Frontpage', 2 => 'Startseite'], ...]
     */
    public function allValues(): array
    {
        $languages = Language::all();
        $result = [];

        foreach ($this->blueprint->fields as $field) {
            $result[$field->identifier] = [];
            foreach ($languages as $lang) {
                $result[$field->identifier][$lang->id] = $this->rawValue($field->identifier, $lang->id);
            }
        }

        return $result;
    }

    // -------------------------------------------------------------------------
    // Convenience accessors
    // -------------------------------------------------------------------------

    /**
     * Get item name (from the 'name' field, current locale).
     */
    public function name(string|int|null $language = null): ?string
    {
        return $this->value('name', $language);
    }

    /**
     * Get the slug for a given language, including parent slugs.
     */
    public function slug(string|int|null $language = null): ?string
    {
        $languageId = $this->resolveLanguageId($language);
        $slugValue = $this->rawValue('slug', $languageId);

        if (!$slugValue) {
            return null;
        }

        $slug = '/' . $slugValue;

        // Walk up the tree using parent relationship
        $parent = $this->parent;
        while ($parent) {
            $parentSlug = $parent->rawValue('slug', $languageId);
            if ($parentSlug) {
                $slug = '/' . $parentSlug . $slug;
            }
            $parent = $parent->parent;
        }

        // Prepend locale prefix if configured
        if (Config::get('marble.uri_locale_prefix', false)) {
            $lang = Language::find($languageId);
            if ($lang) {
                $slug = '/' . $lang->code . $slug;
            }
        }

        return $slug;
    }

    /**
     * Get all slugs for all languages.
     */
    public function slugs(): array
    {
        $slugs = [];
        foreach (Language::all() as $lang) {
            $slugs[$lang->code] = $this->slug($lang->id);
        }
        return $slugs;
    }

    /**
     * Check if item is currently published (respects scheduling and expiry).
     */
    public function isPublished(): bool
    {
        if ($this->status !== 'published') {
            return false;
        }
        if ($this->published_at && $this->published_at->isFuture()) {
            return false;
        }
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }
        return true;
    }

    // -------------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------------

    /**
     * Resolve a language code or ID to a language ID.
     */
    protected function resolveLanguageId(string|int|null $language): int
    {
        if ($language === null) {
            return Marble::currentLanguageId();
        }

        if (is_int($language)) {
            return $language;
        }

        // It's a language code like 'de', 'en'
        $lang = Language::where('code', $language)->first();

        return $lang ? $lang->id : Marble::currentLanguageId();
    }

    /**
     * Resolve a field identifier to a BlueprintField.
     */
    protected function resolveField(string $identifier): ?BlueprintField
    {
        return $this->blueprint->fields->firstWhere('identifier', $identifier);
    }

    /**
     * Eager-load all item values for a given language (single query).
     */
    protected function loadValuesForLanguage(int $languageId): void
    {
        if (isset($this->valueCache[$languageId])) {
            return;
        }

        $values = $this->itemValues()
            ->where('language_id', $languageId)
            ->get()
            ->keyBy('blueprint_field_id');

        // For non-translatable fields, also load the primary language values
        $primaryLanguageId = Marble::primaryLanguageId();

        if ($languageId !== $primaryLanguageId) {
            $primaryValues = $this->itemValues()
                ->where('language_id', $primaryLanguageId)
                ->get()
                ->keyBy('blueprint_field_id');

            // Fill in non-translatable fields from primary language
            foreach ($this->blueprint->fields as $field) {
                if (!$field->translatable && !isset($values[$field->id]) && isset($primaryValues[$field->id])) {
                    $values[$field->id] = $primaryValues[$field->id];
                }
            }
        }

        $this->valueCache[$languageId] = $values;
    }
}
