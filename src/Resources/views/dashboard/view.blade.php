@extends('marble::layouts.app')

@section('sidebar')

    {{-- Stats --}}
    <div class="main-box">
        <header class="main-box-header clearfix">
            <h2>@include('marble::components.famicon', ['name' => 'chart_bar']) {{ trans('marble::admin.stats') }}</h2>
        </header>
        <div class="main-box-body clearfix">
            <table class="table"class="marble-table-flush">
                <tr>
                    <td class="text-muted">{{ trans('marble::admin.items_total') }}</td>
                    <td class="text-right"><strong>{{ $stats['items_total'] }}</strong></td>
                </tr>
                <tr>
                    <td class="text-muted">{{ trans('marble::admin.items_published') }}</td>
                    <td class="text-right"><span class="label label-success">{{ $stats['items_published'] }}</span></td>
                </tr>
                <tr>
                    <td class="text-muted">{{ trans('marble::admin.items_draft') }}</td>
                    <td class="text-right"><span class="label label-default">{{ $stats['items_draft'] }}</span></td>
                </tr>
                <tr>
                    <td class="text-muted">{{ trans('marble::admin.trash') }}</td>
                    <td class="text-right">
                        <a href="{{ route('marble.trash.index') }}" class="marble-color-inherit">
                            <span class="{{ $stats['trash_count'] > 0 ? 'label label-warning' : '' }}">{{ $stats['trash_count'] }}</span>
                        </a>
                    </td>
                </tr>
                <tr>
                    <td class="text-muted">{{ trans('marble::admin.media_files') }}</td>
                    <td class="text-right"><strong>{{ $stats['media_count'] }}</strong></td>
                </tr>
                <tr>
                    <td class="text-muted">{{ trans('marble::admin.users') }}</td>
                    <td class="text-right"><strong>{{ $stats['users_count'] }}</strong></td>
                </tr>
                @if($stats['unread_submissions'] > 0)
                <tr>
                    <td class="text-muted">{{ trans('marble::admin.unread_submissions') }}</td>
                    <td class="text-right"><span class="label label-primary">{{ $stats['unread_submissions'] }}</span></td>
                </tr>
                @endif
            </table>
        </div>
    </div>

    {{-- Unread form submissions --}}
    @if($unreadSubmissions->isNotEmpty())
    <div class="main-box">
        <header class="main-box-header clearfix">
            <h2>@include('marble::components.famicon', ['name' => 'report']) {{ trans('marble::admin.unread_submissions') }}</h2>
        </header>
        <div class="main-box-body clearfix">
            <table class="table table-hover"class="marble-table-flush">
                @foreach($unreadSubmissions as $sub)
                <tr onclick="window.location='{{ route('marble.form.show', [$sub->item, $sub]) }}'" >
                    <td>
                        <span class="label label-primary marble-mr-xs">new</span>
                        {{ $sub->item?->name() ?? '—' }}
                        <br><small class="text-muted">{{ $sub->created_at->diffForHumans() }}</small>
                    </td>
                </tr>
                @endforeach
            </table>
        </div>
    </div>
    @endif

    {{-- System info --}}
    <div class="main-box">
        <header class="main-box-header clearfix">
            <h2>@include('marble::components.famicon', ['name' => 'server']) {{ trans('marble::admin.system') }}</h2>
        </header>
        <div class="main-box-body clearfix">
            <table class="table"class="marble-table-flush">
                <tr>
                    <td class="text-muted">PHP</td>
                    <td class="text-right"><code>{{ PHP_VERSION }}</code></td>
                </tr>
                <tr>
                    <td class="text-muted">Laravel</td>
                    <td class="text-right"><code>{{ app()->version() }}</code></td>
                </tr>
                <tr>
                    <td class="text-muted">{{ trans('marble::admin.environment') }}</td>
                    <td class="text-right"><code>{{ app()->environment() }}</code></td>
                </tr>
            </table>
        </div>
    </div>

@endsection

