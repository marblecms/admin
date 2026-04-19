<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Marble\Admin\Facades\Marble;
use Marble\Admin\Http\Requests\ItemCreateRequest;
use Marble\Admin\Http\Requests\ItemUpdateRequest;
use Marble\Admin\Http\Requests\SearchRequest;
use Marble\Admin\Models\Blueprint;
use Marble\Admin\Models\FieldType;
use Marble\Admin\Models\Item;
use Marble\Admin\Models\ItemRevision;
use Marble\Admin\Models\ItemValue;
use Marble\Admin\Models\FormSubmission;
use Marble\Admin\Models\Language;
use Marble\Admin\Models\ItemComment;
use Marble\Admin\Models\ItemTask;
use Marble\Admin\Models\Redirect;
use Marble\Admin\Models\User;
use Marble\Admin\Events\ItemPublished;
use Marble\Admin\Events\ItemSaved;
use Marble\Admin\Events\ItemTrashed;
use Marble\Admin\Services\ActivityLogService;
use Marble\Admin\Services\ItemRevisionService;
use Marble\Admin\Services\NotificationService;
use Marble\Admin\Services\WebhookService;

class ItemController extends Controller
{
    use AuthorizesRequests;

    private ActivityLogService $activityLog;
    private WebhookService $webhooks;
    private ItemRevisionService $revisions;
    private NotificationService $notifications;

    public function __construct(ActivityLogService $activityLog, WebhookService $webhooks, ItemRevisionService $revisions, NotificationService $notifications)
    {
        $this->activityLog   = $activityLog;
        $this->webhooks      = $webhooks;
        $this->revisions     = $revisions;
        $this->notifications = $notifications;
    }

    public function edit(Item $item)
    {
        $this->authorize('update', $item);
        $languages = Language::all();
        $item->blueprint->load('workflow.steps');
        $groupedFields = $item->blueprint->groupedFields();

        $childItems = null;
        if ($item->blueprint->list_children || $item->blueprint->inline_children) {
            $childItems = $item->children()->with('blueprint')->orderBy('sort_order')->get();
        }

        $revisions = ItemRevision::where('item_id', $item->id)
            ->with('user')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        $currentUser = Auth::guard('marble')->user();

        $item->acquireLock($currentUser->id);
        $activeLock    = $item->activeLock();
        $lockedByOther = $activeLock && $activeLock->user_id !== $currentUser->id;

        $submissions = $item->blueprint->is_form
            ? FormSubmission::where('item_id', $item->id)->orderByDesc('created_at')->paginate(30)
            : null;

        $slugPaths = collect($languages)
            ->map(fn($l) => $item->slug($l->id))
            ->filter()
            ->map(fn($s) => ltrim($s, '/'))
            ->unique()
            ->values();

        $inboundRedirects = Redirect::where('active', true)
            ->where(function ($q) use ($item, $slugPaths) {
                $q->where('target_item_id', $item->id);
                foreach ($slugPaths as $path) {
                    $q->orWhere('target_path', $path)->orWhere('target_path', '/' . $path);
                }
            })
            ->get();

        $isWatching = \Marble\Admin\Models\ItemSubscription::where('user_id', $currentUser->id)
            ->where('item_id', $item->id)->exists();

        $collaborationComments = ItemComment::where('item_id', $item->id)
            ->with('user')
            ->orderBy('created_at')
            ->get();

        $collaborationTasks = ItemTask::where('item_id', $item->id)
            ->with('assignee')
            ->orderBy('done')
            ->orderBy('due_date')
            ->orderBy('id')
            ->get();

        return view('marble::item.edit', [
            'item'                   => $item,
            'languages'              => $languages,
            'groupedFields'          => $groupedFields,
            'childItems'             => $childItems,
            'currentUser'            => $currentUser,
            'revisions'              => $revisions,
            'lockedByOther'          => $lockedByOther,
            'lockUser'               => $lockedByOther ? $activeLock->user : null,
            'usedBy'                 => $item->usedBy(),
            'submissions'            => $submissions,
            'breadcrumb'             => Marble::breadcrumb($item),
            'aliases'                => \Marble\Admin\Models\ItemUrlAlias::where('item_id', $item->id)->with('language')->get(),
            'mountPoints'            => $item->mountPoints()->with('mountParent.blueprint')->get(),
            'inboundRedirects'       => $inboundRedirects,
            'isWatching'             => $isWatching,
            'collaborationComments'  => $collaborationComments,
            'collaborationTasks'     => $collaborationTasks,
            'collaborationUsers'     => User::orderBy('name')->get(),
        ]);
    }

