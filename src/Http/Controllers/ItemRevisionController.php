<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Marble\Admin\Facades\Marble;
use Marble\Admin\Models\Item;
use Marble\Admin\Models\ItemRevision;
use Marble\Admin\Models\ItemValue;
use Marble\Admin\Models\Language;
use Marble\Admin\Services\ItemRevisionService;

class ItemRevisionController extends Controller
{
    use AuthorizesRequests;

    private ItemRevisionService $revisions;

    public function __construct(ItemRevisionService $revisions)
    {
        $this->revisions = $revisions;
    }

    public function diff(Item $item, ItemRevision $revision)
    {
        $this->authorize('update', $item);

        $languages = Language::all();
        $fields    = $item->blueprint->fields;

        $previous = ItemRevision::where('item_id', $item->id)
            ->where('id', '<', $revision->id)
            ->orderByDesc('id')
            ->first();

        $previousValues = $previous ? $previous->values : [];

        $diff = [];
        foreach ($fields as $field) {
            foreach ($languages as $lang) {
                $newVal = $revision->values[$field->id][$lang->id] ?? null;
                $oldVal = $previousValues[$field->id][$lang->id] ?? null;
                if ($newVal === $oldVal) continue;
                $diff[] = [
                    'field'    => $field->name,
                    'language' => $lang->name,
                    'old'      => $oldVal,
                    'new'      => $newVal,
                ];
            }
        }

        $revisions  = ItemRevision::where('item_id', $item->id)->with('user')->orderByDesc('id')->limit(15)->get();
        $breadcrumb = Marble::breadcrumb($item);

        return view('marble::item.revision-diff', compact('item', 'revision', 'previous', 'diff', 'revisions', 'breadcrumb'));
    }

    public function revert(Item $item, ItemRevision $revision)
    {
        $this->authorize('update', $item);

        $languages = Language::all();

        if ($item->blueprint->versionable) {
            $this->revisions->snapshot($item, $languages, Auth::guard('marble')->id());
        }

        foreach ($revision->values as $fieldId => $langValues) {
            foreach ($langValues as $langId => $value) {
                ItemValue::where('item_id', $item->id)
                    ->where('blueprint_field_id', $fieldId)
                    ->where('language_id', $langId)
                    ->update(['value' => $value]);
            }
        }

        $item->touch();
        Marble::invalidateItem($item);

        return redirect()->route('marble.item.edit', $item);
    }

}
