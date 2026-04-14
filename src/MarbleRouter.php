<?php

namespace Marble\Admin;

use Illuminate\Support\Facades\Cookie;
use Marble\Admin\Facades\Marble;
use Marble\Admin\MarbleDebugbarContext;
use Marble\Admin\MarbleTrackingContext;
use Marble\Admin\Models\Item;
use Marble\Admin\Models\ItemMountPoint;
use Marble\Admin\Models\ItemUrlAlias;
use Marble\Admin\Models\ItemVariant;
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
                if ($root?->isPublished()) {
                    static::assignAbVariant($root);
                    static::populateTrackingContext($root, Marble::primaryLanguageId(), $site);
                    return $root;
                }
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
            foreach ($candidates as $item) {
                $itemSlug = $item->slug($languageId);
                if (!$itemSlug) {
                    continue;
                }

                if ($strippedLocalePrefix && str_starts_with($itemSlug, $strippedLocalePrefix)) {
                    $itemSlug = substr($itemSlug, strlen($strippedLocalePrefix)) ?: '/';
                }

                if ($itemSlug === $path) {
                    Marble::setLanguageById($languageId);
                    static::populateDebugbarContext($item, $languageId, $site);
                    static::assignAbVariant($item);
                    static::populateTrackingContext($item, $languageId, $site);
                    return $item;
                }
            }
        }

        // Try mount-point paths
        $mountPoints = ItemMountPoint::with(['item.blueprint', 'mountParent'])->get();

        foreach ($languageScope as $languageId) {
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

                if ($mountSlug === $path) {
                    Marble::setLanguageById($languageId);
                    static::populateDebugbarContext($mountedItem, $languageId, $site);
                    static::assignAbVariant($mountedItem);
                    static::populateTrackingContext($mountedItem, $languageId, $site);
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
            static::assignAbVariant($alias->item);
            static::populateTrackingContext($alias->item, $alias->language_id, $site);
            return $alias->item;
        }

        return null;
    }

    /**
     * If the item has an active A/B variant, assign the visitor to A or B using a cookie,
     * increment impression counters, and store the variant ID in MarbleManager.
     */
    private static function assignAbVariant(Item $item): void
    {
        $variant = $item->activeVariant();
        if (!$variant) {
            return;
        }

        $cookieName = 'marble_ab_' . $item->id;
        $bucket     = request()->cookie($cookieName);

        $isNewAssignment = !in_array($bucket, ['a', 'b'], true);
        if ($isNewAssignment) {
            // Random assignment weighted by traffic_split (% going to B)
            $bucket = (random_int(1, 100) <= $variant->traffic_split) ? 'b' : 'a';
            Cookie::queue(Cookie::make($cookieName, $bucket, 60 * 24 * 30, '/', null, false, false));
        }

        if ($bucket === 'b') {
            if ($isNewAssignment) {
                ItemVariant::where('id', $variant->id)->increment('impressions_b');
            }
            Marble::setActiveVariantId($variant->id, $item->id);
        } elseif ($isNewAssignment) {
            ItemVariant::where('id', $variant->id)->increment('impressions_a');
        }
    }

    private static function populateTrackingContext(Item $item, int $languageId, ?Site $site): void
    {
        if (!config('marble.traffic_tracking', false)) {
            return;
        }
        MarbleTrackingContext::set($item, $languageId, $site?->id);
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
    /**
     * Strip the site root item's slug from the beginning of a full slug path,
     * so the result matches what the router expects as a public URL.
     */
    public static function urlFor(Item $item, string|int|null $locale = null): string
    {
        $frontendUrl = rtrim(config('marble.frontend_url', ''), '/');
        return $frontendUrl . ($item->slug($locale) ?? '');
    }
}
