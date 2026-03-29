<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Marble\Admin\Models\Blueprint;
use Marble\Admin\Models\BlueprintField;
use Marble\Admin\Models\BlueprintFieldGroup;
use Marble\Admin\Models\FieldType;

class BlueprintFieldController extends Controller
{
    public function edit(Blueprint $blueprint)
    {
        return view('marble::blueprint.fields', [
            'blueprint' => $blueprint,
            'fields' => $blueprint->fields()->with('fieldType', 'fieldGroup')->get(),
            'groupedFields' => $blueprint->groupedFields(),
            'fieldGroups' => $blueprint->fieldGroups,
            'fieldTypes' => FieldType::all(),
        ]);
    }

    public function add(Request $request, Blueprint $blueprint)
    {
        $fieldType = FieldType::findOrFail($request->input('type'));

        BlueprintField::create([
            'name' => 'New Field',
            'identifier' => 'new_field_' . time(),
            'blueprint_id' => $blueprint->id,
            'field_type_id' => $fieldType->id,
            'sort_order' => $blueprint->fields()->max('sort_order') + 1,
            'translatable' => false,
            'locked' => false,
        ]);

        return redirect()->route('marble.blueprint.field.edit', $blueprint);
    }

    public function delete(Blueprint $blueprint, BlueprintField $field)
    {
        $field->delete();

        return redirect()->route('marble.blueprint.field.edit', $blueprint);
    }

    public function save(Request $request, Blueprint $blueprint)
    {
        $names = $request->input('name', []);
        $identifiers = $request->input('identifier', []);
        $translatable = $request->input('translatable', []);
        $locked = $request->input('locked', []);
        $groupIds = $request->input('group_id', []);
        $sortOrders = $request->input('sort_order', []);
        $configurations = $request->input('configuration', []);
        $validationRules = $request->input('validation_rules', []);

        foreach ($names as $fieldId => $name) {
            $field = BlueprintField::find($fieldId);
            if (!$field || $field->blueprint_id !== $blueprint->id) continue;

            $field->name = $name;
            $field->identifier = $identifiers[$fieldId] ?? $field->identifier;
            $field->translatable = isset($translatable[$fieldId]);
            $field->locked = isset($locked[$fieldId]);
            $field->blueprint_field_group_id = ($groupIds[$fieldId] ?? 0) ?: null;
            $field->sort_order = $sortOrders[$fieldId] ?? 0;
            $field->validation_rules = ($validationRules[$fieldId] ?? '') ?: null;

            if (isset($configurations[$fieldId])) {
                $field->configuration = $configurations[$fieldId];
            }

            $field->save();
        }

        return redirect()->route('marble.blueprint.field.edit', $blueprint);
    }
}
