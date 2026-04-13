<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Marble\Admin\Models\FieldType;
use Marble\Admin\Models\Item;
use Marble\Admin\Models\ItemUrlAlias;
use Marble\Admin\Models\ItemValue;

class ItemRelationsController extends Controller
{
    public function show(Item $item)
    {
        return view('marble::item.graph', ['item' => $item]);
    }

    public function data(Item $item): JsonResponse
    {
        $nodes = [];
        $edges = [];
        $seen  = [];

        $objectRelationTypeId     = FieldType::where('identifier', 'object_relation')->value('id');
        $objectRelationListTypeId = FieldType::where('identifier', 'object_relation_list')->value('id');

        $addNode = function (Item $i, string $role = 'default') use (&$nodes, &$seen) {
            if (isset($seen[$i->id])) return;
            $seen[$i->id] = true;
            $nodes[] = [
                'id'     => $i->id,
                'label'  => $i->name() ?: "#{$i->id}",
                'type'   => $i->blueprint?->name ?? '—',
                'status' => $i->status,
                'url'    => route('marble.item.edit', $i),
                'role'   => $role,
            ];
        };

        // The focal item
        $item->load('blueprint');
        $addNode($item, 'focus');

        // Parent
        if ($item->parent_id) {
            $parent = Item::with('blueprint')->find($item->parent_id);
            if ($parent) {
                $addNode($parent, 'parent');
                $edges[] = ['from' => $parent->id, 'to' => $item->id, 'label' => trans('marble::admin.graph_parent')];
            }
        }

        // Direct children (limit 20)
        $children = Item::with('blueprint')->where('parent_id', $item->id)->limit(20)->get();
        foreach ($children as $child) {
            $addNode($child, 'child');
            $edges[] = ['from' => $item->id, 'to' => $child->id, 'label' => trans('marble::admin.graph_child')];
        }

        // Outgoing object_relation values of focal item
        $outgoing = ItemValue::where('item_id', $item->id)
            ->whereIn('blueprint_field_id', function ($q) use ($objectRelationTypeId) {
                $q->select('id')->from('blueprint_fields')->where('field_type_id', $objectRelationTypeId);
            })
            ->get();

        foreach ($outgoing as $iv) {
            $relId = (int) $iv->value;
            if (!$relId) continue;
            $related = Item::with('blueprint')->find($relId);
            if ($related) {
                $addNode($related, 'relation');
                $edges[] = ['from' => $item->id, 'to' => $related->id, 'label' => trans('marble::admin.graph_relation')];
            }
        }

        // Outgoing object_relation_list values
        $outgoingLists = ItemValue::where('item_id', $item->id)
            ->whereIn('blueprint_field_id', function ($q) use ($objectRelationListTypeId) {
                $q->select('id')->from('blueprint_fields')->where('field_type_id', $objectRelationListTypeId);
            })
            ->get();

        foreach ($outgoingLists as $iv) {
            $ids = json_decode($iv->value, true) ?? [];
            foreach ($ids as $relId) {
                $related = Item::with('blueprint')->find((int) $relId);
                if ($related) {
                    $addNode($related, 'relation');
                    $edges[] = ['from' => $item->id, 'to' => $related->id, 'label' => trans('marble::admin.graph_relation')];
                }
            }
        }

        // Incoming object_relation (items that reference focal item)
        $incoming = ItemValue::where('value', (string) $item->id)
            ->whereIn('blueprint_field_id', function ($q) use ($objectRelationTypeId) {
                $q->select('id')->from('blueprint_fields')->where('field_type_id', $objectRelationTypeId);
            })
            ->with('item.blueprint')
            ->limit(15)
            ->get();

        foreach ($incoming as $iv) {
            if ($iv->item) {
                $addNode($iv->item, 'relation');
                $edges[] = ['from' => $iv->item->id, 'to' => $item->id, 'label' => trans('marble::admin.graph_relation')];
            }
        }

        // Mount points
        $mounts = $item->mountPoints()->with('mountParent.blueprint')->get();
        foreach ($mounts as $mount) {
            if ($mount->mountParent) {
                $addNode($mount->mountParent, 'mount');
                $edges[] = ['from' => $item->id, 'to' => $mount->mountParent->id, 'label' => trans('marble::admin.graph_mount')];
            }
        }

        // URL aliases (shown as metadata on focal node, not separate nodes)
        $aliases = ItemUrlAlias::where('item_id', $item->id)->with('language')->get();
        $aliasLabels = $aliases->map(fn($a) => $a->alias)->join(', ');

        // Enrich focus node with aliases
        foreach ($nodes as &$node) {
            if ($node['id'] === $item->id) {
                $node['aliases'] = $aliasLabels;
                break;
            }
        }

        return response()->json(compact('nodes', 'edges'));
    }
}
