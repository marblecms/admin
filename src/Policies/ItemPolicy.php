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
        return $user->canDoWithBlueprint($item->blueprint_id, 'read');
    }

    public function create(User $user): bool
    {
        // Blueprint-specific create check is done at the add/create controller level
        // using $user->canDoWithBlueprint($blueprintId, 'create')
        return true;
    }

    public function update(User $user, Item $item): bool
    {
        return $user->canDoWithBlueprint($item->blueprint_id, 'update');
    }

    public function delete(User $user, Item $item): bool
    {
        return $user->canDoWithBlueprint($item->blueprint_id, 'delete');
    }
}
