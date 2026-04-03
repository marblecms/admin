<?php

namespace Marble\Admin\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Marble\Admin\Facades\Marble;
use Marble\Admin\Models\Blueprint;
use Marble\Admin\Models\Item;
use Marble\Admin\Models\Language;
use Marble\Admin\MarbleRouter;

class ItemApiController extends Controller
{
    public function items(Request $request, string $blueprint): JsonResponse
    {
        $bp = Blueprint::where('identifier', $blueprint)->first();

        if (!$bp) {
            return response()->json(['error' => 'Blueprint not found.'], 404);
        }

        if (!$bp->api_public) {
            $token = $request->attributes->get('marble_api_token');
            if (!$token) {
                return response()->json(['error' => 'Unauthorized', 'message' => 'Authentication required.'], 401);
            }
            if (!$token->hasAbility('read')) {
                return response()->json(['error' => 'Forbidden', 'message' => 'Token lacks read ability.'], 403);
            }
        }

        $languageId = $this->resolveLanguageId($request->query('language'));
        $perPage    = min((int) ($request->query('per_page', 20)), 100);
        $status     = $request->query('status', 'published');

        $query = Item::whereHas('blueprint', fn ($q) => $q->where('identifier', $blueprint))
            ->with(['blueprint.fields.fieldType', 'workflowStep'])
            ->orderBy('sort_order');

        if ($status === 'published') {
            $query->where('status', 'published');
        } elseif ($status !== 'all') {
            $query->where('status', $status);
        }

        // Optional parent filter
        if ($request->has('parent_id')) {
            $parentId = $request->query('parent_id');
            $query->where('parent_id', $parentId === 'null' ? null : (int) $parentId);
        }

        $paginator = $query->paginate($perPage);

        $collection    = $paginator->getCollection();
        $relatedCache  = $this->preloadRelated($collection);
        $data = $collection->map(fn (Item $item) => $this->serializeItem($item, $languageId, $relatedCache));

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'total'        => $paginator->total(),
                'per_page'     => $paginator->perPage(),
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $item = Item::with(['blueprint.fields.fieldType', 'workflowStep'])->find($id);

        if (!$item) {
            return response()->json(['error' => 'Item not found.'], 404);
        }

        if (!$item->blueprint->api_public) {
            $token = $request->attributes->get('marble_api_token');
            if (!$token) {
                return response()->json(['error' => 'Unauthorized', 'message' => 'Authentication required.'], 401);
            }
            if (!$token->hasAbility('read')) {
                return response()->json(['error' => 'Forbidden', 'message' => 'Token lacks read ability.'], 403);
            }
        }

        $languageId = $this->resolveLanguageId($request->query('language'));

        return response()->json($this->serializeItem($item, $languageId));
    }

