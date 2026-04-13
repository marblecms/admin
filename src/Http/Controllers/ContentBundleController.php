<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Marble\Admin\Models\ContentBundle;
use Marble\Admin\Models\ContentBundleItem;
use Marble\Admin\Models\Item;
use Marble\Admin\Models\Language;
use Marble\Admin\Services\ItemRevisionService;

class ContentBundleController extends Controller
{
    public function __construct(private ItemRevisionService $revisions) {}

    public function index()
    {
        $bundles = ContentBundle::with('creator')->withCount('bundleItems')->latest()->get();
        return view('marble::bundle.index', compact('bundles'));
    }

    public function create()
    {
        return view('marble::bundle.create');
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);

        $bundle = ContentBundle::create([
            'name'               => $request->input('name'),
            'description'        => $request->input('description'),
            'status'             => 'draft',
            'created_by_user_id' => Auth::guard('marble')->id(),
        ]);

        return redirect()->route('marble.bundle.show', $bundle)
            ->with('success', trans('marble::admin.bundle_saved'));
    }

    public function show(ContentBundle $bundle)
    {
        $bundle->load(['items.blueprint', 'creator']);
        return view('marble::bundle.show', compact('bundle'));
    }

    public function addItem(Request $request, ContentBundle $bundle)
    {
        $request->validate(['item_id' => 'required|exists:items,id']);
        $itemId = (int) $request->input('item_id');

        ContentBundleItem::firstOrCreate([
            'bundle_id' => $bundle->id,
            'item_id'   => $itemId,
        ]);

        return redirect()->route('marble.bundle.show', $bundle)
            ->with('success', trans('marble::admin.bundle_item_added'));
    }

    public function removeItem(ContentBundle $bundle, Item $item)
    {
        ContentBundleItem::where('bundle_id', $bundle->id)
            ->where('item_id', $item->id)
            ->delete();

        return redirect()->route('marble.bundle.show', $bundle)
            ->with('success', trans('marble::admin.bundle_item_removed'));
    }

    public function publish(ContentBundle $bundle)
    {
        if ($bundle->status === 'published') {
            return redirect()->route('marble.bundle.show', $bundle);
        }

        $languages = Language::all();

        DB::transaction(function () use ($bundle, $languages) {
            foreach ($bundle->bundleItems()->with('item.blueprint')->get() as $bi) {
                $item = $bi->item;
                if (!$item) continue;

                // Snapshot current state for rollback
                $revisionId = null;
                if ($item->blueprint?->versionable) {
                    $this->revisions->snapshot($item, $languages, Auth::guard('marble')->id());
                    $revisionId = \Marble\Admin\Models\ItemRevision::where('item_id', $item->id)
                        ->latest('id')->value('id');
                }

                $bi->update([
                    'pre_publish_status'      => $item->status,
                    'pre_publish_revision_id' => $revisionId,
                ]);

                $item->update(['status' => 'published']);
            }

            $bundle->update([
                'status'       => 'published',
                'published_at' => now(),
            ]);
        });

        return redirect()->route('marble.bundle.show', $bundle)
            ->with('success', trans('marble::admin.bundle_published'));
    }

    public function rollback(ContentBundle $bundle)
    {
        if ($bundle->status !== 'published') {
            return redirect()->route('marble.bundle.show', $bundle);
        }

        DB::transaction(function () use ($bundle) {
            foreach ($bundle->bundleItems()->with('item')->get() as $bi) {
                $item = $bi->item;
                if (!$item) continue;

                // Restore status
                $item->update(['status' => $bi->pre_publish_status ?? 'draft']);

                // Restore field values from pre-publish revision if available
                if ($bi->pre_publish_revision_id) {
                    $revision = \Marble\Admin\Models\ItemRevision::find($bi->pre_publish_revision_id);
                    if ($revision) {
                        $snapshot = $revision->values ?? [];
                        foreach ($snapshot as $fieldId => $langValues) {
                            foreach ($langValues as $langId => $value) {
                                \Marble\Admin\Models\ItemValue::updateOrCreate(
                                    ['item_id' => $item->id, 'blueprint_field_id' => $fieldId, 'language_id' => $langId],
                                    ['value' => $value]
                                );
                            }
                        }
                        $item->touch();
                    }
                }
            }

            $bundle->update(['status' => 'rolled_back']);
        });

        return redirect()->route('marble.bundle.show', $bundle)
            ->with('success', trans('marble::admin.bundle_rolled_back'));
    }

    public function destroy(ContentBundle $bundle)
    {
        $bundle->delete();
        return redirect()->route('marble.bundle.index')
            ->with('success', trans('marble::admin.bundle_deleted'));
    }
}
