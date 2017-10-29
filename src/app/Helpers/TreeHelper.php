<?php

namespace Marble\Admin\App\Helpers;

use Marble\Admin\App\Models\Node;

class TreeHelper
{
    private static $tree = array();

    public static function generate($forcedEntryNodeId = null)
    {
        if ($forcedEntryNodeId !== null) {
            $entryNodeId = $forcedEntryNodeId;
        } else {
            $entryNodeId = PermissionHelper::entryNodeId();
        }

        if (isset(self::$tree[$entryNodeId])) {
            return self::$tree[$entryNodeId];
        }

        self::$tree[$entryNodeId] = self::getChildren($entryNodeId);

        return self::$tree[$entryNodeId];
    }

    private static function getChildren($parentId)
    {
        $children = array();
        $allChildren = Node::where(array('parentId' => $parentId))->get()->sortBy(function ($node) {
            return $node->sortOrder;
        });

        foreach ($allChildren as $child) {
            if (PermissionHelper::allowedClass($child->class->id) &&  $child->class->showInTree) {
                $child->children = self::getChildren($child->id);
                $children[] = $child;
            }
        }

        return $children;
    }
}
