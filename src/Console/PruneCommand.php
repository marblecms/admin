<?php

namespace Marble\Admin\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PruneCommand extends Command
{
    protected $signature = 'marble:prune
                            {--activity-days=90 : Delete activity log entries older than this many days}
                            {--notifications-days=30 : Delete read notifications older than this many days}
                            {--dry-run : Show counts without deleting}';

    protected $description = 'Prune old activity log entries and read notifications';

    public function handle(): int
    {
        $activityDays      = (int) $this->option('activity-days');
        $notificationsDays = (int) $this->option('notifications-days');
        $dryRun            = $this->option('dry-run');

        $activityCutoff      = now()->subDays($activityDays);
        $notificationCutoff  = now()->subDays($notificationsDays);

        $activityCount = DB::table('activity_log')
            ->where('created_at', '<', $activityCutoff)
            ->count();

        $notificationsCount = DB::table('notifications')
            ->whereNotNull('read_at')
            ->where('created_at', '<', $notificationCutoff)
            ->count();

        if ($dryRun) {
            $this->info("[dry-run] Would delete {$activityCount} activity log entries older than {$activityDays} days.");
            $this->info("[dry-run] Would delete {$notificationsCount} read notifications older than {$notificationsDays} days.");
            return self::SUCCESS;
        }

        DB::table('activity_log')
            ->where('created_at', '<', $activityCutoff)
            ->delete();

        DB::table('notifications')
            ->whereNotNull('read_at')
            ->where('created_at', '<', $notificationCutoff)
            ->delete();

        $this->info("Deleted {$activityCount} activity log entries older than {$activityDays} days.");
        $this->info("Deleted {$notificationsCount} read notifications older than {$notificationsDays} days.");

        return self::SUCCESS;
    }
}
