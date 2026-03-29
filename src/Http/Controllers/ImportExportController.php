<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Marble\Admin\Models\Blueprint;
use Marble\Admin\Models\Item;
use Marble\Admin\Models\ItemValue;
use Marble\Admin\Models\Language;
use Marble\Admin\Facades\Marble;

class ImportExportController extends Controller
{
    use AuthorizesRequests;

    public function export(Item $item)
    {
        $this->authorize('update', $item);

        $data = [
            'version'     => 1,
            'exported_at' => now()->toIso8601String(),
            'item'        => $this->serializeItem($item),
        ];

        $filename = 'marble-export-' . $item->id . '-' . now()->format('Y-m-d') . '.json';

        return response()->json($data, 200, [
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function importForm()
    {
        $items = Item::with('blueprint')->orderBy('path')->get();
        return view('marble::item.import', compact('items'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file'      => 'required|file|mimes:json',
            'parent_id' => 'required|exists:items,id',
        ]);

        $json    = json_decode(file_get_contents($request->file('file')->getRealPath()), true);
        $parent  = Item::findOrFail($request->input('parent_id'));

        $this->importItem($json['item'], $parent->id);

        return redirect()->route('marble.item.edit', $parent->id)
            ->with('success', trans('marble::admin.import_success'));
    }

    // -------------------------------------------------------------------------

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

    protected function importItem(array $data, int $parentId): Item
    {
        $languages = Language::all()->keyBy('code');
        $blueprint = Blueprint::where('identifier', $data['blueprint'])->firstOrFail();

        $item = Item::create([
            'blueprint_id' => $blueprint->id,
            'parent_id'    => $parentId,
            'status'       => $data['status'] ?? 'draft',
            'sort_order'   => $data['sort_order'] ?? 0,
        ]);

        foreach ($blueprint->fields as $field) {
            $fieldValues = $data['values'][$field->identifier] ?? [];
            $fieldType   = $field->fieldTypeInstance();

            foreach ($languages as $code => $lang) {
                ItemValue::create([
                    'item_id'            => $item->id,
                    'blueprint_field_id' => $field->id,
                    'language_id'        => $lang->id,
                    'value'              => $fieldValues[$code] ?? $fieldType->serialize($fieldType->defaultValue()),
                ]);
            }
        }

        foreach ($data['children'] ?? [] as $childData) {
            $this->importItem($childData, $item->id);
        }

        return $item;
    }
}
