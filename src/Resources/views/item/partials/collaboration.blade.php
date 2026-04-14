<div class="main-box" id="collaboration">
    <header class="main-box-header clearfix">
        <h2>@include('marble::components.famicon', ['name' => 'group']) {{ trans('marble::admin.collaboration') }}</h2>
    </header>
    <div class="main-box-body clearfix marble-collab-body">

        {{-- Tasks --}}
        <h4 class="marble-collab-section-title">@include('marble::components.famicon', ['name' => 'tick']) {{ trans('marble::admin.tasks') }}</h4>

        @if($collaborationTasks->isNotEmpty())
        <div>
                <table class="table marble-table-flush marble-collab-tasks">
                @foreach($collaborationTasks as $task)
                <tr class="{{ $task->done ? 'marble-task-done' : '' }}">
                    <td class="marble-task-check-cell">
                        <form action="{{ route('marble.item.task.toggle', $task) }}" method="post" style="display:inline">
                            @csrf
                            <button type="submit" class="btn btn-xs marble-task-toggle {{ $task->done ? 'marble-task-toggle-done' : 'marble-task-toggle-open' }}" title="{{ $task->done ? trans('marble::admin.mark_undone') : trans('marble::admin.mark_done') }}">
                                @include('marble::components.famicon', ['name' => 'tick'])
                            </button>
                        </form>
                    </td>
                    <td class="marble-task-title {{ $task->done ? 'marble-strikethrough' : '' }}">
                        {{ $task->title }}
                        @if($task->assignee)
                            <span class="marble-task-assignee">{{ $task->assignee->name }}</span>
                        @endif
                        @if($task->due_date)
                            <small class="text-muted {{ $task->due_date->isPast() && !$task->done ? 'text-danger' : '' }}">
                                · {{ $task->due_date->format('d.m.Y') }}
                            </small>
                        @endif
                    </td>
                    <td class="text-right marble-td-meta">
                        <form action="{{ route('marble.item.task.destroy', $task) }}" method="post" style="display:inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-xs btn-link text-danger" onclick="return confirm('{{ trans('marble::admin.are_you_sure') }}')">
                                @include('marble::components.famicon', ['name' => 'bin'])
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </table>
        </div>
        @else
        <p class="text-muted marble-mt-xs">{{ trans('marble::admin.no_tasks') }}</p>
        @endif

        <form action="{{ route('marble.item.task.store', $item) }}" method="post" class="marble-collab-add-form">
            @csrf
            <div class="marble-collab-task-row">
                <input type="text" name="title" class="form-control" placeholder="{{ trans('marble::admin.task_title_placeholder') }}" required />
                <select name="assigned_to" class="form-control marble-collab-assign-select">
                    <option value="">{{ trans('marble::admin.assign_to_nobody') }}</option>
                    @foreach($collaborationUsers as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </select>
                <input type="date" name="due_date" class="form-control marble-collab-due" />
                <button type="submit" class="btn btn-default">@include('marble::components.famicon', ['name' => 'add']) {{ trans('marble::admin.add_task') }}</button>
            </div>
        </form>

        <hr class="marble-collab-divider" />

        {{-- Comments --}}
        <h4 class="marble-collab-section-title">@include('marble::components.famicon', ['name' => 'comment']) {{ trans('marble::admin.comments') }}</h4>

        @foreach($collaborationComments as $comment)
        <div class="marble-comment">
            <div class="marble-comment-meta">
                <strong>{{ $comment->user?->name ?? trans('marble::admin.deleted_user') }}</strong>
                <span class="text-muted marble-ml-xs">{{ $comment->created_at->diffForHumans() }}</span>
                @if($comment->user_id === $currentUser?->id)
                <form action="{{ route('marble.item.comment.destroy', $comment) }}" method="post" style="display:inline; float:right">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-xs btn-link text-danger">@include('marble::components.famicon', ['name' => 'bin'])</button>
                </form>
                @endif
            </div>
            <div class="marble-comment-body">{{ $comment->body }}</div>
        </div>
        @endforeach

        @if($collaborationComments->isEmpty())
        <p class="text-muted marble-mt-xs">{{ trans('marble::admin.no_comments') }}</p>
        @endif

        <form action="{{ route('marble.item.comment.store', $item) }}" method="post" class="marble-collab-add-form marble-mt-sm">
            @csrf
            <div class="form-group">
                <textarea name="body" class="form-control" rows="3" placeholder="{{ trans('marble::admin.comment_placeholder') }}" required></textarea>
            </div>
            <div class="text-right">
                <button type="submit" class="btn btn-default">@include('marble::components.famicon', ['name' => 'comment_add']) {{ trans('marble::admin.add_comment') }}</button>
            </div>
        </form>

    </div>
</div>
