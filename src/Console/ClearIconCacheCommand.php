<?php

namespace Marble\Admin\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearIconCacheCommand extends Command
{
    protected $signature   = 'marble:clear-icon-cache';
    protected $description = 'Clear the cached famicon list (rebuilds on next blueprint edit page load)';

    public function handle(): int
    {
        Cache::forget('marble.famicons');
        $this->info('Famicon cache cleared.');
        return self::SUCCESS;
    }
}