    public function save(ItemUpdateRequest $request, Item $item)
    {
        $this->authorize('update', $item);
        $languages       = Language::all();
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

        if ($item->blueprint->versionable) {
            $this->revisions->snapshot($item, $languages, Auth::guard('marble')->id());
        }

        $changedFields = [];

        DB::transaction(function () use ($item, $languages, $attributeValues, $request, &$changedFields) {
            foreach ($item->blueprint->allFields() as $field) {
                if ($field->locked) continue;

                $fieldType = $field->fieldTypeInstance();

                foreach ($languages as $language) {
                    if (!$field->translatable && $language->id !== Marble::primaryLanguageId()) continue;

                    $newValue  = $attributeValues[$field->id][$language->id] ?? null;
                    $itemValue = ItemValue::firstOrNew([
                        'item_id'            => $item->id,
                        'blueprint_field_id' => $field->id,
                        'language_id'        => $language->id,
                    ]);

                    $oldValue   = $itemValue->exists ? $itemValue->raw() : $fieldType->defaultValue();
                    $processed  = $fieldType->processInput($oldValue, $newValue, $request, $field->id, $language->id);
                    $serialized = $fieldType->serialize($processed);

                    if ($serialized !== $itemValue->value) {
                        $key = $field->identifier . ($field->translatable ? '.' . $language->code : '');
                        $changedFields[$key] = ['old' => $oldValue, 'new' => $serialized];
                    }

                    $itemValue->value = $serialized;
                    $itemValue->save();
                }
            }
        });

        $item->touch();
        $this->activityLog->log('item.saved', $item);
        $this->webhooks->fire('item.saved', $item, $changedFields);
        $this->notifySubscribers($item, 'item.saved', Auth::guard('marble')->id());
        ItemSaved::dispatch($item, $changedFields, Auth::guard('marble')->id());

        if ($parentId = $request->input('_inline_parent_id')) {
            return redirect()->route('marble.item.edit', $parentId)
                ->withFragment('child-' . $item->id)
                ->with('success', trans('marble::admin.item_saved'));
        }

        return redirect()->route('marble.item.edit', $item)
            ->with('success', trans('marble::admin.item_saved'));
    }

    public function saveSchedule(Request $request, Item $item)
    {
        $this->authorize('update', $item);

        $item->published_at = $request->input('published_at') ?: null;
        $item->expires_at   = $request->input('expires_at') ?: null;
        $item->save();

        return redirect()->route('marble.item.edit', $item)
            ->with('success', trans('marble::admin.saved'));
    }

    public function add(Item $parentItem)
    {
        $this->authorize('create', Item::class);
        $user = Auth::guard('marble')->user();

        $allowedBlueprints = $parentItem->blueprint->allowsAllChildren()
            ? Blueprint::all()
            : $parentItem->blueprint->allowedChildBlueprints;

        $allowedBlueprints = $allowedBlueprints->filter(fn($bp) => $user->canDoWithBlueprint($bp->id, 'create'));

        return view('marble::item.add', [
            'parentItem'          => $parentItem,
            'allowedBlueprints'   => $allowedBlueprints,
            'preselectedBlueprint' => (int) request()->query('blueprint'),
        ]);
    }

