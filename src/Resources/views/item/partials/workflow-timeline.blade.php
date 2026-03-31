@php
    $workflow = $item->blueprint->workflow;
@endphp

@if($workflow)
@php
    $steps       = $workflow->steps;
    $currentId   = $item->current_workflow_step_id;
    $isPublished = $item->status === 'published';
    $prefix      = config('marble.route_prefix', 'admin');

    $currentStep = $currentId ? $steps->firstWhere('id', $currentId) : null;
    $canReject   = $currentStep && $currentStep->reject_enabled;

    $transitions = \Marble\Admin\Models\WorkflowTransition::where('item_id', $item->id)
        ->with(['user', 'fromStep', 'toStep'])
        ->latest('created_at')
        ->limit(10)
        ->get();
@endphp

<div class="main-box clearfix profile-box-menu">
    <div class="main-box-body clearfix">
        <div class="profile-box-header gray-bg clearfix" style="padding:0 15px 15px">
            <h2>{{ $workflow->name }}</h2>
        </div>
        <div class="profile-box-content clearfix" style="padding:12px 15px">

            {{-- Timeline --}}
            <div style="position:relative; padding-left:20px; margin-bottom:14px">
                <div style="position:absolute; left:7px; top:8px; bottom:8px; width:2px; background:#ddd;"></div>

                @foreach($steps as $step)
                    @php
                        $isCurrent = ($currentId === $step->id && !$isPublished);
                        $currentIdx = $currentId ? $steps->search(fn($s) => $s->id === $currentId) : ($isPublished ? $steps->count() : -1);
                        $thisIdx    = $steps->search(fn($s) => $s->id === $step->id);
                        $isPast     = $thisIdx < $currentIdx;
                        $dotColor   = $isCurrent ? '#337ab7' : ($isPast ? '#5cb85c' : '#ccc');
                        $textStyle  = $isCurrent ? 'font-weight:bold;color:#337ab7' : ($isPast ? 'color:#5cb85c' : 'color:#999');
                    @endphp
                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:10px; position:relative">
                        <div style="width:14px; height:14px; border-radius:50%; background:{{ $dotColor }}; flex-shrink:0; position:relative; z-index:1; margin-left:-6px"></div>
                        <span style="{{ $textStyle }}; font-size:13px">{{ $step->name }}</span>
                    </div>
                @endforeach

                @php
                    $pubDot  = $isPublished ? '#5cb85c' : '#ccc';
                    $pubText = $isPublished ? 'font-weight:bold;color:#5cb85c' : 'color:#999';
                @endphp
                <div style="display:flex; align-items:center; gap:8px; position:relative">
                    <div style="width:14px; height:14px; border-radius:50%; background:{{ $pubDot }}; flex-shrink:0; position:relative; z-index:1; margin-left:-6px"></div>
                    <span style="{{ $pubText }}; font-size:13px">{{ trans('marble::admin.published') }}</span>
                </div>
            </div>

            {{-- Advance button --}}
            @if(!$isPublished)
                @php
                    $next = $item->nextWorkflowStep();
                    $btnLabel = $next
                        ? trans('marble::admin.workflow_advance_to', ['step' => $next->name])
                        : trans('marble::admin.workflow_publish');
                @endphp
                <form method="POST" class="pull-right" action="{{ url("{$prefix}/item/workflow/advance/{$item->id}") }}">
                    @csrf
                    <button type="submit" class="btn btn-xs btn-primary btn-block">
                        {{ $btnLabel }} @include('marble::components.famicon', ['name' => 'arrow_right'])
                    </button>
                </form>
            @endif

            {{-- Retreat button (only when on step 2+, or published) --}}
            @php
                $canRetreat = $isPublished || (
                    $currentId && $steps->search(fn($s) => $s->id === $currentId) > 0
                );
            @endphp
            @if($canRetreat)
                <form method="POST" class="pull-left" action="{{ url("{$prefix}/item/workflow/retreat/{$item->id}") }}">
                    @csrf
                    <button type="submit" class="btn btn-xs btn-default btn-block">
                        @include('marble::components.famicon', ['name' => 'arrow_left']) {{ trans('marble::admin.workflow_retreat') }}
                    </button>
                </form>
            @endif

            {{-- Reject button --}}
            @if($canReject)
                <div style="clear:both; padding-top:10px">
                    <button type="button" class="btn btn-xs btn-block"
                            data-toggle="modal" data-target="#reject-modal-{{ $item->id }}">
                        @include('marble::components.famicon', ['name' => 'cancel'])
                        {{ trans('marble::admin.workflow_reject') }}
                        @if($currentStep->rejectToStep)
                            → {{ $currentStep->rejectToStep->name }}
                        @elseif($steps->search(fn($s) => $s->id === $currentId) > 0)
                            → {{ $steps->get($steps->search(fn($s) => $s->id === $currentId) - 1)->name }}
                        @endif
                    </button>
                </div>

                {{-- Reject modal --}}
                <div class="modal fade" id="reject-modal-{{ $item->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST" action="{{ url("{$prefix}/item/workflow/reject/{$item->id}") }}">
                                @csrf
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    <h4 class="modal-title">{{ trans('marble::admin.workflow_reject') }}</h4>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label>{{ trans('marble::admin.workflow_reject_comment') }}</label>
                                        <textarea name="comment" class="form-control" rows="3"
                                                  placeholder="{{ trans('marble::admin.workflow_reject_comment_hint') }}"></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('marble::admin.cancel') }}</button>
                                    <button type="submit" class="btn btn-danger">{{ trans('marble::admin.workflow_reject') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Transition log --}}
            @if($transitions->isNotEmpty())
                <div style="clear:both; margin-top:14px; padding-top:10px">
                    <small style="font-size:11px; text-transform:uppercase; color:#aaa; letter-spacing:.5px">{{ trans('marble::admin.workflow_history') }}</small>
                    @foreach($transitions as $t)
                        <div style="padding:5px 0; border-bottom:1px dotted #f0f0f0; font-size:12px">
                            <div style="display:flex; justify-content:space-between; align-items:baseline">
                                <span>
                                    @if($t->action === 'reject')
                                        <span style="color:#d9534f">@include('marble::components.famicon', ['name' => 'cancel'])</span>
                                    @else
                                        <span style="color:#5cb85c">@include('marble::components.famicon', ['name' => 'arrow_right'])</span>
                                    @endif
                                    <strong>{{ $t->user->name ?? '–' }}</strong>:
                                    {{ $t->fromLabel() }} → {{ $t->toLabel() }}
                                </span>
                                <small style="color:#aaa; white-space:nowrap; margin-left:8px">{{ $t->created_at->diffForHumans() }}</small>
                            </div>
                            @if($t->comment)
                                <div style="color:#888; margin-top:2px; padding-left:18px">{{ $t->comment }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

        </div>
    </div>
</div>
@endif
