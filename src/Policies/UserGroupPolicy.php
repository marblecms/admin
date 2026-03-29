<?php

namespace Marble\Admin\Policies;

use Marble\Admin\Models\User;
use Marble\Admin\Models\UserGroup;

class UserGroupPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('list_groups');
    }

    public function view(User $user, UserGroup $group): bool
    {
        return $user->can('list_groups');
    }

    public function create(User $user): bool
    {
        return $user->can('create_groups');
    }

    public function update(User $user, UserGroup $group): bool
    {
        return $user->can('edit_groups');
    }

    public function delete(User $user, UserGroup $group): bool
    {
        return $user->can('delete_groups');
    }
}
