<?php

namespace Marble\Admin\Services;

use Illuminate\Support\Collection;
use Marble\Admin\Models\Item;
use Marble\Admin\Models\ItemRevision;

class ItemRevisionService
{
    public function snapshot(Item $item, Collection $languages, int $userId): void
    {
        $allValues = $item->itemValues()->get()->keyBy(fn($iv) => $iv->blueprint_field_id . '_' . $iv->language_id);
        $snapshot  = [];

        foreach ($item->blueprint->fields as $field) {
            $snapshot[$field->id] = [];
            foreach ($languages as $language) {
                $iv = $allValues->get($field->id . '_' . $language->id);
                $snapshot[$field->id][$language->id] = $iv ? $iv->value : null;
            }
        }

        ItemRevision::create([
            'item_id' => $item->id,
            'user_id' => $userId,
            'values'  => $snapshot,
        ]);

        // Keep max 20 revisions per item
        $keepIds = ItemRevision::where('item_id', $item->id)->orderByDesc('id')->limit(20)->pluck('id');
        if ($keepIds->isNotEmpty()) {
            ItemRevision::where('item_id', $item->id)->whereNotIn('id', $keepIds)->delete();
        }
    }
}
