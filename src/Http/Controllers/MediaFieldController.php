<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Marble\Admin\Models\Media;
use Marble\Admin\Models\MediaValue;
use Marble\Admin\Models\Language;

class MediaFieldController extends Controller
{
    public function edit(Media $media)
    {
        if (!$media->blueprint_id) {
            return redirect()->route('marble.media.index');
        }

        $languages     = Language::where('is_active', true)->get();
        $groupedFields = $media->blueprint->groupedFields();

        return view('marble::media.fields', compact('media', 'languages', 'groupedFields'));
    }

    public function save(Request $request, Media $media)
    {
        $languages     = Language::where('is_active', true)->get();
        $groupedFields = $media->blueprint->groupedFields();

        foreach ($groupedFields as $group) {
            foreach ($group['fields'] as $field) {
                if ($field->locked) continue;

                $ft = $field->fieldTypeInstance();

                foreach ($languages as $language) {
                    if (!$field->translatable && $language->id !== \Marble\Admin\Facades\Marble::primaryLanguageId()) {
                        continue;
                    }

                    $existing = MediaValue::where('media_id', $media->id)
                        ->where('blueprint_field_id', $field->id)
                        ->where('language_id', $language->id)
                        ->first();

                    $oldRaw = $existing?->value;
                    $oldValue = $ft->isStructured() ? json_decode($oldRaw, true) : $oldRaw;
                    $newValue = $request->input("fields.{$field->id}.{$language->id}");

                    $processed = $ft->processInput($oldValue, $newValue, $request, $field->id, $language->id);
                    $stored    = $ft->isStructured() ? json_encode($processed) : $processed;

                    MediaValue::updateOrCreate(
                        ['media_id' => $media->id, 'blueprint_field_id' => $field->id, 'language_id' => $language->id],
                        ['value' => $stored]
                    );
                }
            }
        }

        return redirect()->route('marble.media.fields.edit', $media)
            ->with('success', trans('marble::admin.saved'));
    }
}
