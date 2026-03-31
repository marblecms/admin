<?php

namespace Marble\Admin;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Marble\Admin\Contracts\FieldTypeInterface;
use Marble\Admin\Models\FieldType;
use Marble\Admin\Models\Item;
use Marble\Admin\Models\Language;
use Marble\Admin\Models\Site;

class MarbleManager
{
    protected FieldTypeRegistry $registry;
    protected ?int $currentLanguageId = null;
    protected ?int $primaryLanguageId = null;

    public function __construct(FieldTypeRegistry $registry)
    {
        $this->registry = $registry;
    }

    // -------------------------------------------------------------------------
    // Field Types
    // -------------------------------------------------------------------------

    public function fieldType(string $identifier): ?FieldTypeInterface
    {
        return $this->registry->get($identifier);
    }

    public function fieldTypes(): array
    {
        return $this->registry->all();
    }

    public function registerFieldType(FieldTypeInterface $fieldType): void
    {
        $this->registry->register($fieldType);

        // Auto-sync to DB so the field type appears in the Blueprint editor.
        // Cached forever so the DB is only hit once per environment (clear with cache:clear).
        $cacheKey = "marble.fieldtype.synced.{$fieldType->identifier()}";
        if (!Cache::has($cacheKey)) {
            FieldType::updateOrCreate(
                ['identifier' => $fieldType->identifier()],
                ['name' => $fieldType->name(), 'class' => get_class($fieldType)]
            );
            Cache::forever($cacheKey, true);
        }
    }

    // -------------------------------------------------------------------------
    // Language
    // -------------------------------------------------------------------------

    public function currentLanguageId(): int
    {
        if ($this->currentLanguageId !== null) {
            return $this->currentLanguageId;
        }

        $code = Config::get('marble.locale', Config::get('app.locale', 'en'));

        $this->currentLanguageId = Cache::rememberForever("marble.language_id.{$code}", function () use ($code) {
            return Language::where('code', $code)->value('id') ?? 1;
        });

        return $this->currentLanguageId;
    }

    public function setLanguageById(int $languageId): void
    {
        $this->currentLanguageId = $languageId;
    }

    public function setLocale(string $code): void
    {
        $this->currentLanguageId = Cache::rememberForever("marble.language_id.{$code}", function () use ($code) {
            return Language::where('code', $code)->value('id') ?? 1;
        });

        Config::set('marble.locale', $code);
        Config::set('app.locale', $code);
    }

    public function primaryLanguageId(): int
    {
        if ($this->primaryLanguageId !== null) {
            return $this->primaryLanguageId;
        }

        $code = Config::get('marble.primary_locale', Config::get('app.locale', 'en'));

        $this->primaryLanguageId = Cache::rememberForever("marble.language_id.{$code}", function () use ($code) {
            return Language::where('code', $code)->value('id') ?? 1;
        });

        return $this->primaryLanguageId;
    }

    // -------------------------------------------------------------------------
    // Item API (convenience, cached)
    // -------------------------------------------------------------------------

    /**
     * Find an item by ID (cached).
     */
    public function item(int $id): ?Item
    {
        return Cache::remember("marble.item.{$id}", now()->addHour(), function () use ($id) {
            return Item::with('blueprint.fields.fieldType')->find($id);
        });
    }

    /**
     * Resolve a URL slug path to a published Item.
     * e.g. Marble::resolve('/about/team')
     */
    public function resolve(string $path): ?Item
    {
        return MarbleRouter::resolve($path);
    }

    /**
     * Resolve a URL slug path to a published Item or abort with 404.
     * e.g. Marble::resolveOrFail('/about/team')
     */
    public function resolveOrFail(string $path): Item
    {
        return $this->resolve($path) ?? abort(404);
    }

    /**
     * Return the active Site matching the current request hostname.
     * e.g. Marble::currentSite()
     */
    public function currentSite(): ?Site
    {
        return Site::current();
    }

    /**
     * Returns the settings item for the current site.
     * Falls back to the default site's settings item if the current site has none.
     * e.g. Marble::settings()->value('site_name')
     */
    public function settings(): ?Item
    {
        $site = Site::current();

        if ($site?->settings_item_id) {
            return $site->settingsItem;
        }

        // Fallback: default site
        $default = Site::where('is_default', true)->with('settingsItem')->first();
        return $default?->settingsItem;
    }

    /**
     * Start a fluent item query for a given blueprint identifier.
     * e.g. Marble::items('news')->published()->paginate(10)
     */
    public function items(string $blueprintIdentifier): ItemQuery
    {
        return new ItemQuery($blueprintIdentifier);
    }

    /**
     * Register a catch-all frontend route that resolves items by slug.
     *
     * Usage in routes/web.php:
     *   Marble::routes(fn(Item $item) => view(Marble::viewFor($item), ['item' => $item]));
     *
     * With a prefix:
     *   Marble::routes(fn(Item $item) => view(Marble::viewFor($item), ['item' => $item]), '/blog');
     */
    public function routes(callable $handler, string $prefix = ''): void
    {
        $pattern = ltrim($prefix, '/');
        $pattern = $pattern ? $pattern . '/{path?}' : '{path?}';

        Route::get($pattern, function (string $path = '') use ($handler, $prefix) {
            $fullPath = ($prefix ? rtrim($prefix, '/') : '') . '/' . ltrim($path, '/');
            $item = $this->resolveOrFail($fullPath);
            return $handler($item);
        })->where('path', '^(?!image(/|$)|file(/|$)|admin(/|$)).*$');
    }

