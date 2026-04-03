<?php

namespace Marble\Admin;

use Marble\Admin\Facades\Marble;
use Marble\Admin\MarbleDebugbarContext;
use Marble\Admin\Models\Item;
use Marble\Admin\Models\ItemMountPoint;
use Marble\Admin\Models\ItemUrlAlias;
use Marble\Admin\Models\Language;
use Marble\Admin\Models\Site;

class MarbleRouter
{
    /**
     * Resolve a full URL path to a published Item.
     *
     * Usage:
     *   MarbleRouter::resolve('/about/team')
     *   MarbleRouter::resolve('/de/ueber-uns/team')   // with uri_locale_prefix
     */
    public static function resolve(string $path): ?Item
    {
        $path = '/' . ltrim($path, '/');

        // Strip locale prefix if configured
        $strippedLocalePrefix = null;
        if (config('marble.uri_locale_prefix', false)) {
            foreach (Language::allCached() as $lang) {
                $prefix = '/' . $lang->code;
                if (str_starts_with($path, $prefix . '/') || $path === $prefix) {
                    $strippedLocalePrefix = $prefix;
                    $path = substr($path, strlen($prefix)) ?: '/';
                    Marble::setLocale($lang->code);
                    break;
                }
            }
        }

        $segments = array_values(array_filter(explode('/', $path)));
        $leafSlug = end($segments) ?: '';

        // Empty path = homepage → return the site's root item directly
        if (!$leafSlug) {
            $site = Site::current();
            if ($site?->root_item_id) {
                $root = $site->rootItem()->with('blueprint')->first();
                return $root?->isPublished() ? $root : null;
            }
            return null;
        }

        $site = Site::current();

        // When uri_locale_prefix = true the locale prefix was already stripped
        // and the language already set — search only that language.
        // When false, slugs of all languages share the same URL space — fetch
        // candidates across all languages in one query, then match by full path.
        $languageScope = $strippedLocalePrefix
            ? [Marble::currentLanguageId()]
            : Language::allCached()->pluck('id')->all();

        // Single query: candidates whose leaf slug matches in any relevant language
        $query = Item::where('status', 'published')
            ->whereHas('itemValues', function ($q) use ($leafSlug, $languageScope) {
                $q->where('value', $leafSlug)
                  ->whereIn('language_id', $languageScope)
                  ->whereHas('blueprintField', fn ($q) => $q->where('identifier', 'slug'));
            })
            ->with('blueprint', 'parent');

        if ($site && $site->root_item_id) {
            $rootItem = $site->rootItem;
            if ($rootItem) {
                $query->where('path', 'like', $rootItem->path . '%')
                      ->orWhere('id', $rootItem->id);
            }
        }

        $candidates = $query->get();

        foreach ($languageScope as $languageId) {
            $rootSlug = '';
            if ($site?->root_item_id) {
                $rootSlug = $site->rootItem?->rawValue('slug', $languageId) ?? '';
                if ($rootSlug) {
                    $rootSlug = '/' . ltrim($rootSlug, '/');
                }
            }

            foreach ($candidates as $item) {
                $absoluteSlug = $item->slug($languageId);
                if (!$absoluteSlug) {
                    continue;
                }

                if ($strippedLocalePrefix && str_starts_with($absoluteSlug, $strippedLocalePrefix)) {
                    $absoluteSlug = substr($absoluteSlug, strlen($strippedLocalePrefix)) ?: '/';
                }

                $compareSlug = ($rootSlug && str_starts_with($absoluteSlug, $rootSlug))
                    ? substr($absoluteSlug, strlen($rootSlug))
                    : $absoluteSlug;

                if ($compareSlug === $path) {
                    Marble::setLanguageById($languageId);
                    static::populateDebugbarContext($item, $languageId, $site);
                    return $item;
                }
            }
        }

        // Try mount-point paths
        $mountPoints = ItemMountPoint::with(['item.blueprint', 'mountParent'])->get();

        foreach ($languageScope as $languageId) {
            $rootSlug = '';
            if ($site?->root_item_id) {
                $rootSlug = $site->rootItem?->rawValue('slug', $languageId) ?? '';
                if ($rootSlug) {
                    $rootSlug = '/' . ltrim($rootSlug, '/');
                }
            }

            foreach ($mountPoints as $mount) {
                $mountedItem = $mount->item;
                if (!$mountedItem || !$mountedItem->isPublished()) {
                    continue;
                }

                $mountSlug = $mountedItem->slug($languageId, $mount->mount_parent_id);
                if (!$mountSlug) {
                    continue;
                }

                if ($strippedLocalePrefix && str_starts_with($mountSlug, $strippedLocalePrefix)) {
                    $mountSlug = substr($mountSlug, strlen($strippedLocalePrefix)) ?: '/';
                }

                $compareMountSlug = ($rootSlug && str_starts_with($mountSlug, $rootSlug))
                    ? substr($mountSlug, strlen($rootSlug))
                    : $mountSlug;

                if ($compareMountSlug === $path) {
                    Marble::setLanguageById($languageId);
                    static::populateDebugbarContext($mountedItem, $languageId, $site);
                    return $mountedItem;
                }
            }
        }

        // Fall back to URL aliases
        $alias = ItemUrlAlias::where('alias', ltrim($path, '/'))
            ->whereIn('language_id', $languageScope)
            ->with('item.blueprint', 'language')
            ->first();

        if ($alias && $alias->item?->isPublished()) {
            if ($site?->root_item_id) {
                $rootItem = $site->rootItem;
                if ($rootItem && !str_starts_with($alias->item->path, $rootItem->path)) {
                    return null;
                }
            }
            Marble::setLanguageById($alias->language_id);
            static::populateDebugbarContext($alias->item, $alias->language_id, $site);
            return $alias->item;
        }

        return null;
    }

    private static function populateDebugbarContext(Item $item, int $languageId, ?Site $site): void
    {
        if (!config('marble.debugbar', false)) {
            return;
        }

        $item->loadMissing('blueprint');
        MarbleDebugbarContext::setItem($item);

        $lang = Language::find($languageId);
        if ($lang) {
            MarbleDebugbarContext::setLanguage($lang);
        }

        if ($site) {
            MarbleDebugbarContext::setSite($site);
        }
    }

    /**
     * Generate the full URL path for an item.
     *
     * Usage:
     *   MarbleRouter::urlFor($item)
     *   MarbleRouter::urlFor($item, 'de')
     */
    public static function urlFor(Item $item, string|int|null $locale = null): string
    {
        $frontendUrl = rtrim(config('marble.frontend_url', ''), '/');
        return $frontendUrl . ($item->slug($locale) ?? '');
    }
}
