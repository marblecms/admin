<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Marble\Admin\Models\Item;
use Marble\Admin\Models\ItemUrlAlias;
use Marble\Admin\Models\ItemValue;

class ItemAliasController extends Controller
{
    use AuthorizesRequests;

    public function save(Request $request, Item $item)
    {
        $this->authorize('update', $item);

        $errors = [];
        foreach ($request->input('aliases', []) as $row) {
            $alias = trim($row['alias'] ?? '', '/');
            $rowId = !empty($row['id']) ? (int) $row['id'] : null;
            if (!$alias) continue;

            $takenByAlias = ItemUrlAlias::where('alias', $alias)
                ->where('item_id', '!=', $item->id)
                ->when($rowId, fn($q) => $q->where('id', '!=', $rowId))
                ->exists();

            $takenBySlug = ItemValue::whereHas('blueprintField', fn($q) => $q->where('identifier', 'slug'))
                ->where('value', $alias)
                ->where('item_id', '!=', $item->id)
                ->exists();

            if ($takenByAlias || $takenBySlug) {
                $errors[] = "/{$alias}";
            }
        }

        if (!empty($errors)) {
            return redirect()->route('marble.item.edit', $item)
                ->withErrors(['aliases' => trans('marble::admin.alias_conflict', ['alias' => implode(', ', $errors)])])
                ->withInput();
        }

        $keepIds = collect($request->input('aliases', []))->pluck('id')->filter()->values();
        ItemUrlAlias::where('item_id', $item->id)
            ->when($keepIds->isNotEmpty(), fn($q) => $q->whereNotIn('id', $keepIds))
            ->delete();

        foreach ($request->input('aliases', []) as $row) {
            $alias  = trim($row['alias'] ?? '', '/');
            $langId = (int) ($row['language_id'] ?? 0);
            if (!$alias || !$langId) continue;

            if (!empty($row['id'])) {
                ItemUrlAlias::where('id', $row['id'])
                    ->where('item_id', $item->id)
                    ->update(['alias' => $alias, 'language_id' => $langId]);
            } else {
                ItemUrlAlias::firstOrCreate(
                    ['item_id' => $item->id, 'alias' => $alias, 'language_id' => $langId]
                );
            }
        }

        return redirect()->route('marble.item.edit', $item)
            ->with('success', trans('marble::admin.aliases_saved'));
    }
}
