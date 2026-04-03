<?php

namespace Marble\Admin\Console;

use Illuminate\Console\Command;
use Marble\Admin\Models\Item;
use Marble\Admin\Services\NotificationService;

class WorkflowDeadlineCommand extends Command
{
    protected $signature   = 'marble:workflow-deadlines';
    protected $description = 'Notify assignees of workflow steps that have exceeded their deadline';

    public function handle(NotificationService $notifications): void
    {
        $overdue = Item::where('status', 'draft')
            ->whereNotNull('current_workflow_step_id')
            ->whereNotNull('workflow_step_entered_at')
            ->with(['blueprint.workflow', 'workflowStep.notifiables.resolveUsers', 'workflowStep'])
            ->get()
            ->filter(fn ($item) => $item->isWorkflowOverdue());

        $notified = 0;

        foreach ($overdue as $item) {
            $step = $item->workflowStep;
            if (!$step) {
                continue;
            }

            $name    = $item->name() ?? 'Item #' . $item->id;
            $daysIn  = (int) $item->workflow_step_entered_at->diffInDays(now());
            $body    = "\"{$name}\" has been at step \"{$step->name}\" for {$daysIn} day(s) — deadline: {$step->deadline_days} day(s).";
            $type    = 'workflow.deadline';

            $step->load('notifiables');
            $notifiedIds = collect();

            foreach ($step->notifiables as $notifiable) {
                foreach ($notifiable->resolveUsers() as $user) {
                    if ($notifiedIds->contains($user->id)) {
                        continue;
                    }
                    $notifiedIds->push($user->id);
                    $notifications->create($user, $type, $name, $body, $item);
                }
            }

            // Fallback: notify all users when no notifiables configured
            if ($step->notifiables->isEmpty()) {
                \Marble\Admin\Models\User::all()->each(
                    fn ($user) => $notifications->create($user, $type, $name, $body, $item)
                );
            }

            $notified++;
            $this->line("Deadline exceeded: [{$item->id}] {$name} @ {$step->name}");
        }

        $this->info("Done. {$notified} overdue item(s) notified.");
    }
}
