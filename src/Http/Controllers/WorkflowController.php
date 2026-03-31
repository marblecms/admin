<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Marble\Admin\Models\User;
use Marble\Admin\Models\UserGroup;
use Marble\Admin\Models\Workflow;
use Marble\Admin\Models\WorkflowStep;
use Marble\Admin\Models\WorkflowStepNotifiable;

class WorkflowController extends Controller
{
    public function index()
    {
        $workflows = Workflow::with('steps')->get();

        return view('marble::workflow.index', [
            'workflows' => $workflows,
        ]);
    }

    public function create()
    {
        $workflow = Workflow::create(['name' => 'New Workflow']);

        return redirect()->route('marble.workflow.edit', $workflow);
    }

    public function edit(Workflow $workflow)
    {
        $workflow->load(['steps.notifiables', 'steps.allowedGroups', 'steps.rejectToStep']);

        return view('marble::workflow.edit', [
            'workflow'   => $workflow,
            'allUsers'   => User::orderBy('name')->get(),
            'allGroups'  => UserGroup::orderBy('name')->get(),
        ]);
    }

    public function save(Request $request, Workflow $workflow)
    {
        $request->validate([
            'name'                       => 'required|string|max:255',
            'steps'                      => 'nullable|array',
            'steps.*.name'               => 'required|string|max:255',
            'steps.*.reject_enabled'     => 'nullable|boolean',
            'steps.*.reject_to_step_id'  => 'nullable|integer',
            'steps.*.allowed_groups'     => 'nullable|array',
            'steps.*.notifiables'        => 'nullable|array',
        ]);

        $workflow->update(['name' => $request->input('name')]);

        $incoming    = collect($request->input('steps', []));
        $existingIds = $workflow->steps->pluck('id')->toArray();
        $keptIds     = $incoming->pluck('id')->filter()->map(fn ($id) => (int) $id)->toArray();
        $toDelete    = array_diff($existingIds, $keptIds);

        WorkflowStep::whereIn('id', $toDelete)->delete();

        foreach ($incoming as $i => $stepData) {
            $rejectEnabled   = !empty($stepData['reject_enabled']);
            $rejectToStepId  = $rejectEnabled && !empty($stepData['reject_to_step_id'])
                ? (int) $stepData['reject_to_step_id']
                : null;

            if (!empty($stepData['id'])) {
                $step = WorkflowStep::find((int) $stepData['id']);
                $step->update([
                    'name'              => $stepData['name'],
                    'sort_order'        => $i,
                    'reject_enabled'    => $rejectEnabled,
                    'reject_to_step_id' => $rejectToStepId,
                ]);
            } else {
                $step = WorkflowStep::create([
                    'workflow_id'       => $workflow->id,
                    'name'              => $stepData['name'],
                    'sort_order'        => $i,
                    'reject_enabled'    => $rejectEnabled,
                    'reject_to_step_id' => $rejectToStepId,
                ]);
            }

            // Sync allowed groups (permissions)
            $allowedGroups = array_filter((array) ($stepData['allowed_groups'] ?? []), 'is_numeric');
            $step->allowedGroups()->sync($allowedGroups);

            // Sync notifiables
            $step->notifiables()->delete();
            foreach ((array) ($stepData['notifiables'] ?? []) as $n) {
                if (empty($n['type']) || empty($n['id'])) {
                    continue;
                }
                WorkflowStepNotifiable::create([
                    'workflow_step_id' => $step->id,
                    'notifiable_type'  => $n['type'],
                    'notifiable_id'    => (int) $n['id'],
                    'channel'          => in_array($n['channel'] ?? 'cms', ['cms', 'email', 'both']) ? $n['channel'] : 'cms',
                ]);
            }
        }

        return redirect()->route('marble.workflow.edit', $workflow)
            ->with('success', trans('marble::admin.workflow_saved'));
    }

    public function delete(Workflow $workflow)
    {
        $workflow->steps()->each(function ($step) {
            $step->notifiables()->delete();
            $step->allowedGroups()->detach();
            $step->delete();
        });
        $workflow->delete();

        return redirect()->route('marble.workflow.index');
    }
}
