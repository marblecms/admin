<?php

namespace Marble\Admin\Policies;

use Marble\Admin\Models\Blueprint;
use Marble\Admin\Models\User;

class BlueprintPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('list_blueprints');
    }

    public function view(User $user, Blueprint $blueprint): bool
    {
        return $user->can('list_blueprints');
    }

    public function create(User $user): bool
    {
        return $user->can('create_blueprints');
    }

    public function update(User $user, Blueprint $blueprint): bool
    {
        return $user->can('edit_blueprints');
    }

    public function delete(User $user, Blueprint $blueprint): bool
    {
        return $user->can('delete_blueprints');
    }
}