    public function children(Request $request, int $id): JsonResponse
    {
        $item = Item::with('blueprint')->find($id);

        if (!$item) {
            return response()->json(['error' => 'Item not found.'], 404);
        }

        if (!$item->blueprint->api_public) {
            $token = $request->attributes->get('marble_api_token');
            if (!$token) {
                return response()->json(['error' => 'Unauthorized', 'message' => 'Authentication required.'], 401);
            }
            if (!$token->hasAbility('read')) {
                return response()->json(['error' => 'Forbidden', 'message' => 'Token lacks read ability.'], 403);
            }
        }

        $languageId = $this->resolveLanguageId($request->query('language'));
        $status     = $request->query('status', 'published');
        $perPage    = min((int) ($request->query('per_page', 20)), 100);

        $query = Item::where('parent_id', $item->id)
            ->with(['blueprint.fields.fieldType', 'workflowStep'])
            ->orderBy('sort_order');

        if ($status === 'published') {
            $query->where('status', 'published');
        } elseif ($status !== 'all') {
            $query->where('status', $status);
        }

        $paginator = $query->paginate($perPage);

        $collection   = $paginator->getCollection();
        $relatedCache = $this->preloadRelated($collection);
        $data = $collection->map(fn (Item $child) => $this->serializeItem($child, $languageId, $relatedCache));

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'total'        => $paginator->total(),
                'per_page'     => $paginator->perPage(),
            ],
        ]);
    }

    public function resolve(Request $request): JsonResponse
    {
        $path = $request->query('path', '/');

        $item = MarbleRouter::resolve($path);

        if (!$item || !$item->isPublished()) {
            return response()->json(['error' => 'Not found.'], 404);
        }

        if (!$item->blueprint->api_public) {
            $token = $request->attributes->get('marble_api_token');
            if (!$token) {
                return response()->json(['error' => 'Unauthorized', 'message' => 'Authentication required.'], 401);
            }
            if (!$token->hasAbility('read')) {
                return response()->json(['error' => 'Forbidden', 'message' => 'Token lacks read ability.'], 403);
            }
        }

        $languageId = $this->resolveLanguageId($request->query('language'));
        $item->loadMissing('workflowStep');

        return response()->json($this->serializeItem($item, $languageId));
    }

    private function serializeItem(Item $item, int $languageId, array $relatedCache = []): array
    {
        if (!$item->relationLoaded('blueprint')) {
            $item->load(['blueprint.fields.fieldType', 'workflowStep']);
        }

        $fields = [];

        foreach ($item->blueprint->fields as $field) {
            $fieldTypeIdentifier = $field->fieldType?->identifier ?? '';
            $rawValue = $item->rawValue($field->identifier, $languageId);

            if ($fieldTypeIdentifier === 'object_relation') {
                if ($rawValue) {
                    $related = $relatedCache[(int) $rawValue]
                        ?? Item::with(['blueprint.fields.fieldType'])->find((int) $rawValue);
                    $fields[$field->identifier] = $related ? $this->serializeItemShallow($related, $languageId) : null;
                } else {
                    $fields[$field->identifier] = null;
                }
            } elseif ($fieldTypeIdentifier === 'object_relation_list') {
                $ids = is_array($rawValue) ? $rawValue : (json_decode($rawValue ?? '[]', true) ?? []);
                $relatedItems = collect($ids)->map(fn ($id) =>
                    $relatedCache[(int) $id] ?? Item::with(['blueprint.fields.fieldType'])->find((int) $id)
                )->filter();
                $fields[$field->identifier] = $relatedItems->map(fn (Item $r) => $this->serializeItemShallow($r, $languageId))->values()->all();
            } else {
                $fields[$field->identifier] = $item->value($field->identifier, $languageId);
            }
        }

        $workflowStep = null;
        if ($item->current_workflow_step_id && $item->relationLoaded('workflowStep')) {
            $workflowStep = $item->workflowStep?->name;
        } elseif ($item->status === 'published') {
            $workflowStep = 'published';
        }

        return [
            'id'            => $item->id,
            'name'          => $item->name($languageId),
            'blueprint'     => $item->blueprint->identifier,
            'status'        => $item->status,
            'workflow_step' => $workflowStep,
            'parent_id'     => $item->parent_id,
            'slug'          => $item->slug($languageId),
            'all_slugs'     => $item->allSlugs(),
            'url'           => MarbleRouter::urlFor($item, $languageId),
            'created_at'    => $item->created_at?->toIso8601String(),
            'updated_at'    => $item->updated_at?->toIso8601String(),
            'fields'        => $fields,
        ];
    }

    /**
     * Pre-load all items referenced by object_relation / object_relation_list fields
     * across a collection, keyed by ID, to avoid N+1 queries during serialization.
     */
    private function preloadRelated(\Illuminate\Support\Collection $items): array
    {
        $ids = collect();

        foreach ($items as $item) {
            foreach ($item->blueprint->fields as $field) {
                $identifier = $field->fieldType?->identifier ?? '';
                $raw = $item->rawValue($field->identifier, 0); // language-agnostic raw lookup

                if ($identifier === 'object_relation' && $raw) {
                    $ids->push((int) $raw);
                } elseif ($identifier === 'object_relation_list') {
                    $decoded = is_array($raw) ? $raw : (json_decode($raw ?? '[]', true) ?? []);
                    $ids = $ids->merge(array_map('intval', $decoded));
                }
            }
        }

        if ($ids->isEmpty()) {
            return [];
        }

        return Item::with(['blueprint.fields.fieldType'])
            ->whereIn('id', $ids->unique()->all())
            ->get()
            ->keyBy('id')
            ->all();
    }

    /**
     * Serialize an item without recursing into object_relation fields (one level deep only).
     */
    private function serializeItemShallow(Item $item, int $languageId): array
    {
        if (!$item->relationLoaded('blueprint')) {
            $item->load('blueprint.fields.fieldType');
        }

        $fields = [];

        foreach ($item->blueprint->fields as $field) {
            $fieldTypeIdentifier = $field->fieldType?->identifier ?? '';

            if (in_array($fieldTypeIdentifier, ['object_relation', 'object_relation_list'], true)) {
                $fields[$field->identifier] = null;
            } else {
                $fields[$field->identifier] = $item->value($field->identifier, $languageId);
            }
        }

        return [
            'id'         => $item->id,
            'name'       => $item->name($languageId),
            'blueprint'  => $item->blueprint->identifier,
            'status'     => $item->status,
            'parent_id'  => $item->parent_id,
            'slug'       => $item->slug($languageId),
            'url'        => MarbleRouter::urlFor($item, $languageId),
            'created_at' => $item->created_at?->toIso8601String(),
            'updated_at' => $item->updated_at?->toIso8601String(),
            'fields'     => $fields,
        ];
    }

    private function resolveLanguageId(?string $languageCode): int
    {
        if ($languageCode) {
            $lang = Language::where('code', $languageCode)->first();
            if ($lang) {
                return $lang->id;
            }
        }

        return Marble::currentLanguageId();
    }
}
