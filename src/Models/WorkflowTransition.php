<?php

namespace Marble\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowTransition extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['item_id', 'user_id', 'from_step_id', 'to_step_id', 'action', 'comment'];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fromStep(): BelongsTo
    {
        return $this->belongsTo(WorkflowStep::class, 'from_step_id');
    }

    public function toStep(): BelongsTo
    {
        return $this->belongsTo(WorkflowStep::class, 'to_step_id');
    }

    /**
     * Human-readable label for the destination step.
     */
    public function toLabel(): string
    {
        if ($this->to_step_id === null && $this->action === 'advance') {
            return trans('marble::admin.published');
        }
        return $this->toStep?->name ?? '–';
    }

    /**
     * Human-readable label for the source step.
     */
    public function fromLabel(): string
    {
        return $this->fromStep?->name ?? trans('marble::admin.workflow_draft');
    }
}
