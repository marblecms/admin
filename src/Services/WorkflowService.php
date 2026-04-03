<?php

namespace Marble\Admin\Services;

use Illuminate\Support\Facades\DB;
use Marble\Admin\Models\Item;
use Marble\Admin\Models\User;
use Marble\Admin\Models\WorkflowStep;
use Marble\Admin\Models\WorkflowTransition;

class WorkflowService
{
    public function __construct(private NotificationService $notifications) {}

    // -------------------------------------------------------------------------
    // Permission check
    // -------------------------------------------------------------------------

    /**
     * Returns true if the actor is allowed to retreat the item from its current step.
     * Uses the same group-restriction logic as canAdvance.
     */
    public function canRetreat(Item $item, User $actor): bool
    {
        // Retreating a published item back to the last workflow step
        if ($item->status === 'published') {
            $item->loadMissing('blueprint.workflow.steps.allowedGroups');
            $last = $item->blueprint?->workflow?->steps->last();
            if (!$last || $last->allowedGroups->isEmpty()) {
                return true;
            }
            return $last->allowedGroups->contains('id', $actor->user_group_id);
        }

        $stepId = $item->current_workflow_step_id;
        if (!$stepId) {
            return false; // Already at beginning — nowhere to retreat to
        }

        $step = WorkflowStep::with('allowedGroups')->find($stepId);
        if (!$step || $step->allowedGroups->isEmpty()) {
            return true;
        }

        return $step->allowedGroups->contains('id', $actor->user_group_id);
    }

    /**
     * Returns true if the actor is allowed to advance the item from its current step.
     * No restrictions set on step = anyone can advance.
     */
    public function canAdvance(Item $item, User $actor): bool
    {
        $stepId = $item->current_workflow_step_id;
        if (!$stepId) {
            return true; // advancing from draft (no step) → first step, unrestricted
        }

        $step = WorkflowStep::with('allowedGroups')->find($stepId);
        if (!$step || $step->allowedGroups->isEmpty()) {
            return true;
        }

        return $step->allowedGroups->contains('id', $actor->user_group_id);
    }

    // -------------------------------------------------------------------------
    // State transitions
    // -------------------------------------------------------------------------

    /**
     * Advance item to next workflow step.
     * If already at the final step, publish the item.
     */
    public function advance(Item $item, User $actor): void
    {
        $item->load('blueprint.workflow.steps');
        $workflow = $item->blueprint?->workflow;

        if (!$workflow) {
            return;
        }

        $fromStep = $item->current_workflow_step_id
            ? WorkflowStep::find($item->current_workflow_step_id)
            : null;

        DB::transaction(function () use ($item, $actor, $fromStep) {
            if ($item->isAtFinalWorkflowStep()) {
                $item->status = 'published';
                $item->current_workflow_step_id = null;
                $item->save();

                $this->logTransition($item, $actor, $fromStep, null, 'advance');
                $this->sendNotificationsForStep($item, $actor, null, 'advance', null);
            } else {
                $next = $item->nextWorkflowStep();
                if (!$next) {
                    return;
                }

                $item->current_workflow_step_id   = $next->id;
                $item->workflow_step_entered_at   = now();
                $item->save();

                $this->logTransition($item, $actor, $fromStep, $next, 'advance');
                $this->sendNotificationsForStep($item, $actor, $next, 'advance', null);
            }
        });
    }

    /**
     * Move item back one workflow step (only if there is a previous step).
     * If published, move back to the last workflow step.
     */
    public function retreat(Item $item, User $actor): void
    {
        $item->load('blueprint.workflow.steps');
        $workflow = $item->blueprint?->workflow;

        if (!$workflow) {
            return;
        }

        $steps = $workflow->steps;

        DB::transaction(function () use ($item, $actor, $steps) {
            if ($item->status === 'published') {
                // Was published — go back to last step as draft
                $last = $steps->last();
                if ($last) {
                    $item->status                   = 'draft';
                    $item->current_workflow_step_id = $last->id;
                    $item->workflow_step_entered_at = now();
                    $item->save();

                    $this->logTransition($item, $actor, null, $last, 'retreat');
                }
                return;
            }

            $idx = $steps->search(fn ($s) => $s->id === $item->current_workflow_step_id);

            if ($idx === false || $idx === 0) {
                return;
            }

            $fromStep = $steps->get($idx);
            $prev     = $steps->get($idx - 1);
            $item->current_workflow_step_id = $prev->id;
            $item->workflow_step_entered_at = now();
            $item->save();

            $this->logTransition($item, $actor, $fromStep, $prev, 'retreat');
        });
    }

