<?php

namespace Marble\Admin\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Marble\Admin\Models\Item;

/**
 * Fired when an item's status transitions to 'published'.
 *
 * Usage:
 *   Event::listen(\Marble\Admin\Events\ItemPublished::class, function ($e) {
 *       // $e->item   — the Item model
 *       // $e->userId — admin user id who published
 *   });
 */
class ItemPublished
{
    use Dispatchable;

    public function __construct(
        public readonly Item $item,
        public readonly ?int $userId,
    ) {}
}
