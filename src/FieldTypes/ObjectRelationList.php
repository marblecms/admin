<?php

namespace Marble\Admin\FieldTypes;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Marble\Admin\Models\Item;

class ObjectRelationList extends BaseFieldType
{
    public function identifier(): string
    {
        return 'object_relation_list';
    }

    public function name(): string
    {
        return 'Object Relation List';
    }

    public function isStructured(): bool
    {
        return true;
    }

    public function defaultValue(): mixed
    {
        return [];
    }

    public function process(mixed $raw, int $languageId): mixed
    {
        if (!is_array($raw)) {
            return [];
        }

        return array_filter(array_map(fn($id) => Item::find((int) $id), $raw));
    }

    public function registerRoutes(): void
    {
        Route::post('field-type/object-relation-list/sort', function (Request $request) {
            $itemValue = \Marble\Admin\Models\ItemValue::find($request->input('item_value_id'));

            if (!$itemValue) {
                return response()->json(['error' => 'Not found'], 404);
            }

            $nodes = json_decode($itemValue->value, true);
            $sortOrder = $request->input('sort_order', []);
            $sorted = [];

            foreach ($sortOrder as $index) {
                if (isset($nodes[$index])) {
                    $sorted[] = $nodes[$index];
                }
            }

            $itemValue->value = json_encode($sorted);
            $itemValue->save();

            return response()->json(['success' => true]);
        })->name('marble.field-type.object-relation-list.sort');
    }

    public function scripts(): array
    {
        return ['object-relation-list-edit.js'];
    }

    public function isEmpty(?string $raw): bool
    {
        return $raw === null || $raw === '' || $raw === '[]';
    }
}