    /**
     * Reject item back to the configured step (or previous step if none configured).
     */
    public function reject(Item $item, User $actor, string $comment = ''): void
    {
        $item->load('blueprint.workflow.steps', 'workflowStep.rejectToStep');
        $currentStep = $item->workflowStep;

        if (!$currentStep || !$currentStep->reject_enabled) {
            return;
        }

        $toStep = $currentStep->rejectToStep;

        if (!$toStep) {
            // No reject target configured — go to previous step
            $steps = $item->blueprint->workflow->steps;
            $idx   = $steps->search(fn ($s) => $s->id === $currentStep->id);
            if ($idx > 0) {
                $toStep = $steps->get($idx - 1);
            } else {
                // Already at first step — can't go further back
                return;
            }
        }

        $this->logTransition($item, $actor, $currentStep, $toStep, 'reject', $comment);

        $item->current_workflow_step_id = $toStep->id;
        $item->workflow_step_entered_at = now();
        $item->save();

        $this->sendNotificationsForStep($item, $actor, $toStep, 'reject', $comment);
    }

    // -------------------------------------------------------------------------
    // Internals
    // -------------------------------------------------------------------------

    private function logTransition(
        Item $item,
        User $actor,
        ?WorkflowStep $from,
        ?WorkflowStep $to,
        string $action,
        string $comment = ''
    ): void {
        WorkflowTransition::create([
            'item_id'      => $item->id,
            'user_id'      => $actor->id,
            'from_step_id' => $from?->id,
            'to_step_id'   => $to?->id,
            'action'       => $action,
            'comment'      => $comment ?: null,
        ]);
    }

    /**
     * Send notifications when an item arrives at $toStep (null = published).
     * Uses per-step notifiable config; falls back to notifying all other users via CMS.
     */
    private function sendNotificationsForStep(
        Item $item,
        User $actor,
        ?WorkflowStep $toStep,
        string $action,
        ?string $comment
    ): void {
        $name = $item->name() ?? 'Item #' . $item->id;

        $body = match (true) {
            $action === 'reject'               => "{$actor->name} rejected \"{$name}\" to \"{$toStep?->name}\"" . ($comment ? ": {$comment}" : ''),
            $toStep === null                    => "{$actor->name} published \"{$name}\"",
            default                             => "{$actor->name} moved \"{$name}\" to \"{$toStep->name}\"",
        };

        $type = 'workflow.' . $action;

        // Use per-step notifiables if configured
        if ($toStep) {
            $toStep->load('notifiables');
            $notified = collect();

            foreach ($toStep->notifiables as $notifiable) {
                foreach ($notifiable->resolveUsers() as $user) {
                    if ($notified->contains($user->id)) {
                        continue;
                    }
                    $notified->push($user->id);

                    if (in_array($notifiable->channel, ['cms', 'both'])) {
                        $this->notifications->create($user, $type, $name, $body, $item);
                    }
                    // email stub: if (in_array($notifiable->channel, ['email', 'both'])) { ... }
                }
            }

            // If notifiables are configured, use them exclusively (even if all were deduped).
            // Only fall back to "all users" when no notifiables are defined on this step.
            if ($toStep->notifiables->isNotEmpty()) {
                return;
            }
        }

        // Fallback: CMS-notify all other users
        User::where('id', '!=', $actor->id)->each(
            fn ($user) => $this->notifications->create($user, $type, $name, $body, $item)
        );
    }
}
