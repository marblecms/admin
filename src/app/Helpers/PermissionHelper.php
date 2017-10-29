<?php

namespace Marble\Admin\App\Helpers;

use Auth;
use Config;
use Marble\Admin\App\Models\UserGroup;

class PermissionHelper
{
    private static $userGroup = false;

    private static function getCachedUserGroup()
    {
        if (!self::$userGroup) {
            $user = Auth::user();
            $group = UserGroup::find($user->groupId);
            self::$userGroup = $group;
        }

        return self::$userGroup;
    }

    public static function allowed($permission)
    {
        $userGroup = self::getCachedUserGroup();

        return (bool) self::$userGroup->$permission;
    }

    public static function entryNodeId()
    {
        $userGroup = self::getCachedUserGroup();

        $entryNodeId = $userGroup->entryNodeId;

        if ($entryNodeId === -1) {
            $entryNodeId = Config::get('app.entry_node_id');
        }

        return $entryNodeId;
    }

    public static function allowedClass($classId)
    {
        $userGroup = self::getCachedUserGroup();

        return in_array($classId, $userGroup->allowedClasses) || in_array('all', $userGroup->allowedClasses);
    }
}
