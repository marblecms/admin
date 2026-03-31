<?php

namespace Marble\Admin\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Marble\Admin\Models\Item;
use Marble\Admin\Models\ItemMountPoint;

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
        $descendants = Item::where('path', 'like', $entryItem->path . '%')
            ->with('blueprint')
            ->orderBy('sort_order')
            ->get();

        $user = Auth::guard('marble')->user();

        // Group by parent_id for O(1) lookup when building the tree
        $byParent = $descendants->groupBy('parent_id');

        // Load all mount points where the mount parent is in the current tree
        $treeItemIds = $descendants->pluck('id')->toArray();

        $mountPoints = ItemMountPoint::whereIn('mount_parent_id', $treeItemIds)
            ->with('item.blueprint')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('mount_parent_id');

        // For mounted items that live outside this tree, eager-load their
        // descendants and merge them into $byParent so children show up.
        $externalItemIds = $mountPoints->flatten()
            ->pluck('item_id')
            ->unique()
            ->diff($treeItemIds)
            ->values();

        if ($externalItemIds->isNotEmpty()) {
            $externalItems = Item::whereIn('id', $externalItemIds)->with('blueprint')->get();

            foreach ($externalItems as $extItem) {
                $extDescendants = Item::where('path', 'like', $extItem->path . '%')
                    ->with('blueprint')
                    ->orderBy('sort_order')
                    ->get();

                foreach ($extDescendants as $desc) {
                    if (!$byParent->has($desc->parent_id)) {
                        $byParent[$desc->parent_id] = collect();
                    }
                    if (!$byParent[$desc->parent_id]->contains('id', $desc->id)) {
                        $byParent[$desc->parent_id]->push($desc);
                    }
                }
            }
        }

        $entryItem->tree_children = self::buildChildren(
            $entryItemId, $byParent, $user, $mountPoints, []
        );

        // If the root blueprint is hidden from the tree, elevate its children
        if (!$entryItem->blueprint->show_in_tree) {
            self::$cache[$entryItemId] = $entryItem->tree_children;
        } else {
            self::$cache[$entryItemId] = [$entryItem];
        }

        return self::$cache[$entryItemId];
    }

    private static function buildChildren(
        int $parentId,
        Collection $byParent,
        $user,
        Collection $mountPoints,
        array $visited
    ): array {
        $children = [];

        // Canonical children
        foreach ($byParent->get($parentId, collect()) as $child) {
            if (!$child->blueprint->show_in_tree) {
                continue;
            }

            if ($user && !$user->canUseBlueprint($child->blueprint_id)) {
                continue;
            }

            $child->tree_children = self::buildChildren(
                $child->id, $byParent, $user, $mountPoints, $visited
            );
            $children[] = $child;
        }

        // Mount-pointed items under this parent
        foreach ($mountPoints->get($parentId, collect()) as $mount) {
            $mountedItem = $mount->item;

            if (!$mountedItem || !$mountedItem->blueprint) {
                continue;
            }

            if (!$mountedItem->blueprint->show_in_tree) {
                continue;
            }

            if ($user && !$user->canUseBlueprint($mountedItem->blueprint_id)) {
                continue;
            }

            // Cycle guard: skip if we've already visited this item in this branch
            if (in_array($mountedItem->id, $visited, true)) {
                continue;
            }

            $node = clone $mountedItem;
            $node->_is_mount        = true;
            $node->_mount_parent_id = $parentId;
            $node->tree_children    = self::buildChildren(
                $mountedItem->id, $byParent, $user, $mountPoints,
                array_merge($visited, [$mountedItem->id])
            );

            $children[] = $node;
        }

        return $children;
    }

    private static function getEntryItemId(): int
    {
        $user = Auth::guard('marble')->user();

        if ($user && $user->root_item_id) {
            return $user->root_item_id;
        }

        if ($user && $user->userGroup && $user->userGroup->entry_item_id) {
            return $user->userGroup->entry_item_id;
        }

        return config('marble.entry_item_id', 1);
    }
}
