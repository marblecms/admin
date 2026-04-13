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
    <h1>Dashboard</h1>

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
                @endphp
                <table class="table table-hover"class="marble-table-flush">
                    <tbody>
                        @foreach($recentActivity as $entry)
                        <tr @if($entry->item) onclick="window.location='{{ route('marble.item.edit', $entry->item_id) }}'"  @endif>
                            <td class="marble-icon-cell">
                                @include('marble::components.famicon', ['name' => $actionIcon[$entry->action] ?? 'bullet_go'])
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

    {{-- Workflow Deadlines + Upcoming Scheduled --}}
    <div class="row">
        <div class="col-md-6">
            @if($deadlineItems->isNotEmpty())
            <div class="main-box">
                <header class="main-box-header clearfix">
                    <h2>@include('marble::components.famicon', ['name' => 'clock']) {{ trans('marble::admin.workflow_deadlines') }}</h2>
                </header>
                <div class="main-box-body clearfix">
                    <table class="table table-hover">
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
                        <tr onclick="window.location='{{ route('marble.item.edit', $item) }}'" >
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
        </div>
        <div class="col-md-6">
            <div class="main-box">
                <header class="main-box-header clearfix">
                    <h2>@include('marble::components.famicon', ['name' => 'clock']) {{ trans('marble::admin.upcoming') }}</h2>
                </header>
                <div class="main-box-body clearfix">
                    @if($upcomingItems->isEmpty())
                        <p class="text-muted marble-box-body marble-mb-0 marble-text-sm">{{ trans('marble::admin.nothing_scheduled') }}</p>
                    @else
                        <table class="table table-hover">
                            @foreach($upcomingItems as $upcoming)
                            <tr onclick="window.location='{{ route('marble.item.edit', $upcoming) }}'" >
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
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Admin Blocks --}}
    <div class="row">
        @if($currentUser && $currentUser->can('list_blueprints'))
        <div class="col-md-6">
            <div class="main-box">
                <header class="main-box-header clearfix">
                    <h2>
                        {{ trans('marble::admin.classes') }}
                        <span class="pull-right">
                            <a href="{{ route('marble.blueprint.index') }}" class="btn btn-xs btn-default">{{ trans('marble::admin.list') }}</a>
                        </span>
                    </h2>
                </header>
                <div class="main-box-body clearfix">
                    @if($blueprints->isEmpty())
                        <p class="text-muted text-center marble-empty-state marble-mb-0">{{ trans('marble::admin.no_blueprints') }}</p>
                    @else
                    <table class="table table-hover"class="marble-table-flush">
                        <tbody>
                            @foreach($blueprints as $blueprint)
                            <tr onclick="window.location='{{ route('marble.blueprint.edit', $blueprint) }}'" >
                                <td class="marble-icon-cell">
                                    @include('marble::components.famicon', ['name' => $blueprint->icon ?: 'brick'])
                                </td>
                                <td>{{ $blueprint->name }}</td>
                                <td class="text-right" onclick="event.stopPropagation()">
                                    <a href="{{ route('marble.blueprint.field.edit', $blueprint) }}" class="btn btn-default btn-xs">
                                        @include('marble::components.famicon', ['name' => 'application_form'])
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif
                </div>
            </div>
        </div>
        @endif

        @if($currentUser && $currentUser->can('list_users'))
        <div class="col-md-6">
            <div class="main-box">
                <header class="main-box-header clearfix">
                    <h2>
                        {{ trans('marble::admin.users') }}
                        <span class="pull-right">
                            <a href="{{ route('marble.user.index') }}" class="btn btn-xs btn-default">{{ trans('marble::admin.list') }}</a>
                        </span>
                    </h2>
                </header>
                <div class="main-box-body clearfix">
                    <table class="table table-hover"class="marble-table-flush">
                        <tbody>
                            @foreach($users as $user)
                            <tr onclick="window.location='{{ route('marble.user.edit', $user) }}'" >
                                <td class="marble-icon-cell">
                                    @include('marble::components.famicon', ['name' => 'status_online'])
                                </td>
                                <td>
                                    {{ $user->name }}
                                    <small class="text-muted marble-mr-xs">{{ $user->email }}</small>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
@endsection
