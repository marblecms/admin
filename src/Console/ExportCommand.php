<?php

namespace Marble\Admin\Console;

use Illuminate\Console\Command;
use Marble\Admin\Models\Item;
use Marble\Admin\Models\ItemValue;
use Marble\Admin\Models\Language;

class ExportCommand extends Command
{
    protected $signature = 'marble:export {item : Item ID to export} {--output= : Output JSON file path}';
    protected $description = 'Export a Marble item and its descendants to a JSON file';

    public function handle(): int
    {
        $item = Item::find($this->argument('item'));

        if (!$item) {
            $this->error('Item not found.');
            return self::FAILURE;
        }

        $data = [
            'version'     => 1,
            'exported_at' => now()->toIso8601String(),
            'item'        => $this->serializeItem($item),
        ];

        $output = $this->option('output') ?? ('marble-export-' . $item->id . '-' . now()->format('Y-m-d') . '.json');
        file_put_contents($output, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->info('Exported to ' . $output);

        return self::SUCCESS;
    }

    protected function serializeItem(Item $item): array
    {
        $languages = Language::all();
        $values    = [];

        foreach ($item->blueprint->fields as $field) {
            $values[$field->identifier] = [];
            foreach ($languages as $lang) {
                $iv = $item->itemValues()
                    ->where('blueprint_field_id', $field->id)
                    ->where('language_id', $lang->id)
                    ->first();
                $values[$field->identifier][$lang->code] = $iv ? $iv->value : null;
            }
        }

        $children = [];
        foreach ($item->children()->orderBy('sort_order')->get() as $child) {
            $children[] = $this->serializeItem($child);
        }

        return [
            'blueprint'  => $item->blueprint->identifier,
            'status'     => $item->status,
            'sort_order' => $item->sort_order,
            'values'     => $values,
            'children'   => $children,
        ];
    }
}
