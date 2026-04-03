@php
    $stepId          = $step?->id ?? '';
    $stepName        = $step?->name ?? '';
    $rejectEnabled   = $step?->reject_enabled ?? false;
    $rejectToId      = $step?->reject_to_step_id ?? '';
    $deadlineDays    = $step?->deadline_days ?? '';
    $existingNotifiables = $step?->notifiables ?? collect();
    $allowedGroupIds = $step ? $step->allowedGroups->pluck('id')->toArray() : [];
@endphp

<div class="step-row marble-step-row">

    {{-- Header row --}}
    <div class="marble-step-header">
        <span class="step-handle marble-step-handle">&#9776;</span>

        <input type="hidden" name="steps[{{ $idx }}][id]" data-step-field="id" value="{{ $stepId }}" />

        <input type="text"
               name="steps[{{ $idx }}][name]"
               data-step-field="name"
               value="{{ $stepName }}"
               class="form-control input-sm marble-flex-1"
               placeholder="{{ trans('marble::admin.workflow_step_name') }}"
               required />

        <button type="button" class="btn btn-xs btn-default toggle-step-config" title="{{ trans('marble::admin.workflow_configure_step') }}">
            <span>{{ ($rejectEnabled || count($allowedGroupIds) || $existingNotifiables->count()) ? '▾' : '▸' }}</span>
            {{ trans('marble::admin.workflow_configure_step') }}
        </button>

        <button type="button" class="btn btn-xs btn-danger remove-step">
            @include('marble::components.famicon', ['name' => 'bin'])
        </button>
    </div>

    {{-- Config panel --}}
    <div class="step-config-panel marble-step-config{{ ($rejectEnabled || count($allowedGroupIds) || $existingNotifiables->count()) ? '' : ' marble-hidden' }}">

        <div class="row">
            {{-- Left: Notifications --}}
            <div class="col-md-6">
                <strong class="marble-section-label">{{ trans('marble::admin.workflow_notify') }}</strong>
                <small class="text-muted marble-block marble-mb-sm">{{ trans('marble::admin.workflow_notify_hint') }}</small>

                <div class="notifiables-list marble-mb-sm">
                    @foreach($existingNotifiables as $n)
                        <div class="notifiable-row marble-notifiable-row">
                            <select class="form-control input-sm marble-input-num-xs"
                                    name="steps[{{ $idx }}][notifiables][{{ $loop->index }}][type]"
                                    data-notifiable-field="type">
                                <option value="user" {{ $n->notifiable_type === 'user' ? 'selected' : '' }}>{{ trans('marble::admin.user') }}</option>
                                <option value="group" {{ $n->notifiable_type === 'group' ? 'selected' : '' }}>{{ trans('marble::admin.usergroup_singular') }}</option>
                            </select>
                            <select class="form-control input-sm marble-flex-1"
                                    name="steps[{{ $idx }}][notifiables][{{ $loop->index }}][id]"
                                    data-notifiable-field="id">
                                @if($n->notifiable_type === 'user')
                                    @foreach($allUsers as $u)
                                        <option value="{{ $u->id }}" {{ $u->id == $n->notifiable_id ? 'selected' : '' }}>{{ $u->name }}</option>
                                    @endforeach
                                @else
                                    @foreach($allGroups as $g)
                                        <option value="{{ $g->id }}" {{ $g->id == $n->notifiable_id ? 'selected' : '' }}>{{ $g->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <select class="form-control input-sm marble-input-num-sm"
                                    name="steps[{{ $idx }}][notifiables][{{ $loop->index }}][channel]"
                                    data-notifiable-field="channel">
                                <option value="cms"   {{ $n->channel === 'cms'   ? 'selected' : '' }}>CMS</option>
                                <option value="email" {{ $n->channel === 'email' ? 'selected' : '' }}>Email</option>
                                <option value="both"  {{ $n->channel === 'both'  ? 'selected' : '' }}>Both</option>
                            </select>
                            <button type="button" class="btn btn-xs btn-danger remove-notifiable">✕</button>
                        </div>
                    @endforeach
                </div>

                <button type="button" class="btn btn-xs btn-default add-notifiable">
                    + {{ trans('marble::admin.workflow_add_notifiable') }}
                </button>
            </div>

            {{-- Right: Permissions + Reject --}}
            <div class="col-md-6">

                {{-- Permissions --}}
                <strong class="marble-section-label">{{ trans('marble::admin.workflow_allowed_groups') }}</strong>
                <small class="text-muted marble-block marble-mb-sm">{{ trans('marble::admin.workflow_allowed_groups_hint') }}</small>

                @foreach($allGroups as $g)
                    <label class="marble-check-label marble-mb-xs">
                        <input type="checkbox"
                               name="steps[{{ $idx }}][allowed_groups][]"
                               data-step-field="allowed_groups[]"
                               value="{{ $g->id }}"
                               {{ in_array($g->id, $allowedGroupIds) ? 'checked' : '' }} />
                        {{ $g->name }}
                    </label>
                @endforeach
                @if($allGroups->isEmpty())
                    <small class="text-muted">{{ trans('marble::admin.workflow_no_groups') }}</small>
                @endif

                {{-- Deadline --}}
                <div class="marble-mt-md">
                    <strong class="marble-section-label">{{ trans('marble::admin.workflow_deadline') }}</strong>
                    <small class="text-muted marble-block marble-mb-xs">{{ trans('marble::admin.workflow_deadline_hint') }}</small>
                    <div class="marble-flex-center-sm">
                        <input type="number"
                               name="steps[{{ $idx }}][deadline_days]"
                               data-step-field="deadline_days"
                               value="{{ $deadlineDays }}"
                               class="form-control input-sm marble-input-num-sm"
                               min="1" max="3650"
                               placeholder="–" />
                        <span class="text-muted marble-text-sm">{{ trans('marble::admin.workflow_deadline_days') }}</span>
                    </div>
                </div>

                {{-- Reject --}}
                <div class="marble-mt-md">
                    <label class="marble-check-label">
                        <input type="hidden"   name="steps[{{ $idx }}][reject_enabled]" data-step-field="reject_enabled" value="0" />
                        <input type="checkbox" name="steps[{{ $idx }}][reject_enabled]" data-step-field="reject_enabled"
                               value="1" class="reject-toggle"
                               {{ $rejectEnabled ? 'checked' : '' }} />
                        <strong>{{ trans('marble::admin.workflow_reject_enabled') }}</strong>
                    </label>
                    <small class="text-muted marble-block marble-mb-sm">{{ trans('marble::admin.workflow_reject_hint') }}</small>

                    <div class="reject-options{{ $rejectEnabled ? '' : ' marble-hidden' }}">
                        <label class="marble-text-sm marble-mb-xs">{{ trans('marble::admin.workflow_reject_to') }}</label>
                        <select name="steps[{{ $idx }}][reject_to_step_id]" data-step-field="reject_to_step_id" class="form-control input-sm">
                            <option value="">— {{ trans('marble::admin.workflow_reject_to_prev') }} —</option>
                            @if(isset($allSteps))
                                @foreach($allSteps as $s)
                                    @if($s->id !== $stepId)
                                        <option value="{{ $s->id }}" {{ $rejectToId == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                                    @endif
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
