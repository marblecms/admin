<?php

namespace Marble\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class WorkflowStepNotifiable extends Model
{
    protected $fillable = ['workflow_step_id', 'notifiable_type', 'notifiable_id', 'channel'];

    public function step(): BelongsTo
    {
        return $this->belongsTo(WorkflowStep::class, 'workflow_step_id');
    }

    /**
     * Resolve to actual User objects (handles both 'user' and 'group' types).
     */
    public function resolveUsers(): Collection
    {
        if ($this->notifiable_type === 'user') {
            $user = User::find($this->notifiable_id);
            return $user ? collect([$user]) : collect();
        }

        $group = UserGroup::with('users')->find($this->notifiable_id);
        return $group ? $group->users : collect();
    }
}