    /**
     * Generate the full frontend URL for an item.
     * e.g. Marble::url($item)  →  'https://example.com/about/team'
     */
    public function url(Item $item, string|int|null $locale = null): string
    {
        return MarbleRouter::urlFor($item, $locale);
    }

    /**
     * Get published children of an item, optionally filtered by blueprint identifier.
     * e.g. Marble::children($item, 'article')
     */
    public function children(Item $item, ?string $blueprint = null, string $status = 'published'): Collection
    {
        $query = $item->children()->where('status', $status)->with('blueprint');

        if ($blueprint) {
            $query->whereHas('blueprint', fn ($q) => $q->where('identifier', $blueprint));
        }

        return $query->get();
    }

    /**
     * Determine which view to render for a given item.
     * Looks for: resources/views/marble-pages/{blueprint_identifier}.blade.php
     * Falls back to: resources/views/marble-pages/default.blade.php
     *
     * e.g. return view(Marble::viewFor($item), compact('item'));
     */
    public function viewFor(Item $item): string
    {
        $identifier = $item->blueprint->identifier;

        if (view()->exists('marble-pages.' . $identifier)) {
            return 'marble-pages.' . $identifier;
        }

        if (view()->exists('marble-pages.default')) {
            return 'marble-pages.default';
        }

        // Package-internal fallback — always works, shows debug info
        return 'marble::frontend.default';
    }

    /**
     * Find an item by its blueprint identifier and a field value.
     * e.g. Marble::findItem('page', 'slug', 'about-us')
     */
    public function findItem(string $blueprintIdentifier, string $fieldIdentifier, string $value, string|int|null $language = null): ?Item
    {
        $languageId = $language ? $this->resolveLanguageId($language) : $this->currentLanguageId();

        return Item::whereHas('blueprint', function ($q) use ($blueprintIdentifier) {
            $q->where('identifier', $blueprintIdentifier);
        })->whereHas('itemValues', function ($q) use ($fieldIdentifier, $value, $languageId) {
            $q->where('value', $value)
                ->where('language_id', $languageId)
                ->whereHas('blueprintField', function ($q) use ($fieldIdentifier) {
                    $q->where('identifier', $fieldIdentifier);
                });
        })->first();
    }

    /**
     * Get the navigation tree starting from a root item.
     * Only returns published items with show_in_nav = true and show_in_tree = true on their blueprint.
     * If rootItemId is omitted, uses the current site's root_item_id.
     * e.g. Marble::navigation()         → from current site's root
     *      Marble::navigation(1)        → full tree from item 1
     *      Marble::navigation(1, 2)     → max 2 levels deep
     */
    public function navigation(?int $rootItemId = null, int $depth = 99): Collection
    {
        if ($rootItemId === null) {
            $rootItemId = $this->currentSite()?->root_item_id
                ?? config('marble.entry_item_id', 1);
        }

        return $this->buildNavTree($rootItemId, $depth, 1);
    }

    protected function buildNavTree(int $parentId, int $maxDepth, int $currentDepth): Collection
    {
        if ($currentDepth > $maxDepth) {
            return collect();
        }

        $children = Item::where('parent_id', $parentId)
            ->where('status', 'published')
            ->where('show_in_nav', true)
            ->whereHas('blueprint', fn($q) => $q->where('show_in_tree', true))
            ->with('blueprint')
            ->orderBy('sort_order')
            ->get();

        foreach ($children as $child) {
            $child->setRelation('navChildren', $this->buildNavTree($child->id, $maxDepth, $currentDepth + 1));
        }

        return $children;
    }

    /**
     * Get the breadcrumb trail for an item (from root to the item itself).
     * e.g. Marble::breadcrumb($item)  →  [Item(root), Item(about), Item(team)]
     */
    public function breadcrumb(Item $item): Collection
    {
        if (!$item->path) {
            return collect([$item]);
        }

        // path looks like /1/5/8/ — extract ancestor IDs
        $ids = array_filter(explode('/', trim($item->path, '/')));

        if (empty($ids)) {
            return collect([$item]);
        }

        $ancestors = Item::whereIn('id', $ids)
            ->with('blueprint')
            ->get()
            ->keyBy('id');

        $trail = collect();
        foreach ($ids as $id) {
            if ($ancestors->has($id)) {
                $trail->push($ancestors->get($id));
            }
        }

        // Only append the item itself if it's not already the last entry (path may include its own ID)
        if ($trail->last()?->id !== $item->id) {
            $trail->push($item);
        }

        return $trail;
    }

    /**
     * Invalidate cache for an item and its ancestors.
     */
    public function invalidateItem(Item $item): void
    {
        Cache::forget("marble.item.{$item->id}");

        foreach ($item->ancestorIds() as $ancestorId) {
            Cache::forget("marble.item.{$ancestorId}");
        }
    }

    // -------------------------------------------------------------------------
    // Internal
    // -------------------------------------------------------------------------

    /**
     * Get the currently authenticated portal user (frontend auth).
     * Returns null if no portal user is logged in.
     */
    public function portalUser(): ?\Marble\Admin\Models\PortalUser
    {
        return \Illuminate\Support\Facades\Auth::guard('portal')->user();
    }

    /**
     * Check if a portal user is currently authenticated.
     */
    public function isPortalAuthenticated(): bool
    {
        return \Illuminate\Support\Facades\Auth::guard('portal')->check();
    }

    protected function resolveLanguageId(string|int $language): int
    {
        if (is_int($language)) {
            return $language;
        }

        $lang = Language::where('code', $language)->first();
        return $lang?->id ?? $this->currentLanguageId();
    }
}
