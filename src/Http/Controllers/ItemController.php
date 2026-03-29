<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Marble\Admin\Facades\Marble;
use Marble\Admin\Http\Requests\ItemCreateRequest;
use Marble\Admin\Http\Requests\ItemUpdateRequest;
use Marble\Admin\Http\Requests\SearchRequest;
use Marble\Admin\Models\Blueprint;
use Marble\Admin\Models\Item;
use Marble\Admin\Models\ItemRevision;
use Marble\Admin\Models\ItemValue;
use Marble\Admin\Models\FormSubmission;
use Marble\Admin\Models\Language;
use Marble\Admin\Services\ActivityLogService;
use Marble\Admin\Services\WebhookService;

class ItemController extends Controller
{
    use AuthorizesRequests;

    public function edit(Item $item)
    {
        $this->authorize('update', $item);
        $languages = Language::all();
        $groupedFields = $item->blueprint->groupedFields();

        $childItems = null;
        if ($item->blueprint->list_children) {
            $childItems = $item->children()->orderBy('sort_order')->get();
        }

        $revisions = ItemRevision::where('item_id', $item->id)
            ->with('user')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        $currentUser = Auth::guard('marble')->user();

        // Acquire lock for current user, get any conflicting lock
        $item->acquireLock($currentUser->id);
        $activeLock = $item->activeLock();
        $lockedByOther = $activeLock && $activeLock->user_id !== $currentUser->id;

        $submissions = $item->blueprint->is_form
            ? FormSubmission::where('item_id', $item->id)->orderByDesc('created_at')->paginate(30)
            : null;

        return view('marble::item.edit', [
            'item'          => $item,
            'languages'     => $languages,
            'groupedFields' => $groupedFields,
            'childItems'    => $childItems,
            'currentUser'   => $currentUser,
            'revisions'     => $revisions,
            'lockedByOther' => $lockedByOther,
            'lockUser'      => $lockedByOther ? $activeLock->user : null,
            'usedBy'        => $item->usedBy(),
            'submissions'   => $submissions,
            'breadcrumb'    => \Marble\Admin\Facades\Marble::breadcrumb($item),
        ]);
    }

    public function save(ItemUpdateRequest $request, Item $item)
    {
        $this->authorize('update', $item);
        $languages = Language::all();
        $attributeValues = $request->input('fields', []);

        // Field-level validation
        $validationRules = [];
        foreach ($item->blueprint->allFields() as $field) {
            if ($field->locked || !$field->validation_rules) continue;
            foreach ($languages as $language) {
                if (!$field->translatable && $language->id !== Marble::primaryLanguageId()) continue;
                $validationRules["fields.{$field->id}.{$language->id}"] = $field->validation_rules;
            }
        }
        if (!empty($validationRules)) {
            $request->validate($validationRules);
        }

        // Snapshot current values before overwriting (only if blueprint has versioning enabled)
        if ($item->blueprint->versionable) {
            $this->snapshotRevision($item, $languages);
        }

        foreach ($item->blueprint->fields as $field) {
            if ($field->locked) {
                continue;
            }

            $fieldType = $field->fieldTypeInstance();

            foreach ($languages as $language) {
                if (!$field->translatable && $language->id !== Marble::primaryLanguageId()) {
                    continue;
                }

                $newValue = $attributeValues[$field->id][$language->id] ?? null;

                $itemValue = ItemValue::firstOrNew([
                    'item_id'            => $item->id,
                    'blueprint_field_id' => $field->id,
                    'language_id'        => $language->id,
                ]);

                $oldValue = $itemValue->exists ? $itemValue->raw() : $fieldType->defaultValue();
                $processed = $fieldType->processInput($oldValue, $newValue, $request, $field->id, $language->id);

                $itemValue->value = $fieldType->serialize($processed);
                $itemValue->save();
            }
        }

        // Scheduling
        $item->published_at = $request->input('published_at') ?: null;
        $item->expires_at   = $request->input('expires_at') ?: null;
        $item->touch();

        app(ActivityLogService::class)->log('item.saved', $item);
        app(WebhookService::class)->fire('item.saved', $item);

        return redirect()->route('marble.item.edit', $item);
    }

    public function add(Item $parentItem)

