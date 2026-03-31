<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Marble\Admin\Models\Item;
use Marble\Admin\Models\ItemValue;
use Marble\Admin\Models\Language;

class ItemLanguageCopyController extends Controller
{
    /**
     * Copy all field values from one language to another.
     */
    public function copy(Request $request, Item $item)
    {
        $request->validate([
            'from_language_id' => 'required|integer|exists:languages,id',
            'to_language_id'   => 'required|integer|exists:languages,id|different:from_language_id',
        ]);

        $fromId = (int) $request->input('from_language_id');
        $toId   = (int) $request->input('to_language_id');

        $sourceValues = ItemValue::where('item_id', $item->id)
            ->where('language_id', $fromId)
            ->get();

        foreach ($sourceValues as $source) {
            ItemValue::updateOrCreate(
                [
                    'item_id'            => $item->id,
                    'blueprint_field_id' => $source->blueprint_field_id,
                    'language_id'        => $toId,
                ],
                ['value' => $source->value]
            );
        }

        return redirect()->route('marble.item.edit', $item)
            ->with('success', trans('marble::admin.copy_language_done'));
    }
}