    public function create(ItemCreateRequest $request)
    {
        $this->authorize('create', Item::class);
        $user        = Auth::guard('marble')->user();
        $blueprintId = $request->input('blueprint_id');

        if (!$user->canDoWithBlueprint((int) $blueprintId, 'create')) {
            abort(403);
        }

        $blueprint = Blueprint::findOrFail($blueprintId);
        $languages = Language::all();
        $name      = $request->input('name', '');

        $item = Item::create([
            'blueprint_id' => $blueprintId,
            'parent_id'    => $request->input('parent_id'),
            'status'       => 'draft',
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

        $this->activityLog->log('item.created', $item);

        if ($request->input('_inline_parent_id')) {
            return redirect()->route('marble.item.edit', $request->input('parent_id'))
                ->withFragment('child-' . $item->id);
        }

        return redirect()->route('marble.item.edit', $item);
    }

    public function delete(Item $item)
    {
        $this->authorize('delete', $item);
        $parentId = $item->parent_id;

        $deletingIds = Item::where('path', 'like', $item->path . '%')
            ->pluck('id')
            ->push($item->id)
            ->unique();

        $restrictingItems = $this->findRestrictingRelations($deletingIds);
        if ($restrictingItems->isNotEmpty()) {
            return redirect()->back()->withErrors([
                'delete' => trans('marble::admin.delete_restricted', [
                    'items' => $restrictingItems->map(fn($i) => '"' . $i->name() . '"')->implode(', '),
                ]),
            ]);
        }

        DB::transaction(function () use ($item, $deletingIds) {
            $this->handleRelationsOnDelete($deletingIds);
            Item::where('path', 'like', $item->path . '/%')->delete();
            $item->delete();
        });

        $this->activityLog->log('item.deleted', $item, ['blueprint' => $item->blueprint->identifier]);
        ItemTrashed::dispatch($item, Auth::guard('marble')->id());

        return redirect()->route('marble.item.edit', $parentId)
            ->with('success', trans('marble::admin.item_deleted'));
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

        $slugFieldIds = $item->blueprint->fields()
            ->where('identifier', 'slug')
            ->pluck('id')
            ->flip();

        foreach ($item->itemValues()->get() as $iv) {
            $value = $iv->value;
            if ($slugFieldIds->has($iv->blueprint_field_id) && $value !== null && $value !== '') {
                $value = $value . '-copy';
            }
            ItemValue::create([
                'item_id'            => $newItem->id,
                'blueprint_field_id' => $iv->blueprint_field_id,
                'language_id'        => $iv->language_id,
                'value'              => $value,
            ]);
        }

        return redirect()->route('marble.item.edit', $newItem);
    }

    public function toggleStatus(Item $item)
    {
        $this->authorize('update', $item);

        $item->load('blueprint.workflow');
        if ($item->blueprint?->workflow) {
            return back()->withErrors(['status' => trans('marble::admin.workflow_required')]);
        }

        $item->status = $item->status === 'published' ? 'draft' : 'published';
        $item->save();

        $action = $item->status === 'published' ? 'item.published' : 'item.draft';
        $this->activityLog->log($action, $item);
        $this->webhooks->fire($action, $item);
        $this->notifySubscribers($item, $action, Auth::guard('marble')->id());
        if ($item->status === 'published') {
            ItemPublished::dispatch($item, Auth::guard('marble')->id());
        }

        return back();
    }

    public function toggleNav(Item $item)
    {
        $this->authorize('update', $item);
        $item->show_in_nav = !$item->show_in_nav;
        $item->save();

        return back();
    }

    public function searchJson(SearchRequest $request)
    {
        $user  = Auth::guard('marble')->user();
        $query = substr(strip_tags($request->input('q', '')), 0, 100);

        $ids = ItemValue::where('value', 'LIKE', '%' . addcslashes($query, '%_\\') . '%')
            ->select('item_id')
            ->distinct()
            ->limit(50)
            ->pluck('item_id');

        $items = Item::with('blueprint')->whereIn('id', $ids)->get()
            ->filter(fn($item) => $user->canDoWithBlueprint($item->blueprint_id, 'read'))
            ->take(20)
            ->values();

        return response()->json(
            $items->map(fn($item) => [
                'id'        => $item->id,
                'name'      => $item->name(),
                'parent_id' => $item->parent_id,
                'blueprint' => $item->blueprint->name,
                'icon'      => $item->blueprint->icon,
                'slug'      => $item->slug(),
            ])->values()
        );
    }

    public function ajaxField(ItemValue $itemValue, Language $language, Request $request)
    {
        $this->authorize('update', $itemValue->item);

        $fieldType = $itemValue->blueprintField->fieldTypeInstance();

        if (method_exists($fieldType, 'ajaxEndpoint')) {
            return $fieldType->ajaxEndpoint($request, $itemValue, $language->id);
        }

        return response()->json(['error' => 'No ajax endpoint'], 404);
    }

    // -------------------------------------------------------------------------

    private function findRestrictingRelations(\Illuminate\Support\Collection $deletingIds): \Illuminate\Support\Collection
    {
        return ItemValue::whereIn('value', $deletingIds->map(fn($id) => (string) $id))
            ->whereHas('blueprintField', fn($q) => $q->where('field_type_id',
                FieldType::where('identifier', 'object_relation')->value('id')
            ))
            ->with('blueprintField', 'item.blueprint')
            ->get()
            ->filter(function ($iv) use ($deletingIds) {
                $config   = $iv->blueprintField->configuration ?? [];
                $behavior = $config['on_delete'] ?? 'detach';
                return $behavior === 'restrict' && !$deletingIds->contains($iv->item_id);
            })
            ->map(fn($iv) => $iv->item)
            ->unique('id')
            ->values();
    }

    private function notifySubscribers(Item $item, string $action, int $actorUserId): void
    {
        $subscribers = \Marble\Admin\Models\User::whereIn('id', $item->subscriberIds())
            ->where('id', '!=', $actorUserId)
            ->get();

        $title = match ($action) {
            'item.published' => trans('marble::admin.subscription_notify_published', ['name' => $item->name()]),
            'item.draft'     => trans('marble::admin.subscription_notify_draft',     ['name' => $item->name()]),
            default          => trans('marble::admin.subscription_notify_saved',     ['name' => $item->name()]),
        };

        foreach ($subscribers as $user) {
            $this->notifications->create($user, $action, $title, '', $item);
        }
    }

    private function handleRelationsOnDelete(\Illuminate\Support\Collection $deletingIds): void
    {
        $objectRelationTypeId = FieldType::where('identifier', 'object_relation')->value('id');

        ItemValue::whereIn('value', $deletingIds->map(fn($id) => (string) $id))
            ->whereHas('blueprintField', fn($q) => $q->where('field_type_id', $objectRelationTypeId))
            ->with('blueprintField', 'item.blueprint')
            ->get()
            ->each(function ($iv) use ($deletingIds) {
                if ($deletingIds->contains($iv->item_id)) return;
                $config   = $iv->blueprintField->configuration ?? [];
                $behavior = $config['on_delete'] ?? 'detach';

                if ($behavior === 'detach') {
                    $iv->update(['value' => null]);
                } elseif ($behavior === 'cascade') {
                    $ownerItem = $iv->item;
                    if ($ownerItem && Gate::allows('delete', $ownerItem)) {
                        Item::where('path', 'like', $ownerItem->path . '/%')->delete();
                        $ownerItem->delete();
                    }
                }
            });
    }

}
