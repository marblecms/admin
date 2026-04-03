<?php

namespace Marble\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowStep extends Model
{
    protected $fillable = ['workflow_id', 'name', 'sort_order', 'reject_enabled', 'reject_to_step_id', 'deadline_days'];

    protected $casts = [
        'reject_enabled' => 'boolean',
        'deadline_days'  => 'integer',
    ];

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'current_workflow_step_id');
    }

    public function notifiables(): HasMany
    {
        return $this->hasMany(WorkflowStepNotifiable::class);
    }

    /**
     * User groups that are allowed to advance from this step.
     * Empty = anyone can advance.
     */
    public function allowedGroups(): BelongsToMany
    {
        return $this->belongsToMany(
            UserGroup::class,
            'workflow_step_permissions',
            'workflow_step_id',
            'user_group_id'
        );
    }

    /**
     * The step to reject back to (if reject_enabled).
     */
    public function rejectToStep(): BelongsTo
    {
        return $this->belongsTo(WorkflowStep::class, 'reject_to_step_id');
    }
}
