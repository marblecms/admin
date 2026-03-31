<?php

namespace Marble\Admin\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Marble\Admin\Models\Blueprint;
use Marble\Admin\Models\Item;
use Marble\Admin\Models\Site;

class DoctorCommand extends Command
{
    protected $signature   = 'marble:doctor';
    protected $description = 'Check the health of your Marble installation';

    private int $warnings = 0;
    private int $errors   = 0;

    public function handle(): int
    {
        $this->newLine();
        $this->line('<fg=blue>╔══════════════════════════════╗</>');
        $this->line('<fg=blue>║      Marble Doctor            ║</>');
        $this->line('<fg=blue>╚══════════════════════════════╝</>');
        $this->newLine();

        $this->checkDatabase();
        $this->checkSites();
        $this->checkBlueprints();
        $this->checkItems();
        $this->checkOrphanedItemValues();
        $this->checkWorkflows();
        $this->checkMountPoints();
        $this->checkFieldTypes();

        $this->newLine();
        $this->line('─────────────────────────────────────');

        if ($this->errors === 0 && $this->warnings === 0) {
            $this->line('<fg=green>✓ Everything looks healthy!</>');
        } else {
            if ($this->errors > 0) {
                $this->line("<fg=red>✗ {$this->errors} error(s) found</>");
            }
            if ($this->warnings > 0) {
                $this->line("<fg=yellow>⚠ {$this->warnings} warning(s) found</>");
            }
        }

        $this->newLine();

        return $this->errors > 0 ? self::FAILURE : self::SUCCESS;
    }

    // ─── Checks ──────────────────────────────────────────────────────────────

    private function checkDatabase(): void
    {
        $this->section('Database');

        try {
            DB::connection()->getPdo();
            $this->pass('Database connection');
        } catch (\Exception $e) {
            $this->error_row('Database connection', $e->getMessage());
            return;
        }

        // Check all expected tables exist
        $tables = [
            'blueprints', 'blueprint_fields', 'field_types', 'items', 'item_values',
            'users', 'user_groups', 'languages', 'sites', 'redirects', 'api_tokens',
            'notifications', 'workflows', 'workflow_steps', 'workflow_transitions',
            'item_mount_points', 'portal_users',
        ];

        $missing = [];
        foreach ($tables as $table) {
            if (!DB::getSchemaBuilder()->hasTable($table)) {
                $missing[] = $table;
            }
        }

        if ($missing) {
            $this->warn('Missing tables: ' . implode(', ', $missing), 'Run php artisan migrate');
        } else {
            $this->pass('All expected tables present');
        }
    }

    private function checkSites(): void
    {
        $this->section('Sites');

        $sites = Site::all();

        if ($sites->isEmpty()) {
            $this->warn('No sites configured', 'Run marble:install or create a site in the admin');
            return;
        }

        $this->pass('Sites found: ' . $sites->count());

        $default = $sites->where('is_default', true)->first();
        if (!$default) {
            $this->warn('No default site set', 'Mark one site as default in System → Sites');
        } else {
            $this->pass("Default site: {$default->name}");
        }

        foreach ($sites as $site) {
            if (!$site->root_item_id) {
                $this->warn("Site '{$site->name}' has no root item set");
                continue;
            }

            $root = Item::find($site->root_item_id);
            if (!$root) {
                $this->error_row("Site '{$site->name}' root_item_id={$site->root_item_id} does not exist");
            } elseif (!$root->isPublished()) {
                $this->warn("Site '{$site->name}' root item is not published");
            } else {
                $this->pass("Site '{$site->name}' root item OK");
            }
        }
    }

