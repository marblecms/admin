<?php

namespace Marble\Admin\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Marble\Admin\Contracts\FieldTypeInterface|null fieldType(string $identifier)
 * @method static array fieldTypes()
 * @method static void registerFieldType(\Marble\Admin\Contracts\FieldTypeInterface $fieldType)
 *
 * @method static int currentLanguageId()
 * @method static void setLanguageById(int $languageId)
 * @method static void setLocale(string $code)
 * @method static int primaryLanguageId()
 * @method static void setActiveVariantId(int $variantId, int $itemId)
 * @method static int|null activeVariantId()
 * @method static int|null activeVariantItemId()
 * @method static void recordAbConversion(\Marble\Admin\Models\Item $item)
 *
 * @method static \Marble\Admin\Models\Site|null currentSite()
 * @method static \Marble\Admin\Models\Item|null settings()
 *
 * @method static \Marble\Admin\Models\Item|null item(int $id)
 * @method static \Marble\Admin\Models\Item|null resolve(string $path)
 * @method static \Marble\Admin\Models\Item     resolveOrFail(string $path)
 * @method static \Marble\Admin\Models\Item|null findItem(string $blueprintIdentifier, string $fieldIdentifier, string $value, string|int|null $language = null)
 * @method static \Marble\Admin\ItemQuery        items(string $blueprintIdentifier)
 * @method static void                           invalidateItem(\Marble\Admin\Models\Item $item)
 *
 * @method static string url(\Marble\Admin\Models\Item $item, string|int|null $locale = null)
 * @method static string viewFor(\Marble\Admin\Models\Item $item)
 * @method static void   routes(callable $handler, string $prefix = '')
 *
 * @method static \Illuminate\Support\Collection nav(int $rootItemId, int $depth = 99)
 * @method static \Illuminate\Support\Collection breadcrumb(\Marble\Admin\Models\Item $item)
 * @method static \Illuminate\Support\Collection children(\Marble\Admin\Models\Item $item, ?string $blueprint = null, string $status = 'published')
 *
 * @see \Marble\Admin\MarbleManager
 */
class Marble extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'marble';
    }
}
