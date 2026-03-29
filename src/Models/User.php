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
        'language',
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

    /**
     * Get the entry item for this user (determines tree root).
     */
    public function entryItem(): ?Item
    {
        return $this->userGroup?->entryItem;
    }

    /**
     * Check if user can use a specific blueprint.
     */
    public function canUseBlueprint(int $blueprintId): bool
    {
        return $this->userGroup?->canUseBlueprint($blueprintId) ?? false;
    }
}
