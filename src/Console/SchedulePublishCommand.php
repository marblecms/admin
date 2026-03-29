<?php

namespace Marble\Admin\Console;

use Illuminate\Console\Command;
use Marble\Admin\Models\Item;
use Marble\Admin\Services\ActivityLogService;
use Marble\Admin\Services\WebhookService;

class SchedulePublishCommand extends Command
{
    protected $signature   = 'marble:schedule-publish';
    protected $description = 'Publish scheduled items and expire items past their expiry date';

    public function handle(ActivityLogService $activityLog, WebhookService $webhooks): void
    {
        // Publish items whose published_at has passed
        $toPublish = Item::where('status', 'draft')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->get();

        foreach ($toPublish as $item) {
            $item->status = 'published';
            $item->save();
            $activityLog->log('item.published', $item, ['source' => 'scheduled']);
            $webhooks->fire('item.published', $item);
            $this->line("Published: [{$item->id}] {$item->name()}");
        }

        // Expire items whose expires_at has passed
        $toExpire = Item::where('status', 'published')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->get();

        foreach ($toExpire as $item) {
            $item->status = 'draft';
            $item->save();
            $activityLog->log('item.expired', $item, ['source' => 'scheduled']);
            $webhooks->fire('item.draft', $item);
            $this->line("Expired:   [{$item->id}] {$item->name()}");
        }

        $this->info("Done. Published: {$toPublish->count()}, Expired: {$toExpire->count()}");
    }
}
