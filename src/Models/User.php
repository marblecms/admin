<?php

namespace Marble\Admin\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'user_group_id',
        'active',
        'last_login_at',
        'language',
        'root_item_id',
    ];

    protected $casts = [
        'active'        => 'boolean',
        'last_login_at' => 'datetime',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function userGroup(): BelongsTo
    {
        return $this->belongsTo(UserGroup::class);
    }

    /**
     * Check if user has a specific permission via their group.
     */
    public function can($ability, $arguments = []): bool
    {
        // If it's a Marble permission string like 'create_users'
        if (is_string($ability) && $this->userGroup) {
            return $this->userGroup->can($ability);
        }

        return parent::can($ability, $arguments);
    }

    public function rootItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'root_item_id');
    }

    /**
     * Get the entry item for this user (determines tree root).
     * Per-user root_item_id takes priority over the user group setting.
     */
    public function entryItem(): ?Item
    {
        if ($this->root_item_id) {
            return $this->rootItem;
        }

        return $this->userGroup?->entryItem;
    }

    /**
     * Check if user can use a specific blueprint.
     */
    public function canUseBlueprint(int $blueprintId): bool
    {
        return $this->userGroup?->canUseBlueprint($blueprintId) ?? false;
    }

    /**
     * Check a granular CRUD permission for a specific blueprint.
     * $action: 'create' | 'read' | 'update' | 'delete'
     */
    public function canDoWithBlueprint(int $blueprintId, string $action): bool
    {
        return $this->userGroup?->canDoWithBlueprint($blueprintId, $action) ?? false;
    }
}
