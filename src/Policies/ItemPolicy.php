<?php

namespace Marble\Admin\Policies;

use Marble\Admin\Models\Item;
use Marble\Admin\Models\User;

class ItemPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Item $item): bool
    {
        return $user->canUseBlueprint($item->blueprint_id);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Item $item): bool
    {
        return $user->canUseBlueprint($item->blueprint_id);
    }

    public function delete(User $user, Item $item): bool
    {
        return $user->canUseBlueprint($item->blueprint_id);
    }
}
