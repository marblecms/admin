@php
    $stepId       = $step?->id ?? '';
    $stepName     = $step?->name ?? '';
    $rejectEnabled = $step?->reject_enabled ?? false;
    $rejectToId   = $step?->reject_to_step_id ?? '';
    $existingNotifiables = $step?->notifiables ?? collect();
    $allowedGroupIds = $step ? $step->allowedGroups->pluck('id')->toArray() : [];
@endphp

<div class="step-row" style="margin-bottom:8px; border:1px solid #e0e0e0; border-radius:3px; background:#fff">

    {{-- Header row --}}
    <div style="display:flex; align-items:center; gap:8px; padding:8px 10px">
        <span class="step-handle" style="cursor:grab; color:#aaa; padding:0 4px; font-size:16px">&#9776;</span>

        <input type="hidden" name="steps[{{ $idx }}][id]" data-step-field="id" value="{{ $stepId }}" />

        <input type="text"
               name="steps[{{ $idx }}][name]"
               data-step-field="name"
               value="{{ $stepName }}"
               class="form-control input-sm"
               style="flex:1"
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
    <div class="step-config-panel" style="{{ ($rejectEnabled || count($allowedGroupIds) || $existingNotifiables->count()) ? '' : 'display:none' }}; border-top:1px solid #eee; padding:12px 16px; background:#fafafa">

        <div class="row">
            {{-- Left: Notifications --}}
            <div class="col-md-6">
                <strong style="font-size:12px; text-transform:uppercase; color:#888; letter-spacing:.5px">
                    {{ trans('marble::admin.workflow_notify') }}
                </strong>
                <small class="text-muted" style="display:block; margin-bottom:8px">{{ trans('marble::admin.workflow_notify_hint') }}</small>

                <div class="notifiables-list" style="margin-bottom:8px">
                    @foreach($existingNotifiables as $n)
                        <div class="notifiable-row" style="display:flex; align-items:center; gap:6px; margin-bottom:6px">
                            <select class="form-control input-sm" style="width:70px"
                                    name="steps[{{ $idx }}][notifiables][{{ $loop->index }}][type]"
                                    data-notifiable-field="type">
                                <option value="user" {{ $n->notifiable_type === 'user' ? 'selected' : '' }}>{{ trans('marble::admin.user') }}</option>
                                <option value="group" {{ $n->notifiable_type === 'group' ? 'selected' : '' }}>{{ trans('marble::admin.usergroup_singular') }}</option>
                            </select>
                            <select class="form-control input-sm" style="flex:1"
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
                            <select class="form-control input-sm" style="width:80px"
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
                <strong style="font-size:12px; text-transform:uppercase; color:#888; letter-spacing:.5px">
                    {{ trans('marble::admin.workflow_allowed_groups') }}
                </strong>
                <small class="text-muted" style="display:block; margin-bottom:8px">{{ trans('marble::admin.workflow_allowed_groups_hint') }}</small>

                @foreach($allGroups as $g)
                    <label style="display:flex; align-items:center; gap:6px; font-weight:normal; margin-bottom:4px">
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

                {{-- Reject --}}
                <div style="margin-top:16px">
                    <label style="display:flex; align-items:center; gap:8px; font-weight:normal">
                        <input type="hidden"   name="steps[{{ $idx }}][reject_enabled]" data-step-field="reject_enabled" value="0" />
                        <input type="checkbox" name="steps[{{ $idx }}][reject_enabled]" data-step-field="reject_enabled"
                               value="1" class="reject-toggle"
                               {{ $rejectEnabled ? 'checked' : '' }} />
                        <strong>{{ trans('marble::admin.workflow_reject_enabled') }}</strong>
                    </label>
                    <small class="text-muted" style="display:block; margin-bottom:8px">{{ trans('marble::admin.workflow_reject_hint') }}</small>

                    <div class="reject-options" style="{{ $rejectEnabled ? '' : 'display:none' }}">
                        <label style="font-size:12px; margin-bottom:4px">{{ trans('marble::admin.workflow_reject_to') }}</label>
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
