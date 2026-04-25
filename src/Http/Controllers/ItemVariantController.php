<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Marble\Admin\Models\Item;
use Marble\Admin\Models\ItemVariant;
use Marble\Admin\Models\ItemVariantValue;
use Marble\Admin\Models\Language;

class ItemVariantController extends Controller
{
    private function ensureBelongsToItem(Item $item, ItemVariant $variant): void
    {
        if ($variant->item_id !== $item->id) {
            abort(404);
        }
    }

    public function create(Item $item)
    {
        $variant = ItemVariant::create([
            'item_id'       => $item->id,
            'name'          => 'Variant B',
            'traffic_split' => 50,
            'is_active'     => false,
        ]);

        return redirect()->route('marble.item.variant.edit', [$item, $variant]);
    }

    public function edit(Item $item, ItemVariant $variant)
    {
        $this->ensureBelongsToItem($item, $variant);
        $languages     = Language::all();
        $groupedFields = $item->blueprint->groupedFields();

        return view('marble::item.variant-edit', [
            'item'          => $item,
            'variant'       => $variant,
            'languages'     => $languages,
            'groupedFields' => $groupedFields,
        ]);
    }

    public function save(Request $request, Item $item, ItemVariant $variant)
    {
        $this->ensureBelongsToItem($item, $variant);
        $request->validate([
            'traffic_split' => 'required|integer|min:1|max:99',
            'name'          => 'required|string|max:128',
        ]);

        $variant->update([
            'name'          => $request->input('name'),
            'traffic_split' => $request->integer('traffic_split'),
        ]);

        $languages       = Language::all();
        $attributeValues = $request->input('fields', []);

        foreach ($item->blueprint->allFields() as $field) {
            if ($field->locked) continue;
            $fieldType = $field->fieldTypeInstance();

            foreach ($languages as $language) {
                if (!$field->translatable && $language->id !== \Marble\Admin\Facades\Marble::primaryLanguageId()) continue;

                $newValue  = $attributeValues[$field->id][$language->id] ?? null;
                $existing  = ItemVariantValue::firstOrNew([
                    'variant_id'         => $variant->id,
                    'blueprint_field_id' => $field->id,
                    'language_id'        => $language->id,
                ]);

                $oldValue  = $existing->exists ? $existing->value : null;
                $processed = $fieldType->processInput($oldValue, $newValue, $request, $field->id, $language->id);
                $existing->value = $fieldType->serialize($processed);
                $existing->save();
            }
        }

        return redirect()->route('marble.item.variant.edit', [$item, $variant])
            ->with('success', trans('marble::admin.ab_variant_saved'));
    }

    public function toggle(Item $item, ItemVariant $variant)
    {
        $this->ensureBelongsToItem($item, $variant);
        $variant->update(['is_active' => !$variant->is_active]);
        return redirect()->route('marble.item.edit', $item);
    }

    public function updateSplit(Request $request, Item $item, ItemVariant $variant)
    {
        $this->ensureBelongsToItem($item, $variant);
        $request->validate(['traffic_split' => 'required|integer|min:1|max:99']);
        $variant->update(['traffic_split' => $request->integer('traffic_split')]);
        return redirect()->route('marble.item.edit', $item);
    }

    public function destroy(Item $item, ItemVariant $variant)
    {
        $this->ensureBelongsToItem($item, $variant);
        $variant->delete();
        return redirect()->route('marble.item.edit', $item)
            ->with('success', trans('marble::admin.ab_variant_deleted'));
    }
}
