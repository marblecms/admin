<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Marble\Admin\Http\Requests\BlueprintRequest;
use Marble\Admin\Models\Blueprint;
use Marble\Admin\Models\BlueprintField;
use Marble\Admin\Models\BlueprintGroup;
use Marble\Admin\Models\FieldType;
use Marble\Admin\Models\Workflow;

class BlueprintController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAny', Blueprint::class);
        $groups = BlueprintGroup::with('blueprints')->get();

        return view('marble::blueprint.index', [
            'groups' => $groups,
        ]);
    }

    public function add()
    {
        $this->authorize('create', Blueprint::class);
        $defaultGroup = BlueprintGroup::first() ?? BlueprintGroup::create(['name' => 'Content']);

        $blueprint = Blueprint::create([
            'name'               => 'New Blueprint',
            'identifier'         => 'new_blueprint_' . time(),
            'blueprint_group_id' => $defaultGroup->id,
        ]);

        // Default: allow all child blueprints
        DB::table('blueprint_allowed_children')->insert([
            'blueprint_id'       => $blueprint->id,
            'child_blueprint_id' => null,
            'allow_all'          => true,
        ]);

        $this->createDefaultFields($blueprint);

        return redirect()->route('marble.blueprint.edit', $blueprint);
    }

    private function createDefaultFields(Blueprint $blueprint): void
    {
        $textfieldType = FieldType::where('identifier', 'textfield')->first();
        if ($textfieldType) {
            BlueprintField::create([
                'name'          => 'Name',
                'identifier'    => 'name',
                'blueprint_id'  => $blueprint->id,
                'field_type_id' => $textfieldType->id,
                'sort_order'    => -1,
                'translatable'  => true,
                'locked'        => false,
            ]);

            BlueprintField::create([
                'name'          => 'Slug',
                'identifier'    => 'slug',
                'blueprint_id'  => $blueprint->id,
                'field_type_id' => $textfieldType->id,
                'sort_order'    => 0,
                'translatable'  => true,
                'locked'        => false,
            ]);
        }
    }

    public function edit(Blueprint $blueprint)
    {
        $this->authorize('update', $blueprint);

        $famicons = \Illuminate\Support\Facades\Cache::rememberForever('marble.famicons', function () {
            return collect(glob(public_path('vendor/marble/assets/images/famicons/*.svg')))
                ->map(fn($path) => pathinfo($path, PATHINFO_FILENAME))
                ->sort()
                ->values()
                ->toArray();
        });

        return view('marble::blueprint.edit', [
            'blueprint'       => $blueprint,
            'blueprintGroups' => BlueprintGroup::all(),
            'allBlueprints'   => Blueprint::with('group')->orderBy('name')->get()->groupBy(fn($b) => $b->group?->name ?? trans('marble::admin.no_group')),
            'famicons'        => $famicons,
            'workflows'       => Workflow::orderBy('name')->get(),
        ]);
    }

    public function save(BlueprintRequest $request, Blueprint $blueprint)
    {
        $this->authorize('update', $blueprint);
        $blueprint->name                 = $request->input('name', $blueprint->name);
        $blueprint->identifier           = $request->input('identifier', $blueprint->identifier);
        $blueprint->icon                 = $request->input('icon', '') ?: '';
        $blueprint->blueprint_group_id   = $request->input('blueprint_group_id', $blueprint->blueprint_group_id);
        $blueprint->parent_blueprint_id  = $request->input('parent_blueprint_id') ?: null;
        $blueprint->allow_children       = $request->input('allow_children', 0);
        $blueprint->list_children        = $request->input('list_children', 0);
        $blueprint->show_in_tree         = $request->input('show_in_tree', 1);
        $blueprint->locked               = $request->input('locked', 0);
        $blueprint->versionable          = $request->input('versionable', 1);
        $blueprint->schedulable          = $request->input('schedulable', 0);
        $blueprint->is_form              = $request->input('is_form', 0);
        $blueprint->form_recipients      = $request->input('form_recipients') ?: null;
        $blueprint->form_success_message  = $request->input('form_success_message') ?: null;
        $blueprint->form_success_item_id  = $request->input('form_success_item_id') ?: null;
        $blueprint->api_public            = $request->input('api_public', 0);
        $blueprint->workflow_id           = $request->input('workflow_id') ?: null;
        $blueprint->save();

        // Sync allowed child blueprints
        $allowedChildren = $request->input('allowed_child_blueprints', []);

        DB::table('blueprint_allowed_children')
            ->where('blueprint_id', $blueprint->id)
            ->delete();

        if (in_array('all', $allowedChildren)) {
            DB::table('blueprint_allowed_children')->insert([
                'blueprint_id'       => $blueprint->id,
                'child_blueprint_id' => null,
                'allow_all'          => true,
            ]);
        } else {
            $ids = array_filter($allowedChildren, fn($id) => is_numeric($id));
            $blueprint->allowedChildBlueprints()->sync($ids);
        }

        return redirect()->route('marble.blueprint.edit', $blueprint);
    }

    public function delete(Blueprint $blueprint)
    {
        $this->authorize('delete', $blueprint);
        DB::transaction(function () use ($blueprint) {
            DB::table('blueprint_allowed_children')
                ->where('blueprint_id', $blueprint->id)
                ->orWhere('child_blueprint_id', $blueprint->id)
                ->delete();
            DB::table('user_group_allowed_blueprints')
                ->where('blueprint_id', $blueprint->id)
                ->delete();

            $blueprint->fields()->delete();
            $blueprint->fieldGroups()->delete();
            $blueprint->items()->each(function ($item) {
                $item->itemValues()->delete();
                $item->delete();
            });
            $blueprint->delete();
        });

        return redirect()->route('marble.blueprint.index');
    }
}
