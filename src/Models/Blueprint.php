<?php

namespace Marble\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Blueprint extends Model
{
    protected $fillable = [
        'name',
        'identifier',
        'icon',
        'blueprint_group_id',
        'parent_blueprint_id',
        'allow_children',
        'list_children',
        'show_in_tree',
        'locked',
        'versionable',
        'schedulable',
        'is_form',
        'form_recipients',
        'form_success_message',
        'form_success_item_id',
        'api_public',
    ];

    protected $casts = [
        'allow_children' => 'boolean',
        'list_children'  => 'boolean',
        'show_in_tree'   => 'boolean',
        'locked'         => 'boolean',
        'versionable'    => 'boolean',
        'schedulable'    => 'boolean',
        'is_form'        => 'boolean',
        'api_public'     => 'boolean',
    ];

    public function effectiveIcon(): string
    {
        if ($this->icon) {
            return $this->icon;
        }
        return $this->is_form ? 'application_form' : 'page';
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(BlueprintGroup::class, 'blueprint_group_id');
    }

    public function fields(): HasMany
    {
        return $this->hasMany(BlueprintField::class)->orderBy('sort_order');
    }

    public function fieldGroups(): HasMany
    {
        return $this->hasMany(BlueprintFieldGroup::class)->orderBy('sort_order');
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function formSuccessItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'form_success_item_id');
    }

    // -------------------------------------------------------------------------
    // Blueprint Inheritance
    // -------------------------------------------------------------------------

    public function parentBlueprint(): BelongsTo
    {
        return $this->belongsTo(Blueprint::class, 'parent_blueprint_id');
    }

    public function childBlueprints(): HasMany
    {
        return $this->hasMany(Blueprint::class, 'parent_blueprint_id');
    }

    /**
     * All fields including inherited ones from parent blueprint (prepended).
     * Inherited fields carry _inherited = true attribute.
     */
    public function allFields(): \Illuminate\Support\Collection
    {
        $own = $this->fields()->with('fieldGroup', 'fieldType')->get();

        if (!$this->parent_blueprint_id) {
            return $own;
        }

        $parent = $this->parentBlueprint;
        if (!$parent) {
            return $own;
        }

        $inherited = $parent->fields()->with('fieldGroup', 'fieldType')->get()
            ->each(fn ($f) => $f->setAttribute('_inherited', true));

        return $inherited->merge($own);
    }

    // -------------------------------------------------------------------------
    // Allowed Children
    // -------------------------------------------------------------------------

    public function allowedChildBlueprints(): BelongsToMany
    {
        return $this->belongsToMany(
            Blueprint::class,
            'blueprint_allowed_children',
            'blueprint_id',
            'child_blueprint_id'
        );
    }

    public function allowsAllChildren(): bool
    {
        return \DB::table('blueprint_allowed_children')
            ->where('blueprint_id', $this->id)
            ->where('allow_all', true)
            ->exists();
    }

    public function allowsChild(Blueprint $child): bool
    {
        if ($this->allowsAllChildren()) {
            return true;
        }

        return $this->allowedChildBlueprints()
            ->where('child_blueprint_id', $child->id)
            ->exists();
    }

    // -------------------------------------------------------------------------
    // Field grouping
    // -------------------------------------------------------------------------

    /**
     * Get all fields (including inherited) grouped by field group, sorted.
     */
    public function groupedFields(): array
    {
        $fields  = $this->allFields();
        $grouped = [];

        foreach ($fields as $field) {
            $groupKey = $field->blueprint_field_group_id ?? 0;
            $group    = $field->fieldGroup ?? null;
            $sortKey  = $group ? $group->sort_order : 9999;

            if (!isset($grouped[$sortKey])) {
                $grouped[$sortKey] = [
                    'group'  => $group,
                    'fields' => [],
                ];
            }

            $grouped[$sortKey]['fields'][] = $field;
        }

        ksort($grouped);

        return $grouped;
    }
}
