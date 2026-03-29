<?php

namespace Marble\Admin\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Marble\Admin\Models\Item;

class TreeHelper
{
    private static array $cache = [];

    public static function generate(?int $entryItemId = null): array
    {
        $entryItemId = $entryItemId ?? static::getEntryItemId();

        if (isset(self::$cache[$entryItemId])) {
            return self::$cache[$entryItemId];
        }

        $entryItem = Item::with('blueprint')->find($entryItemId);

        if (!$entryItem) {
            return self::$cache[$entryItemId] = [];
        }

        // Load ALL descendants in one query using the materialized path
        $descendants = Item::where('path', 'like', $entryItem->path . '/%')
            ->with('blueprint')
            ->orderBy('sort_order')
            ->get();

        $user = Auth::guard('marble')->user();

        // Group by parent_id for O(1) lookup when building the tree
        $byParent = $descendants->groupBy('parent_id');

        $entryItem->tree_children = self::buildChildren($entryItemId, $byParent, $user);

        // If the root blueprint is hidden from the tree, elevate its children to top-level
        if (!$entryItem->blueprint->show_in_tree) {
            self::$cache[$entryItemId] = $entryItem->tree_children;
        } else {
            self::$cache[$entryItemId] = [$entryItem];
        }

        return self::$cache[$entryItemId];
    }

    private static function buildChildren(int $parentId, Collection $byParent, $user): array
    {
        $children = [];

        foreach ($byParent->get($parentId, collect()) as $child) {
            if (!$child->blueprint->show_in_tree) {
                continue;
            }

            if ($user && !$user->canUseBlueprint($child->blueprint_id)) {
                continue;
            }

            $child->tree_children = self::buildChildren($child->id, $byParent, $user);
            $children[] = $child;
        }

        return $children;
    }

    private static function getEntryItemId(): int
    {
        $user = Auth::guard('marble')->user();

        // Per-user root node takes highest priority
        if ($user && $user->root_item_id) {
            return $user->root_item_id;
        }

        if ($user && $user->userGroup && $user->userGroup->entry_item_id) {
            return $user->userGroup->entry_item_id;
        }

        return config('marble.entry_item_id', 1);
    }
}
