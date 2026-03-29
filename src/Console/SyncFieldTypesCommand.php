<?php

namespace Marble\Admin\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Marble\Admin\FieldTypeRegistry;
use Marble\Admin\Models\FieldType;

class SyncFieldTypesCommand extends Command
{
    protected $signature = 'marble:sync-field-types';
    protected $description = 'Sync all registered Marble field types to the database';

    public function handle(FieldTypeRegistry $registry): int
    {
        $types = $registry->all();

        if (empty($types)) {
            $this->warn('No field types registered.');
            return self::SUCCESS;
        }

        foreach ($types as $fieldType) {
            FieldType::updateOrCreate(
                ['identifier' => $fieldType->identifier()],
                ['name' => $fieldType->name(), 'class' => get_class($fieldType)]
            );

            Cache::forever("marble.fieldtype.synced.{$fieldType->identifier()}", true);

            $this->line("  <info>✓</info> {$fieldType->identifier()} ({$fieldType->name()})");
        }

        $this->info('Done. ' . count($types) . ' field type(s) synced.');

        return self::SUCCESS;
    }
}
