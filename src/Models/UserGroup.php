<?php

namespace Marble\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserGroup extends Model
{
    protected $table = 'user_groups';

    protected $fillable = [
        'name',
        'entry_item_id',
        'can_create_users',
        'can_edit_users',
        'can_delete_users',
        'can_list_users',
        'can_create_blueprints',
        'can_edit_blueprints',
        'can_delete_blueprints',
        'can_list_blueprints',
        'can_create_groups',
        'can_edit_groups',
        'can_delete_groups',
        'can_list_groups',
    ];

    protected $casts = [
        'can_create_users' => 'boolean',
        'can_edit_users' => 'boolean',
        'can_delete_users' => 'boolean',
        'can_list_users' => 'boolean',
        'can_create_blueprints' => 'boolean',
        'can_edit_blueprints' => 'boolean',
        'can_delete_blueprints' => 'boolean',
        'can_list_blueprints' => 'boolean',
        'can_create_groups' => 'boolean',
        'can_edit_groups' => 'boolean',
        'can_delete_groups' => 'boolean',
        'can_list_groups' => 'boolean',
    ];

    public function entryItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'entry_item_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function allowedBlueprints(): BelongsToMany
    {
        return $this->belongsToMany(
            Blueprint::class,
            'user_group_allowed_blueprints',
            'user_group_id',
            'blueprint_id'
        )->withPivot(['allow_all', 'can_create', 'can_read', 'can_update', 'can_delete']);
    }

    protected ?bool $allowsAllBlueprintsCache = null;

    /**
     * Check if this group allows all blueprints.
     */
    public function allowsAllBlueprints(): bool
    {
        if ($this->allowsAllBlueprintsCache !== null) {
            return $this->allowsAllBlueprintsCache;
        }

        return $this->allowsAllBlueprintsCache = \DB::table('user_group_allowed_blueprints')
            ->where('user_group_id', $this->id)
            ->where('allow_all', true)
            ->exists();
    }

    /**
     * Check if this group can use a specific blueprint (any access at all).
     */
    public function canUseBlueprint(int $blueprintId): bool
    {
        if ($this->allowsAllBlueprints()) {
            return true;
        }

        return $this->allowedBlueprints()->where('blueprint_id', $blueprintId)->exists();
    }

    /**
     * Check a granular CRUD permission for a specific blueprint.
     * $action: 'create' | 'read' | 'update' | 'delete'
     */
    public function canDoWithBlueprint(int $blueprintId, string $action): bool
    {
        if ($this->allowsAllBlueprints()) {
            return true;
        }

        $pivot = \DB::table('user_group_allowed_blueprints')
            ->where('user_group_id', $this->id)
            ->where('blueprint_id', $blueprintId)
            ->first();

        if (!$pivot) {
            return false;
        }

        return (bool) ($pivot->{'can_' . $action} ?? false);
    }

    /**
     * Check a permission by name.
     */
    public function can(string $permission): bool
    {
        $attribute = 'can_' . $permission;

        return (bool) ($this->$attribute ?? false);
    }
}