    {
        $this->authorize('create', Item::class);
        $user = Auth::guard('marble')->user();

        $allowedBlueprints = $parentItem->blueprint->allowsAllChildren()
            ? Blueprint::all()
            : $parentItem->blueprint->allowedChildBlueprints;

        $allowedBlueprints = $allowedBlueprints->filter(fn($bp) => $user->canUseBlueprint($bp->id));

        return view('marble::item.add', [
            'parentItem'       => $parentItem,
            'allowedBlueprints' => $allowedBlueprints,
        ]);
    }

    public function create(ItemCreateRequest $request)
    {
        $this->authorize('create', Item::class);
        $parentId   = $request->input('parent_id');
        $blueprintId = $request->input('blueprint_id');
        $name        = $request->input('name', '');

        $blueprint = Blueprint::findOrFail($blueprintId);
        $languages = Language::all();

        $item = Item::create([
            'blueprint_id' => $blueprintId,
            'parent_id'    => $parentId,
            'status'       => 'published',
        ]);

        foreach ($blueprint->fields as $field) {
            $fieldType = $field->fieldTypeInstance();

            foreach ($languages as $language) {
                $value = $field->identifier === 'name' ? $name : $fieldType->defaultValue();

                ItemValue::create([
                    'item_id'            => $item->id,
                    'blueprint_field_id' => $field->id,
                    'language_id'        => $language->id,
                    'value'              => $fieldType->serialize($value),
                ]);
            }
        }

        app(ActivityLogService::class)->log('item.created', $item);

        return redirect()->route('marble.item.edit', $item);
    }

    public function delete(Item $item)
    {
        $this->authorize('delete', $item);
        $parentId = $item->parent_id;

        app(ActivityLogService::class)->log('item.deleted', $item, ['blueprint' => $item->blueprint->identifier]);

        // Soft-delete the item and all descendants (restorable from Trash)
        Item::where('path', 'like', $item->path . '/%')->delete();
        $item->delete();

        return redirect()->route('marble.item.edit', $parentId);
    }

    public function acquireLock(Item $item)
    {
        $userId = Auth::guard('marble')->id();
        $item->acquireLock($userId);
        return response()->json(['ok' => true]);
    }

    public function releaseLock(Item $item)
    {
        $userId = Auth::guard('marble')->id();
        $item->releaseLock($userId);
        return response()->json(['ok' => true]);
    }

    public function duplicate(Item $item)
    {
        $this->authorize('create', Item::class);

        $newItem = Item::create([
            'blueprint_id' => $item->blueprint_id,
            'parent_id'    => $item->parent_id,
            'status'       => 'draft',
            'sort_order'   => $item->sort_order,
        ]);

        foreach ($item->itemValues()->get() as $iv) {
            ItemValue::create([
                'item_id'            => $newItem->id,
                'blueprint_field_id' => $iv->blueprint_field_id,
                'language_id'        => $iv->language_id,
                'value'              => $iv->value,
            ]);
        }

        return redirect()->route('marble.item.edit', $newItem);
    }

    public function moveForm(Item $item)
    {
        $this->authorize('update', $item);

        // Collect all valid parent candidates: items whose blueprint allows this blueprint as child,
        // excluding the item itself and its own descendants
        $potentialParents = Item::with('blueprint')
            ->where('id', '!=', $item->id)
            ->get()
            ->filter(function (Item $candidate) use ($item) {
                // Exclude own subtree
                if (str_starts_with($candidate->path, $item->path . '/')) {
                    return false;
                }
                return $candidate->blueprint->allowsChild($item->blueprint);
            })
            ->values();

        return view('marble::item.move', compact('item', 'potentialParents'));
    }

    public function move(Request $request, Item $item)
    {
        $this->authorize('update', $item);

        $newParent = Item::findOrFail($request->input('parent_id'));

        if (!$newParent->blueprint->allowsChild($item->blueprint)) {
            return back()->withErrors(['parent_id' => 'Blueprint not allowed as child here.']);
        }

        if (str_starts_with($newParent->path, $item->path . '/')) {
            return back()->withErrors(['parent_id' => 'Cannot move item into its own subtree.']);
        }

        $item->parent_id = $newParent->id;
        $item->save();

        return redirect()->route('marble.item.edit', $item);
    }

    public function toggleStatus(Item $item)
    {
        $this->authorize('update', $item);
        $item->status = $item->status === 'published' ? 'draft' : 'published';
        $item->save();

        $action = $item->status === 'published' ? 'item.published' : 'item.draft';
        app(ActivityLogService::class)->log($action, $item);
        app(WebhookService::class)->fire($action, $item);

        return back();
    }

