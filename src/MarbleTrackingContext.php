<?php

namespace Marble\Admin;

use Marble\Admin\Models\Item;
use Marble\Admin\Models\Language;
use Marble\Admin\Models\Site;

/**
 * Holds request-scoped context for traffic tracking.
 * Populated by MarbleRouter so that the tracking middleware can record pageviews.
 */
class MarbleTrackingContext
{
    private static ?Item $item = null;
    private static ?int $languageId = null;
    private static ?int $siteId = null;

    public static function set(Item $item, int $languageId, ?int $siteId): void
    {
        self::$item       = $item;
        self::$languageId = $languageId;
        self::$siteId     = $siteId;
    }

    public static function getItem(): ?Item
    {
        return self::$item;
    }

    public static function getLanguageId(): ?int
    {
        return self::$languageId;
    }

    public static function getSiteId(): ?int
    {
        return self::$siteId;
    }

    public static function clear(): void
    {
        self::$item       = null;
        self::$languageId = null;
        self::$siteId     = null;
    }
}
