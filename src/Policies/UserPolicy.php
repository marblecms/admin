<?php

namespace Marble\Admin\Policies;

use Marble\Admin\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('list_users');
    }

    public function view(User $user, User $model): bool
    {
        return $user->can('list_users');
    }

    public function create(User $user): bool
    {
        return $user->can('create_users');
    }

    public function update(User $user, User $model): bool
    {
        return $user->can('edit_users');
    }

    public function delete(User $user, User $model): bool
    {
        return $user->can('delete_users');
    }
}
