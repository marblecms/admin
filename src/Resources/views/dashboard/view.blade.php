@extends('marble::layouts.app')

@section('sidebar')

    {{-- Stats --}}
    <div class="main-box">
        <header class="main-box-header clearfix">
            <h2>@include('marble::components.famicon', ['name' => 'chart_bar']) {{ trans('marble::admin.stats') }}</h2>
        </header>
        <div class="main-box-body clearfix">
            <table class="table" style="margin-bottom:0">
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
                        <a href="{{ route('marble.trash.index') }}" style="color:inherit">
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

    {{-- Upcoming scheduled --}}
    <div class="main-box">
        <header class="main-box-header clearfix">
            <h2>@include('marble::components.famicon', ['name' => 'clock']) {{ trans('marble::admin.upcoming') }}</h2>
        </header>
        <div class="main-box-body clearfix">
            @if($upcomingItems->isEmpty())
                <p class="text-muted" style="padding:10px 15px;margin:0;font-size:12px">{{ trans('marble::admin.nothing_scheduled') }}</p>
            @else
                <table class="table table-hover" style="margin-bottom:0">
                    @foreach($upcomingItems as $upcoming)
                    <tr onclick="window.location='{{ route('marble.item.edit', $upcoming) }}'" style="cursor:pointer">
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

    {{-- Unread form submissions --}}
    @if($unreadSubmissions->isNotEmpty())
    <div class="main-box">
        <header class="main-box-header clearfix">
            <h2>@include('marble::components.famicon', ['name' => 'report']) {{ trans('marble::admin.unread_submissions') }}</h2>
        </header>
        <div class="main-box-body clearfix">
            <table class="table table-hover" style="margin-bottom:0">
                @foreach($unreadSubmissions as $sub)
                <tr onclick="window.location='{{ route('marble.form.show', [$sub->item, $sub]) }}'" style="cursor:pointer">
                    <td>
                        <span class="label label-primary" style="margin-right:4px">new</span>
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
            <table class="table" style="margin-bottom:0">
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
                <p class="text-muted" style="padding:10px 0">{{ trans('marble::admin.no_activity') }}</p>
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
                <table class="table table-hover" style="margin-bottom:0">
                    <tbody>
                        @foreach($recentActivity as $entry)
                        <tr @if($entry->item) onclick="window.location='{{ route('marble.item.edit', $entry->item_id) }}'" style="cursor:pointer" @endif>
                            <td style="width:20px;padding-right:0;color:#999">
                                @include('marble::components.famicon', ['name' => $actionIcon[$entry->action] ?? 'bullet_go'])
                            </td>
                            <td>
                                <strong>{{ $entry->item_name ?: '—' }}</strong>
                                @if($entry->item?->blueprint)
                                    <small class="text-muted" style="margin-left:4px">{{ $entry->item->blueprint->name }}</small>
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
                    <div style="padding:10px 15px">{{ $recentActivity->links() }}</div>
                @endif
            @endif
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
                        <p class="text-muted" style="padding:20px 0;text-align:center;margin:0">{{ trans('marble::admin.no_blueprints') }}</p>
                    @else
                    <table class="table table-hover" style="margin-bottom:0">
                        <tbody>
                            @foreach($blueprints as $blueprint)
                            <tr onclick="window.location='{{ route('marble.blueprint.edit', $blueprint) }}'" style="cursor:pointer">
                                <td style="width:24px;padding-right:0">
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
                    <table class="table table-hover" style="margin-bottom:0">
                        <tbody>
                            @foreach($users as $user)
                            <tr onclick="window.location='{{ route('marble.user.edit', $user) }}'" style="cursor:pointer">
                                <td style="width:20px;padding-right:0">
                                    @include('marble::components.famicon', ['name' => 'status_online'])
                                </td>
                                <td>
                                    {{ $user->name }}
                                    <small class="text-muted" style="margin-left:4px">{{ $user->email }}</small>
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
