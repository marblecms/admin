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
        <div class="profile-box-header gray-bg clearfix">
            <h2>{{ $workflow->name }}</h2>
        </div>
        <div class="profile-box-content clearfix marble-box-body">

            {{-- Timeline --}}
            <div class="marble-wf-timeline">
                <div class="marble-wf-line"></div>

                @foreach($steps as $step)
                    @php
                        $isCurrent  = ($currentId === $step->id && !$isPublished);
                        $currentIdx = $currentId ? $steps->search(fn($s) => $s->id === $currentId) : ($isPublished ? $steps->count() : -1);
                        $thisIdx    = $steps->search(fn($s) => $s->id === $step->id);
                        $isPast     = $thisIdx < $currentIdx;
                        $isOverdue  = $isCurrent && $item->isWorkflowOverdue();
                        $dotClass   = $isOverdue ? 'marble-wf-dot-overdue' : ($isCurrent ? 'marble-wf-dot-current' : ($isPast ? 'marble-wf-dot-done' : 'marble-wf-dot-future'));
                        $textClass  = $isOverdue ? 'marble-wf-text-overdue' : ($isCurrent ? 'marble-wf-text-current' : ($isPast ? 'marble-wf-text-done' : 'marble-wf-text-future'));
                    @endphp
                    <div class="marble-wf-step">
                        <div class="marble-wf-dot {{ $dotClass }}"></div>
                        <span class="{{ $textClass }}">{{ $step->name }}</span>
                        @if($isOverdue)
                            <span class="marble-wf-badge-overdue">{{ trans('marble::admin.workflow_overdue') }}</span>
                        @elseif($isCurrent && $step->deadline_days)
                            @php $daysLeft = (int) now()->diffInDays($item->workflow_step_entered_at?->addDays($step->deadline_days), false); @endphp
                            @if($daysLeft >= 0)
                                <span class="marble-wf-days-left">{{ $daysLeft }}d left</span>
                            @endif
                        @endif
                    </div>
                @endforeach

                <div class="marble-wf-step">
                    <div class="marble-wf-dot {{ $isPublished ? 'marble-wf-dot-done' : 'marble-wf-dot-future' }}"></div>
                    <span class="{{ $isPublished ? 'marble-wf-text-done' : 'marble-wf-text-future' }}">{{ trans('marble::admin.published') }}</span>
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
                <div class="marble-wf-reject-wrap">
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

            @push('modals')
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
            @endpush
            @endif

            {{-- Transition log --}}
            @if($transitions->isNotEmpty())
                <div class="marble-wf-history">
                    <small class="marble-wf-history-label">{{ trans('marble::admin.workflow_history') }}</small>
                    @foreach($transitions as $t)
                        <div class="marble-wf-history-row">
                            <div class="marble-flex-between">
                                <span>
                                    @if($t->action === 'reject')
                                        <span class="marble-wf-text-overdue">@include('marble::components.famicon', ['name' => 'cancel'])</span>
                                    @else
                                        <span class="marble-wf-text-done">@include('marble::components.famicon', ['name' => 'arrow_right'])</span>
                                    @endif
                                    <strong>{{ $t->user->name ?? '–' }}</strong>:
                                    {{ $t->fromLabel() }} → {{ $t->toLabel() }}
                                </span>
                                <small class="marble-meta marble-nowrap marble-mr-xs">{{ $t->created_at->diffForHumans() }}</small>
                            </div>
                            @if($t->comment)
                                <div class="marble-wf-comment">{{ $t->comment }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

        </div>
    </div>
</div>
@endif
