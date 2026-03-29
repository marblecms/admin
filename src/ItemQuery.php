<?php

namespace Marble\Admin;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Marble\Admin\Models\Item;

/**
 * Fluent query builder for Items by blueprint identifier.
 *
 * Usage:
 *   Marble::items('news')->published()->get()
 *   Marble::items('news')->published()->under($parentId)->paginate(10)
 *   Marble::items('product')->orderBy('sort_order')->first()
 */
class ItemQuery
{
    protected bool   $publishedOnly = false;
    protected ?int   $parentId      = null;
    protected string $orderColumn   = 'sort_order';
    protected string $orderDir      = 'asc';
    protected ?int   $limitCount    = null;

    public function __construct(protected string $blueprintIdentifier) {}

    /** Only return published items (respects scheduling and expiry). */
    public function published(): static
    {
        $this->publishedOnly = true;
        return $this;
    }

    /** Restrict to direct children of a given parent item ID. */
    public function under(int $parentId): static
    {
        $this->parentId = $parentId;
        return $this;
    }

    /** Order results. */
    public function orderBy(string $column, string $direction = 'asc'): static
    {
        $this->orderColumn = $column;
        $this->orderDir    = $direction;
        return $this;
    }

    /** Limit number of results. */
    public function limit(int $count): static
    {
        $this->limitCount = $count;
        return $this;
    }

    /** Execute and return all results. */
    public function get(): Collection
    {
        return $this->build()->get();
    }

    /** Execute and return paginated results. */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->build()->paginate($perPage);
    }

    /** Return the first result or null. */
    public function first(): ?Item
    {
        return $this->build()->first();
    }

    /** Return count. */
    public function count(): int
    {
        return $this->build()->count();
    }

    /** Access the underlying Eloquent builder for custom constraints. */
    public function query(): Builder
    {
        return $this->build();
    }

    protected function build(): Builder
    {
        $query = Item::whereHas('blueprint', fn ($q) => $q->where('identifier', $this->blueprintIdentifier))
            ->with('blueprint')
            ->orderBy($this->orderColumn, $this->orderDir);

        if ($this->publishedOnly) {
            $query->where('status', 'published')
                ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()))
                ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
        }

        if ($this->parentId !== null) {
            $query->where('parent_id', $this->parentId);
        }

        if ($this->limitCount !== null) {
            $query->limit($this->limitCount);
        }

        return $query;
    }
}