    private function checkBlueprints(): void
    {
        $this->section('Blueprints');

        $count = Blueprint::count();
        if ($count === 0) {
            $this->warn('No blueprints found');
            return;
        }
        $this->pass("Blueprints: {$count}");

        // Blueprints with no fields
        $noFields = Blueprint::doesntHave('fields')->count();
        if ($noFields > 0) {
            $this->warn("{$noFields} blueprint(s) have no fields");
        }

        // Blueprints with duplicate identifiers
        $dupes = DB::table('blueprints')
            ->select('identifier', DB::raw('COUNT(*) as cnt'))
            ->groupBy('identifier')
            ->having('cnt', '>', 1)
            ->pluck('identifier');

        if ($dupes->isNotEmpty()) {
            $this->error_row('Duplicate blueprint identifiers: ' . $dupes->implode(', '));
        } else {
            $this->pass('No duplicate blueprint identifiers');
        }

        // Parent blueprint FKs
        $badParents = Blueprint::whereNotNull('parent_blueprint_id')
            ->whereDoesntHave('parentBlueprint')
            ->count();
        if ($badParents > 0) {
            $this->error_row("{$badParents} blueprint(s) reference a non-existent parent blueprint");
        }
    }

    private function checkItems(): void
    {
        $this->section('Items');

        $total = Item::count();
        $this->pass("Total items: {$total}");

        // Items with no blueprint
        $noBp = DB::table('items')
            ->leftJoin('blueprints', 'items.blueprint_id', '=', 'blueprints.id')
            ->whereNull('blueprints.id')
            ->count();
        if ($noBp > 0) {
            $this->error_row("{$noBp} item(s) reference a non-existent blueprint");
        }

        // Items with non-existent parent
        $badParent = DB::table('items as i')
            ->leftJoin('items as p', 'i.parent_id', '=', 'p.id')
            ->whereNotNull('i.parent_id')
            ->whereNull('p.id')
            ->count();
        if ($badParent > 0) {
            $this->error_row("{$badParent} item(s) have a parent_id that does not exist");
        } else {
            $this->pass('All item parent references valid');
        }

        // Items with broken materialized paths
        $pathMismatch = 0;
        Item::whereNotNull('parent_id')->chunkById(200, function ($items) use (&$pathMismatch) {
            foreach ($items as $item) {
                $parent = Item::find($item->parent_id);
                if ($parent && !str_starts_with($item->path, $parent->path)) {
                    $pathMismatch++;
                }
            }
        });
        if ($pathMismatch > 0) {
            $this->warn("{$pathMismatch} item(s) have a materialized path mismatch");
        } else {
            $this->pass('Materialized paths consistent');
        }

        // Published items with expired expiry
        $expired = Item::where('status', 'published')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->count();
        if ($expired > 0) {
            $this->warn("{$expired} published item(s) are past their expiry date but still published", 'Run marble:schedule-publish');
        }

        // Scheduled items ready to publish
        $readyToPublish = Item::where('status', 'draft')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->count();
        if ($readyToPublish > 0) {
            $this->warn("{$readyToPublish} item(s) are scheduled to be published but still draft", 'Run marble:schedule-publish');
        }
    }

    private function checkOrphanedItemValues(): void
    {
        $this->section('Item Values');

        // ItemValues pointing to non-existent items
        $orphanedItems = DB::table('item_values')
            ->leftJoin('items', 'item_values.item_id', '=', 'items.id')
            ->whereNull('items.id')
            ->count();
        if ($orphanedItems > 0) {
            $this->error_row("{$orphanedItems} item_values row(s) reference a non-existent item");
        }

        // ItemValues pointing to non-existent blueprint fields
        $orphanedFields = DB::table('item_values')
            ->leftJoin('blueprint_fields', 'item_values.blueprint_field_id', '=', 'blueprint_fields.id')
            ->whereNull('blueprint_fields.id')
            ->count();
        if ($orphanedFields > 0) {
            $this->warn("{$orphanedFields} item_values row(s) reference a non-existent blueprint field", 'These are harmless but can be cleaned up');
        }

        // ItemValues pointing to non-existent languages
        $orphanedLangs = DB::table('item_values')
            ->leftJoin('languages', 'item_values.language_id', '=', 'languages.id')
            ->whereNull('languages.id')
            ->count();
        if ($orphanedLangs > 0) {
            $this->warn("{$orphanedLangs} item_values row(s) reference a non-existent language");
        }

        if ($orphanedItems === 0 && $orphanedFields === 0 && $orphanedLangs === 0) {
            $this->pass('No orphaned item values');
        }
    }

