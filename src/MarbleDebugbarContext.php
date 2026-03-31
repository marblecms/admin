<?php

namespace Marble\Admin;

use Marble\Admin\Models\Item;
use Marble\Admin\Models\Language;
use Marble\Admin\Models\Site;

/**
 * Holds request-scoped context for the Marble Debugbar.
 * Populated by MarbleRouter and MarbleServiceProvider middleware hooks.
 */
class MarbleDebugbarContext
{
    private static array $data = [];

    public static function setItem(Item $item): void
    {
        self::$data['item'] = $item;
    }

    public static function setLanguage(Language $language): void
    {
        self::$data['language'] = $language;
    }

    public static function setSite(Site $site): void
    {
        self::$data['site'] = $site;
    }

    public static function get(): array
    {
        return self::$data;
    }
}
