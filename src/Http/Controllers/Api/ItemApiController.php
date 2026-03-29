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
            ->with('blueprint.fields.fieldType')
            ->orderBy('sort_order');

        if ($status === 'published') {
            $query->where('status', 'published');
        } elseif ($status !== 'all') {
            $query->where('status', $status);
        }

        $paginator = $query->paginate($perPage);

        $data = $paginator->getCollection()->map(fn (Item $item) => $this->serializeItem($item, $languageId));

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
        $item = Item::with('blueprint.fields.fieldType')->find($id);

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

        return response()->json($this->serializeItem($item, $languageId));
    }

    private function serializeItem(Item $item, int $languageId): array
    {
        // Ensure blueprint & fields are loaded
        if (!$item->relationLoaded('blueprint')) {
            $item->load('blueprint.fields.fieldType');
        }

        $fields = [];

        foreach ($item->blueprint->fields as $field) {
            $fieldTypeIdentifier = $field->fieldType?->identifier ?? '';
            $rawValue = $item->rawValue($field->identifier, $languageId);

            if ($fieldTypeIdentifier === 'object_relation') {
                if ($rawValue) {
                    $related = Item::with('blueprint.fields.fieldType')->find((int) $rawValue);
                    $fields[$field->identifier] = $related ? $this->serializeItemShallow($related, $languageId) : null;
                } else {
                    $fields[$field->identifier] = null;
                }
            } elseif ($fieldTypeIdentifier === 'object_relation_list') {
                $ids = is_array($rawValue) ? $rawValue : (json_decode($rawValue ?? '[]', true) ?? []);
                $relatedItems = Item::with('blueprint.fields.fieldType')->findMany($ids);
                $fields[$field->identifier] = $relatedItems->map(fn (Item $r) => $this->serializeItemShallow($r, $languageId))->values()->all();
            } else {
                $fields[$field->identifier] = $item->value($field->identifier, $languageId);
            }
        }

        return [
            'id'         => $item->id,
            'blueprint'  => $item->blueprint->identifier,
            'status'     => $item->status,
            'slug'       => $item->slug($languageId),
            'url'        => MarbleRouter::urlFor($item, $languageId),
            'created_at' => $item->created_at?->toIso8601String(),
            'updated_at' => $item->updated_at?->toIso8601String(),
            'fields'     => $fields,
        ];
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

            // Don't recurse further into relation fields
            if (in_array($fieldTypeIdentifier, ['object_relation', 'object_relation_list'], true)) {
                $fields[$field->identifier] = null;
            } else {
                $fields[$field->identifier] = $item->value($field->identifier, $languageId);
            }
        }

        return [
            'id'         => $item->id,
            'blueprint'  => $item->blueprint->identifier,
            'status'     => $item->status,
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
