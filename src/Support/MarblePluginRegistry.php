<?php

namespace Marble\Admin\Support;

/**
 * Central registry for Marble plugin integrations.
 *
 * Plugins call these methods from their ServiceProvider::boot() to hook into
 * the Marble admin panel without modifying core files.
 *
 * Usage (in your plugin's ServiceProvider::boot()):
 *
 *   use Marble\Admin\Facades\MarbleAdmin;
 *
 *   MarbleAdmin::addNavItem('content', 'Orders', 'marble.shop.orders', 'bag', ['marble.shop.*']);
 *   MarbleAdmin::addAsset('css', asset('vendor/my-plugin/plugin.css'));
 *   MarbleAdmin::addAsset('js',  asset('vendor/my-plugin/plugin.js'));
 *   MarbleAdmin::addCkEditorPlugin('myplugin', asset('vendor/my-plugin/ckeditor/'), ['MyButton']);
 */
class MarblePluginRegistry
{
    /** @var array<string, list<array{label:string, route:string, icon:string, patterns:list<string>}>> */
    protected array $navItems = [];

    /** @var array{css: list<string>, js: list<string>} */
    protected array $assets = ['css' => [], 'js' => []];

    /** @var list<array{name:string, url:string, buttons:list<string>, group:string}> */
    protected array $ckEditorPlugins = [];

    /**
     * Additional top-level nav dropdown sections (e.g. "Shop").
     * @var list<array{key:string, label:string, icon:string, patterns:list<string>, items:list<array{label:string,route:string,icon:string}>}>
     */
    protected array $topNavSections = [];

    /**
     * Add an item to one of the admin top-nav dropdowns.
     *
     * @param string        $section         'content' | 'structure' | 'users' | 'system'
     * @param string        $label           Display label
     * @param string        $route           Named Laravel route
     * @param string        $icon            Famicon name (without .svg)
     * @param list<string>  $activePatterns  Route name patterns that mark this section active.
     *                                       Defaults to ['{route}*'].
     */
    public function addNavItem(
        string $section,
        string $label,
        string $route,
        string $icon = 'plugin',
        array  $activePatterns = [],
    ): void {
        $this->navItems[$section][] = [
            'label'    => $label,
            'route'    => $route,
            'icon'     => $icon,
            'patterns' => $activePatterns ?: [$route . '*'],
        ];
    }

    /**
     * Inject a CSS or JS asset into every admin page.
     *
     * @param string $type 'css' | 'js'
     * @param string $url  Fully-qualified public URL (use asset() helper)
     */
    public function addAsset(string $type, string $url): void
    {
        if (!isset($this->assets[$type])) {
            $this->assets[$type] = [];
        }
        $this->assets[$type][] = $url;
    }

    /**
     * Register a CKEditor 4 external plugin and (optionally) add its toolbar buttons
     * to the 'marble' toolbar group.
     *
     * @param string       $name    Plugin identifier (matches CKEDITOR.plugins.add name)
     * @param string       $url     URL to the directory containing plugin.js (trailing slash)
     * @param list<string> $buttons Button names to add to the marble toolbar group
     * @param string       $group   Toolbar group to append buttons to (default 'marble')
     */
    public function addCkEditorPlugin(
        string $name,
        string $url,
        array  $buttons = [],
        string $group = 'marble',
    ): void {
        $this->ckEditorPlugins[] = [
            'name'    => $name,
            'url'     => $url,
            'buttons' => $buttons,
            'group'   => $group,
        ];
    }

    // ── Getters ──────────────────────────────────────────────────────────────

    public function getNavItems(string $section): array
    {
        return $this->navItems[$section] ?? [];
    }

    public function getAllNavItems(): array
    {
        return $this->navItems;
    }

    /** All route patterns from plugin nav items for a given section. */
    public function getNavActivePatterns(string $section): array
    {
        return collect($this->navItems[$section] ?? [])
            ->pluck('patterns')
            ->flatten()
            ->all();
    }

    public function getAssets(string $type): array
    {
        return $this->assets[$type] ?? [];
    }

    public function getCkEditorPlugins(): array
    {
        return $this->ckEditorPlugins;
    }

    /**
     * Register a new top-level nav dropdown (e.g. "Shop").
     *
     * @param string $key    Unique identifier for this section
     * @param array  $config {
     *   label:    string,
     *   icon:     string (famicon name),
     *   patterns: string[]  (route name patterns that mark this section active),
     *   items:    array[]   each: {label, route, icon}
     * }
     */
    public function addTopNavSection(string $key, array $config): void
    {
        $this->topNavSections[] = array_merge(['key' => $key], $config);
    }

    public function getTopNavSections(): array
    {
        return $this->topNavSections;
    }
}
