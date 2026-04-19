<?php

namespace Marble\Admin\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Marble\Admin\Models\Item;

/**
 * Fired when an item is moved to the trash (soft-deleted).
 *
 * Usage:
 *   Event::listen(\Marble\Admin\Events\ItemTrashed::class, function ($e) {
 *       // $e->item   — the Item model (already soft-deleted)
 *       // $e->userId — admin user id who trashed it
 *   });
 */
class ItemTrashed
{
    use Dispatchable;

    public function __construct(
        public readonly Item $item,
        public readonly ?int $userId,
    ) {}
}
