<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Marble\Admin\Models\MediaBlueprintRule;
use Marble\Admin\Models\MarbleSetting;

class MediaBlueprintController extends Controller
{
    public function saveRules(Request $request)
    {
        $request->validate([
            'rules.*.mime_pattern' => 'required|string|max:100',
            'rules.*.blueprint_id' => 'required|integer|exists:blueprints,id',
        ]);

        MediaBlueprintRule::truncate();

        foreach ($request->input('rules', []) as $i => $rule) {
            MediaBlueprintRule::create([
                'mime_pattern' => trim($rule['mime_pattern']),
                'blueprint_id' => (int) $rule['blueprint_id'],
                'sort_order'   => $i,
            ]);
        }

        MarbleSetting::set('media_default_blueprint_id', $request->input('media_default_blueprint_id') ?: '');

        return redirect()->route('marble.configuration.index')->with('success', trans('marble::admin.configuration_saved'));
    }
}