    private function checkWorkflows(): void
    {
        $this->section('Workflows');

        // Items with a current_workflow_step_id that no longer exists
        $badStep = DB::table('items')
            ->leftJoin('workflow_steps', 'items.current_workflow_step_id', '=', 'workflow_steps.id')
            ->whereNotNull('items.current_workflow_step_id')
            ->whereNull('workflow_steps.id')
            ->count();

        if ($badStep > 0) {
            $this->error_row("{$badStep} item(s) have a current_workflow_step_id that does not exist");
        } else {
            $this->pass('Workflow step references valid');
        }

        // Workflow steps with reject_to_step_id pointing to non-existent step
        $badReject = DB::table('workflow_steps as s')
            ->leftJoin('workflow_steps as r', 's.reject_to_step_id', '=', 'r.id')
            ->whereNotNull('s.reject_to_step_id')
            ->whereNull('r.id')
            ->count();

        if ($badReject > 0) {
            $this->warn("{$badReject} workflow step(s) have a reject_to_step_id that does not exist");
        }
    }

    private function checkMountPoints(): void
    {
        $this->section('Mount Points');

        $count = DB::table('item_mount_points')->count();
        if ($count === 0) {
            $this->info_row('No mount points configured');
            return;
        }

        $this->pass("Mount points: {$count}");

        // Mount points referencing non-existent items
        $badItem = DB::table('item_mount_points as m')
            ->leftJoin('items as i', 'm.item_id', '=', 'i.id')
            ->whereNull('i.id')
            ->count();
        if ($badItem > 0) {
            $this->error_row("{$badItem} mount point(s) reference a non-existent item");
        }

        $badParent = DB::table('item_mount_points as m')
            ->leftJoin('items as p', 'm.mount_parent_id', '=', 'p.id')
            ->whereNull('p.id')
            ->count();
        if ($badParent > 0) {
            $this->error_row("{$badParent} mount point(s) reference a non-existent parent item");
        }

        if ($badItem === 0 && $badParent === 0) {
            $this->pass('All mount point references valid');
        }
    }

    private function checkFieldTypes(): void
    {
        $this->section('Field Types');

        // Blueprint fields with no field_type_id
        $missing = DB::table('blueprint_fields')
            ->leftJoin('field_types', 'blueprint_fields.field_type_id', '=', 'field_types.id')
            ->whereNull('field_types.id')
            ->count();

        if ($missing > 0) {
            $this->warn("{$missing} blueprint field(s) have no registered field type", 'Run marble:sync-field-types');
        } else {
            $this->pass('All blueprint fields have a registered field type');
        }
    }

    // ─── Output helpers ───────────────────────────────────────────────────────

    protected function section(string $title): void
    {
        $this->newLine();
        $this->line("<fg=cyan;options=bold>  {$title}</>");
    }

    protected function pass(string $message): void
    {
        $this->line("  <fg=green>✓</> {$message}");
    }

    public function warn($message, $hint = ''): void
    {
        $this->warnings++;
        $this->line("  <fg=yellow>⚠</> {$message}");
        if ($hint) {
            $this->line("    <fg=gray>→ {$hint}</>");
        }
    }

    protected function error_row(string $message, string $hint = ''): void
    {
        $this->errors++;
        $this->line("  <fg=red>✗</> {$message}");
        if ($hint) {
            $this->line("    <fg=gray>→ {$hint}</>");
        }
    }

    protected function info_row(string $message): void
    {
        $this->line("  <fg=gray>–</> {$message}");
    }
}