@section('content')

    {{-- Greeting --}}
    @php
        $hour = now()->hour;
        $greeting = $hour < 12 ? trans('marble::admin.greeting_morning') : ($hour < 18 ? trans('marble::admin.greeting_afternoon') : trans('marble::admin.greeting_evening'));
        $userName = $currentUser?->name ? explode(' ', $currentUser->name)[0] : trans('marble::admin.greeting_fallback');
    @endphp
    <div class="dash-greeting">
        <div>
            <h1 class="dash-greeting-title">{{ $greeting }}, {{ $userName }}</h1>
            <p class="dash-greeting-date">{{ now()->format('l, F j, Y') }}</p>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="dash-stat-cards">
        <div class="dash-stat-card dash-stat-published">
            <div class="dash-stat-value">{{ $stats['items_published'] }}</div>
            <div class="dash-stat-label">{{ trans('marble::admin.items_published') }}</div>
        </div>
        <div class="dash-stat-card">
            <div class="dash-stat-value">{{ $stats['items_draft'] }}</div>
            <div class="dash-stat-label">{{ trans('marble::admin.items_draft') }}</div>
        </div>
        <div class="dash-stat-card {{ $stats['trash_count'] > 0 ? 'dash-stat-trash-warn' : '' }}">
            <a href="{{ route('marble.trash.index') }}" class="dash-stat-link">
                <div class="dash-stat-value">{{ $stats['trash_count'] }}</div>
                <div class="dash-stat-label">{{ trans('marble::admin.trash') }}</div>
            </a>
        </div>
        <div class="dash-stat-card">
            <div class="dash-stat-value">{{ $stats['media_count'] }}</div>
            <div class="dash-stat-label">{{ trans('marble::admin.media_files') }}</div>
        </div>
        @if($stats['unread_submissions'] > 0)
        <div class="dash-stat-card dash-stat-submissions">
            <div class="dash-stat-value">{{ $stats['unread_submissions'] }}</div>
            <div class="dash-stat-label">{{ trans('marble::admin.unread_submissions') }}</div>
        </div>
        @endif
    </div>

    {{-- My Assigned Tasks --}}
    @if($myAssignedTasks->isNotEmpty())
    <div class="main-box">
        <header class="main-box-header clearfix">
            <h2>@include('marble::components.famicon', ['name' => 'tick']) {{ trans('marble::admin.my_assigned_tasks') }}</h2>
        </header>
        <div class="main-box-body clearfix">
            <table class="table table-hover marble-table-flush marble-collab-tasks">
                @foreach($myAssignedTasks as $task)
                @php
                    $overdue = $task->due_date && $task->due_date->isPast();
                @endphp
                <tr onclick="window.location='{{ route('marble.item.edit', $task->item) }}#collaboration'" class="marble-clickable-row">
                    <td>
                        <strong>{{ $task->title }}</strong>
                        <br>
                        <small class="text-muted">
                            {{ $task->item->name() }}
                            @if($task->item->blueprint)
                                · {{ $task->item->blueprint->name }}
                            @endif
                            @if($task->creator)
                                · {{ trans('marble::admin.by') }} {{ $task->creator->name }}
                            @endif
                        </small>
                    </td>
                    <td class="text-right marble-vmid marble-nowrap">
                        @if($task->due_date)
                            <span class="label {{ $overdue ? 'label-danger' : 'label-default' }}">
                                {{ $task->due_date->format('d.m.Y') }}
                            </span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </table>
        </div>
    </div>
    @endif

    {{-- Quick Create --}}
    @if($quickCreateBlueprints->isNotEmpty())
    <div class="dash-quick-create">
        <span class="dash-quick-label">{{ trans('marble::admin.quick_create') }}</span>
        @foreach($quickCreateBlueprints as $bp)
            <a href="{{ route('marble.item.add', $quickCreateParentId) }}?blueprint={{ $bp->id }}" class="btn btn-xs btn-default dash-quick-btn">
                @include('marble::components.famicon', ['name' => $bp->icon ?: 'page_add'])
                {{ $bp->name }}
            </a>
        @endforeach
    </div>
    @endif

    {{-- My Pending Items (workflow) --}}
    @if($myPendingItems->isNotEmpty())
    <div class="main-box">
        <header class="main-box-header clearfix">
            <h2>@include('marble::components.famicon', ['name' => 'hourglass']) {{ trans('marble::admin.my_pending_items') }}</h2>
        </header>
        <div class="main-box-body clearfix">
            <table class="table table-hover marble-table-flush">
                @foreach($myPendingItems as $pending)
                <tr onclick="window.location='{{ route('marble.item.edit', $pending) }}'" class="marble-clickable-row">
                    <td>
                        {{ $pending->name() ?: '—' }}
                        @if($pending->blueprint)
                            <br><small class="text-muted">{{ $pending->blueprint->name }}</small>
                        @endif
                    </td>
                    <td class="text-right marble-vmid marble-nowrap">
                        <span class="label label-warning">{{ $pending->workflowStep->name }}</span>
                    </td>
                </tr>
                @endforeach
            </table>
        </div>
    </div>
    @endif

    {{-- My Drafts --}}
    @if($myDrafts->isNotEmpty())
    <div class="main-box">
        <header class="main-box-header clearfix">
            <h2>@include('marble::components.famicon', ['name' => 'pencil']) {{ trans('marble::admin.my_drafts') }}</h2>
        </header>
        <div class="main-box-body clearfix">
            <table class="table table-hover marble-table-flush">
                @foreach($myDrafts as $draft)
                <tr onclick="window.location='{{ route('marble.item.edit', $draft) }}'" class="marble-clickable-row">
                    <td>
                        {{ $draft->name() ?: '—' }}
                        @if($draft->blueprint)
                            <br><small class="text-muted">{{ $draft->blueprint->name }}</small>
                        @endif
                    </td>
                    <td class="text-right text-muted marble-text-sm marble-td-meta">
                        {{ $draft->updated_at->diffForHumans() }}
                    </td>
                </tr>
                @endforeach
            </table>
        </div>
    </div>
    @endif

    {{-- Workflow Deadlines — prominent if any overdue --}}
    @if($deadlineItems->isNotEmpty())
    <div class="main-box {{ $deadlineItems->where('days_left', '<', 0)->isNotEmpty() ? 'dash-box-urgent' : '' }}">
        <header class="main-box-header clearfix">
            <h2>@include('marble::components.famicon', ['name' => 'clock']) {{ trans('marble::admin.workflow_deadlines') }}</h2>
        </header>
        <div class="main-box-body clearfix">
            <table class="table table-hover marble-table-flush">
                @foreach($deadlineItems as $entry)
                @php
                    $daysLeft = $entry['days_left'];
                    $item     = $entry['item'];
                    if ($daysLeft < 0) {
                        $badgeClass = 'label-danger';
                        $label      = trans('marble::admin.workflow_overdue');
                    } elseif ($daysLeft <= 1) {
                        $badgeClass = 'label-danger';
                        $label      = $daysLeft === 0
                            ? trans('marble::admin.deadline_today')
                            : trans('marble::admin.deadline_days_left', ['days' => $daysLeft]);
                    } elseif ($daysLeft <= 3) {
                        $badgeClass = 'label-warning';
                        $label      = trans('marble::admin.deadline_days_left', ['days' => $daysLeft]);
                    } else {
                        $badgeClass = 'label-success';
                        $label      = trans('marble::admin.deadline_days_left', ['days' => $daysLeft]);
                    }
                @endphp
                <tr onclick="window.location='{{ route('marble.item.edit', $item) }}'" class="marble-clickable-row">
                    <td>
                        {{ $item->name() ?: '—' }}
                        <br>
                        <small class="text-muted">
                            {{ $item->workflowStep->name }}
                            @if($item->blueprint)· {{ $item->blueprint->name }}@endif
                        </small>
                    </td>
                    <td class="text-right marble-vmid marble-nowrap">
                        <span class="label {{ $badgeClass }}">{{ $label }}</span>
                    </td>
                </tr>
                @endforeach
            </table>
        </div>
    </div>
    @endif

    {{-- Watched items --}}
    @if($watchedItems->isNotEmpty())
    <div class="main-box">
        <header class="main-box-header clearfix">
            <h2>@include('marble::components.famicon', ['name' => 'bell']) {{ trans('marble::admin.subscription_watch') }}</h2>
        </header>
        <div class="main-box-body clearfix">
            <table class="table table-hover marble-table-flush">
                @foreach($watchedItems as $watched)
                <tr onclick="window.location='{{ route('marble.item.edit', $watched) }}'" class="marble-clickable-row">
                    <td>
                        <span class="label {{ $watched->status === 'published' ? 'label-success' : ($watched->status === 'draft' ? 'label-default' : 'label-warning') }} marble-mr-xs">
                            {{ $watched->status }}
                        </span>
                        {{ $watched->name() ?: '—' }}
                        @if($watched->blueprint)
                            <br><small class="text-muted">{{ $watched->blueprint->name }}</small>
                        @endif
                    </td>
                    <td class="text-right text-muted marble-text-sm marble-td-meta">
                        {{ $watched->updated_at->diffForHumans() }}
                    </td>
                </tr>
                @endforeach
            </table>
        </div>
    </div>
    @endif

    {{-- Activity Feed --}}
    <div class="main-box">
        <header class="main-box-header clearfix">
            <h2>
                @include('marble::components.famicon', ['name' => 'time']) {{ trans('marble::admin.activity_feed') }}
                <span class="pull-right">
                    <a href="{{ route('marble.activity-log.index') }}" class="btn btn-xs btn-default">{{ trans('marble::admin.list') }}</a>
                </span>
            </h2>
        </header>
        <div class="main-box-body clearfix">
            @if($recentActivity->isEmpty())
                <p class="text-muted marble-mt-xs marble-mb-xs">{{ trans('marble::admin.no_activity') }}</p>
            @else
                @php
                    $actionMap = [
                        'item.created'    => 'activity_created',
                        'item.saved'      => 'activity_saved',
                        'item.deleted'    => 'activity_deleted',
                        'item.published'  => 'activity_published',
                        'item.draft'      => 'activity_draft',
                        'item.duplicated' => 'activity_duplicated',
                        'item.moved'      => 'activity_moved',
                        'item.reverted'   => 'activity_reverted',
                    ];
                    $actionIcon = [
                        'item.created'    => 'add',
                        'item.saved'      => 'pencil',
                        'item.deleted'    => 'bin',
                        'item.published'  => 'tick',
                        'item.draft'      => 'cross',
                        'item.duplicated' => 'page_copy',
                        'item.moved'      => 'arrow_right',
                        'item.reverted'   => 'arrow_undo',
                    ];
                    $actionColor = [
                        'item.created'    => 'dash-act-blue',
                        'item.saved'      => 'dash-act-blue',
                        'item.deleted'    => 'dash-act-red',
                        'item.published'  => 'dash-act-green',
                        'item.draft'      => 'dash-act-grey',
                        'item.duplicated' => 'dash-act-blue',
                        'item.moved'      => 'dash-act-blue',
                        'item.reverted'   => 'dash-act-orange',
                    ];
                @endphp
                <table class="table table-hover marble-table-flush">
                    <tbody>
                        @foreach($recentActivity as $entry)
                        <tr @if($entry->item) onclick="window.location='{{ route('marble.item.edit', $entry->item_id) }}'" class="marble-clickable-row" @endif>
                            <td class="marble-icon-cell">
                                <span class="dash-act-icon {{ $actionColor[$entry->action] ?? 'dash-act-blue' }}">
                                    @include('marble::components.famicon', ['name' => $actionIcon[$entry->action] ?? 'bullet_go'])
                                </span>
                            </td>
                            <td>
                                <strong>{{ $entry->item_name ?: '—' }}</strong>
                                @if($entry->item?->blueprint)
                                    <small class="text-muted marble-mr-xs">{{ $entry->item->blueprint->name }}</small>
                                @endif
                                <br>
                                <small class="text-muted">
                                    {{ trans('marble::admin.' . ($actionMap[$entry->action] ?? 'activity_saved')) }}
                                    @if($entry->user)
                                        {{ trans('marble::admin.by') }} <strong>{{ $entry->user->name }}</strong>
                                    @endif
                                    · {{ $entry->created_at->diffForHumans() }}
                                </small>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @if($recentActivity->hasPages())
                    <div class="marble-box-body">{{ $recentActivity->links() }}</div>
                @endif
            @endif
        </div>
    </div>

    {{-- Upcoming Scheduled --}}
    @if($upcomingItems->isNotEmpty())
    <div class="main-box">
        <header class="main-box-header clearfix">
            <h2>@include('marble::components.famicon', ['name' => 'clock']) {{ trans('marble::admin.upcoming') }}</h2>
        </header>
        <div class="main-box-body clearfix">
            <table class="table table-hover marble-table-flush">
                @foreach($upcomingItems as $upcoming)
                <tr onclick="window.location='{{ route('marble.item.edit', $upcoming) }}'" class="marble-clickable-row">
                    <td>
                        {{ $upcoming->name() ?: '—' }}
                        <br><small class="text-muted">
                            @if($upcoming->published_at && $upcoming->published_at->isFuture())
                                {{ trans('marble::admin.publishes') }} {{ $upcoming->published_at->diffForHumans() }}
                            @elseif($upcoming->expires_at && $upcoming->expires_at->isFuture())
                                {{ trans('marble::admin.expires') }} {{ $upcoming->expires_at->diffForHumans() }}
                            @endif
                        </small>
                    </td>
                </tr>
                @endforeach
            </table>
        </div>
    </div>
    @endif

@endsection