    public function toggleNav(Item $item)
    {
        $this->authorize('update', $item);
        $item->show_in_nav = !$item->show_in_nav;
        $item->save();

        return back();
    }

    public function revert(Item $item, ItemRevision $revision)
    {
        $this->authorize('update', $item);

        $languages = Language::all();

        // Snapshot current state before overwriting (so restore can be undone)
        if ($item->blueprint->versionable) {
            $this->snapshotRevision($item, $languages);
        }

        foreach ($revision->values as $fieldId => $langValues) {
            foreach ($langValues as $langId => $value) {
                ItemValue::where('item_id', $item->id)
                    ->where('blueprint_field_id', $fieldId)
                    ->where('language_id', $langId)
                    ->update(['value' => $value]);
            }
        }

        $item->touch();
        Marble::invalidateItem($item);

        return redirect()->route('marble.item.edit', $item);
    }

    public function diff(Item $item, ItemRevision $revision)
    {
        $this->authorize('update', $item);
        $languages  = Language::all();
        $fields     = $item->blueprint->fields;

        // Find the previous revision to compare against
        $previous = ItemRevision::where('item_id', $item->id)
            ->where('id', '<', $revision->id)
            ->orderByDesc('id')
            ->first();

        $previousValues = $previous ? $previous->values : [];

        $diff = [];
        foreach ($fields as $field) {
            foreach ($languages as $lang) {
                $newVal = $revision->values[$field->id][$lang->id] ?? null;
                $oldVal = $previousValues[$field->id][$lang->id] ?? null;

                if ($newVal === $oldVal) continue;

                $diff[] = [
                    'field'    => $field->name,
                    'language' => $lang->name,
                    'old'      => $oldVal,
                    'new'      => $newVal,
                ];
            }
        }

        $revisions = ItemRevision::where('item_id', $item->id)
            ->with('user')
            ->orderByDesc('id')
            ->limit(15)
            ->get();
        $breadcrumb = \Marble\Admin\Facades\Marble::breadcrumb($item);

        return view('marble::item.revision-diff', compact('item', 'revision', 'previous', 'diff', 'revisions', 'breadcrumb'));
    }

    public function sort(Request $request)
    {
        foreach ($request->input('items', []) as $itemId => $sortOrder) {
            Item::where('id', $itemId)->update(['sort_order' => $sortOrder]);
        }

        return response()->json(['success' => true]);
    }

    public function searchJson(SearchRequest $request)
    {
        $query = substr(strip_tags($request->input('q', '')), 0, 100);

        $ids = ItemValue::where('value', 'LIKE', '%' . addcslashes($query, '%_\\') . '%')
            ->select('item_id')
            ->distinct()
            ->limit(20)
            ->pluck('item_id');

        $items = Item::with('blueprint')->whereIn('id', $ids)->get();

        return response()->json(
            $items->map(fn($item) => [
                'id'        => $item->id,
                'name'      => $item->name(),
                'parent_id' => $item->parent_id,
                'blueprint' => $item->blueprint->name,
                'icon'      => $item->blueprint->icon,
            ])->values()
        );
    }

    public function ajaxField(ItemValue $itemValue, Language $language, Request $request)
    {
        $fieldType = $itemValue->blueprintField->fieldTypeInstance();

        if (method_exists($fieldType, 'ajaxEndpoint')) {
            return $fieldType->ajaxEndpoint($request, $itemValue, $language->id);
        }

        return response()->json(['error' => 'No ajax endpoint'], 404);
    }

    // -------------------------------------------------------------------------

    protected function snapshotRevision(Item $item, $languages): void
    {
        $snapshot = [];

        foreach ($item->blueprint->fields as $field) {
            $snapshot[$field->id] = [];
            foreach ($languages as $language) {
                $iv = $item->itemValues()
                    ->where('blueprint_field_id', $field->id)
                    ->where('language_id', $language->id)
                    ->first();
                $snapshot[$field->id][$language->id] = $iv ? $iv->value : null;
            }
        }

        ItemRevision::create([
            'item_id' => $item->id,
            'user_id' => Auth::guard('marble')->id(),
            'values'  => $snapshot,
        ]);

        // Keep max 20 revisions per item
        $keepIds = ItemRevision::where('item_id', $item->id)
            ->orderByDesc('id')
            ->limit(20)
            ->pluck('id');

        if ($keepIds->isNotEmpty()) {
            ItemRevision::where('item_id', $item->id)
                ->whereNotIn('id', $keepIds)
                ->delete();
        }
    }
}
