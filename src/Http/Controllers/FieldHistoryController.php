<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Marble\Admin\Models\BlueprintField;
use Marble\Admin\Models\Item;
use Marble\Admin\Models\ItemRevision;
use Marble\Admin\Models\ItemValue;
use Marble\Admin\Models\Language;

class FieldHistoryController extends Controller
{
    /**
     * Return the history of a single field across all saved revisions.
     */
    public function history(Request $request, Item $item, BlueprintField $field): JsonResponse
    {
        $languageId = (int) $request->query('language_id', Language::first()?->id);

        $revisions = ItemRevision::where('item_id', $item->id)
            ->with('user')
            ->orderByDesc('id')
            ->get();

        $history = [];
        $lastValue = null;

        // Add current live value as first entry
        $liveValue = ItemValue::where('item_id', $item->id)
            ->where('blueprint_field_id', $field->id)
            ->where('language_id', $languageId)
            ->value('value');

        $history[] = [
            'revision_id' => null,
            'label'       => trans('marble::admin.field_history_current'),
            'created_at'  => $item->updated_at?->toDateTimeString(),
            'user'        => null,
            'value'       => $liveValue,
        ];
        $lastValue = $liveValue;

        foreach ($revisions as $rev) {
            $snapshot  = $rev->values ?? [];
            $value     = $snapshot[$field->id][$languageId] ?? null;

            // Only include entries where the value actually changed
            if ($value === $lastValue) continue;

            $history[] = [
                'revision_id' => $rev->id,
                'label'       => $rev->created_at->format('d.m.Y H:i'),
                'created_at'  => $rev->created_at->toDateTimeString(),
                'user'        => $rev->user?->name,
                'value'       => $value,
            ];
            $lastValue = $value;
        }

        return response()->json([
            'field'     => ['id' => $field->id, 'name' => $field->name, 'type' => $field->fieldType?->identifier],
            'language'  => $languageId,
            'history'   => $history,
        ]);
    }

    /**
     * Restore a specific field to the value from a given revision.
     */
    public function restore(Request $request, Item $item, BlueprintField $field): JsonResponse
    {
        $request->validate([
            'language_id' => 'required|integer',
            'value'       => 'nullable|string',
        ]);

        $languageId = (int) $request->input('language_id');
        $value      = $request->input('value');

        ItemValue::updateOrCreate(
            [
                'item_id'            => $item->id,
                'blueprint_field_id' => $field->id,
                'language_id'        => $languageId,
            ],
            ['value' => $value]
        );

        $item->touch();

        return response()->json(['ok' => true, 'message' => trans('marble::admin.field_history_restored')]);
    }
}
