<?php

namespace Marble\Admin\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

trait HasPath
{
    public static function bootHasPath(): void
    {
        static::creating(function ($model) {
            $model->path = $model->buildPath();
        });

        static::updating(function ($model) {
            if ($model->isDirty('parent_id')) {
                $oldPath = $model->path;
                $model->path = $model->buildPath();

                // After save, update all descendants
                static::saved(function ($model) use ($oldPath) {
                    if ($oldPath !== $model->path) {
                        $model->updateDescendantPaths($oldPath, $model->path);
                    }
                });
            }
        });
    }

    /**
     * Build the materialized path for this item.
     * e.g. /1/20/22
     */
    protected function buildPath(): string
    {
        if (!$this->parent_id) {
            return '/' . ($this->id ?? '');
        }

        $parent = static::find($this->parent_id);

        if (!$parent) {
            return '/' . ($this->id ?? '');
        }

        return $parent->path . '/' . ($this->id ?? '');
    }

    /**
     * Fix path after creation (id not available during creating event).
     */
    public function fixPathAfterCreate(): void
    {
        $expected = $this->buildPath();
        if ($this->path !== $expected) {
            $this->path = $expected;
            $this->saveQuietly();
        }
    }

    /**
     * Update all descendant paths when a parent moves.
     */
    protected function updateDescendantPaths(string $oldPath, string $newPath): void
    {
        $table = $this->getTable();
        $oldPrefix = $oldPath . '/';

        DB::table($table)
            ->where('path', 'LIKE', $oldPrefix . '%')
            ->update(['path' => DB::raw(
                "REPLACE(path, " . DB::getPdo()->quote($oldPath) . ", " . DB::getPdo()->quote($newPath) . ")"
            )]);
    }

    /**
     * Get all ancestor IDs from the path.
     */
    public function ancestorIds(): array
    {
        $ids = array_filter(explode('/', $this->path));
        array_pop($ids); // Remove self

        return array_map('intval', $ids);
    }

    /**
     * Get all ancestors (ordered from root to direct parent).
     */
    public function ancestors(): Collection
    {
        $ids = $this->ancestorIds();

        if (empty($ids)) {
            return new Collection();
        }

        // Sort in PHP to avoid DB-specific FIELD() function (breaks SQLite)
        return static::whereIn('id', $ids)
            ->get()
            ->sortBy(fn($item) => array_search($item->id, $ids))
            ->values();
    }

    /**
     * Get all descendants.
     */
    public function descendants(): Collection
    {
        return static::where('path', 'LIKE', $this->path . '/%')->get();
    }

    /**
     * Get depth level (0 = root).
     */
    public function depth(): int
    {
        return count($this->ancestorIds());
    }
}
