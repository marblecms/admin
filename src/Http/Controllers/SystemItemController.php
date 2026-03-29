<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Routing\Controller;
use Marble\Admin\Models\Blueprint;
use Marble\Admin\Models\Item;

class SystemItemController extends Controller
{
    public function index()
    {
        $blueprints = Blueprint::where('hide_system_fields', true)->orderBy('name')->get();

        // Auto-create one item per system blueprint if it doesn't exist yet
        foreach ($blueprints as $blueprint) {
            $exists = Item::where('blueprint_id', $blueprint->id)->exists();
            if (!$exists) {
                Item::create([
                    'blueprint_id' => $blueprint->id,
                    'parent_id'    => null,
                    'status'       => 'published',
                    'sort_order'   => 0,
                ]);
            }
        }

        // Reload with one item per blueprint
        $items = Item::whereIn('blueprint_id', $blueprints->pluck('id'))
            ->with('blueprint')
            ->get()
            ->keyBy('blueprint_id');

        return view('marble::system-items.index', compact('blueprints', 'items'));
    }
}
