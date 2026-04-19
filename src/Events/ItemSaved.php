<?php

namespace Marble\Admin\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Marble\Admin\Models\Item;

/**
 * Fired after an item's field values are saved via the admin panel.
 *
 * Usage:
 *   Event::listen(\Marble\Admin\Events\ItemSaved::class, function ($e) {
 *       // $e->item          — the Item model
 *       // $e->changedFields — ['slug.en' => ['old' => '...', 'new' => '...'], ...]
 *       // $e->userId        — admin user id who saved
 *   });
 */
class ItemSaved
{
    use Dispatchable;

    public function __construct(
        public readonly Item  $item,
        public readonly array $changedFields,
        public readonly ?int  $userId,
    ) {}
}
