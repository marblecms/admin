<?php

namespace Marble\Admin\Facades;

use Illuminate\Support\Facades\Facade;
use Marble\Admin\Support\MarblePluginRegistry;

/**
 * Facade for the Marble plugin registry.
 *
 * Use this in your plugin's ServiceProvider::boot() to integrate with the
 * Marble admin panel.
 *
 * @method static void  addNavItem(string $section, string $label, string $route, string $icon = 'plugin', array $activePatterns = [])
 * @method static void  addAsset(string $type, string $url)
 * @method static void  addCkEditorPlugin(string $name, string $url, array $buttons = [], string $group = 'marble')
 * @method static array getNavItems(string $section)
 * @method static array getAllNavItems()
 * @method static array getNavActivePatterns(string $section)
 * @method static array getAssets(string $type)
 * @method static array getCkEditorPlugins()
 * @method static void  addTopNavSection(string $key, array $config)
 * @method static array getTopNavSections()
 *
 * @see \Marble\Admin\Support\MarblePluginRegistry
 */
class MarbleAdmin extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'marble.admin';
    }
}
