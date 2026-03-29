@extends('marble::layouts.app')

@php $prefix = config('marble.route_prefix', 'admin'); @endphp

@section('sidebar')

    {{-- Stats --}}
    <div class="main-box">
        <header class="main-box-header clearfix">
            <h2>@include('marble::components.famicon', ['name' => 'chart_bar']) Stats</h2>
        </header>
        <div class="main-box-body clearfix">
            <table class="table" style="margin-bottom:0">
                <tr>
                    <td class="text-muted">Items total</td>
                    <td class="text-right"><strong>{{ $stats['items_total'] }}</strong></td>
                </tr>
                <tr>
                    <td class="text-muted">Published</td>
                    <td class="text-right"><span class="label label-success">{{ $stats['items_published'] }}</span></td>
                </tr>
                <tr>
                    <td class="text-muted">Draft</td>
                    <td class="text-right"><span class="label label-default">{{ $stats['items_draft'] }}</span></td>
                </tr>
                <tr>
                    <td class="text-muted">Media files</td>
                    <td class="text-right"><strong>{{ $stats['media_count'] }}</strong></td>
                </tr>
                <tr>
                    <td class="text-muted">Users</td>
                    <td class="text-right"><strong>{{ $stats['users_count'] }}</strong></td>
                </tr>
            </table>
        </div>
    </div>

    {{-- Upcoming scheduled --}}
    @if($upcomingItems->isNotEmpty())
    <div class="main-box">
        <header class="main-box-header clearfix">
            <h2>@include('marble::components.famicon', ['name' => 'clock']) Upcoming</h2>
        </header>
        <div class="main-box-body clearfix">
            <table class="table" style="margin-bottom:0">
                @foreach($upcomingItems as $upcoming)
                <tr onclick="window.location='{{ route('marble.item.edit', $upcoming) }}'" style="cursor:pointer">
                    <td>
                        {{ $upcoming->name() ?: '—' }}
                        <br><small class="text-muted">
                            @if($upcoming->published_at && $upcoming->published_at->isFuture())
                                Publishes {{ $upcoming->published_at->diffForHumans() }}
                            @elseif($upcoming->expires_at && $upcoming->expires_at->isFuture())
                                Expires {{ $upcoming->expires_at->diffForHumans() }}
                            @endif
                        </small>
                    </td>
                </tr>
                @endforeach
            </table>
        </div>
    </div>
    @endif

    {{-- Unread form submissions --}}
    @if($unreadSubmissions->isNotEmpty())
    <div class="main-box">
        <header class="main-box-header clearfix">
            <h2>@include('marble::components.famicon', ['name' => 'report']) Unread Submissions</h2>
        </header>
        <div class="main-box-body clearfix">
            <table class="table" style="margin-bottom:0">
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
            <h2>@include('marble::components.famicon', ['name' => 'server']) System</h2>
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
                    <td class="text-muted">Environment</td>
                    <td class="text-right"><code>{{ app()->environment() }}</code></td>
                </tr>
            </table>
        </div>
    </div>

@endsection

@section('content')
    <h1>Dashboard</h1>

    {{-- Quick Actions --}}
    <div class="row" style="margin-bottom:10px">
        <div class="col-xs-12">
            <div class="main-box">
                <div class="main-box-body clearfix" style="padding:12px 15px">
                    <a href="{{ route('marble.trash.index') }}" class="btn btn-default" style="margin-right:6px">
                        @include('marble::components.famicon', ['name' => 'bin'])
                        {{ trans('marble::admin.trash') }}
                        @if($trashCount > 0)
                            <span class="badge" style="background:#c0392b">{{ $trashCount }}</span>
                        @endif
                    </a>
                    <a href="{{ route('marble.item.import-form') }}" class="btn btn-default" style="margin-right:6px">
                        @include('marble::components.famicon', ['name' => 'page_white_paste'])
                        {{ trans('marble::admin.import') }}
                    </a>
                    <a href="{{ route('marble.activity-log.index') }}" class="btn btn-default" style="margin-right:6px">
                        @include('marble::components.famicon', ['name' => 'time'])
                        {{ trans('marble::admin.activity_log') }}
                    </a>
                    <a href="{{ route('marble.webhook.index') }}" class="btn btn-default" style="margin-right:6px">
                        @include('marble::components.famicon', ['name' => 'connect'])
                        {{ trans('marble::admin.webhooks') }}
                    </a>
                    <a href="{{ route('marble.api-token.index') }}" class="btn btn-default" style="margin-right:6px">
                        @include('marble::components.famicon', ['name' => 'key'])
                        API Tokens
                    </a>
                    <a href="{{ route('marble.package.export') }}" class="btn btn-default">
                        @include('marble::components.famicon', ['name' => 'box'])
                        Packages
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Last Edited Items --}}
    <div class="main-box">
        <header class="main-box-header clearfix">
            <h2>{{ trans('marble::admin.last_edited') }}</h2>
        </header>
        <div class="main-box-body clearfix">
            @if($recentItems->isEmpty())
                <p class="text-muted" style="padding:10px 0">{{ trans('marble::admin.no_items') }}</p>
            @else
                <table class="table table-hover" style="margin-bottom:0">
                    <tbody>
                        @foreach($recentItems as $item)
                            <tr onclick="window.location='{{ route('marble.item.edit', $item) }}'" style="cursor:pointer">
                                <td style="width:24px;padding-right:0">
                                    @if($item->blueprint?->icon)
                                        @include('marble::components.famicon', ['name' => $item->blueprint->icon])
                                    @endif
                                </td>
                                <td>
                                    {{ $item->name() ?: '—' }}
                                    <small class="text-muted" style="margin-left:6px">{{ $item->blueprint?->name }}</small>
                                </td>
                                <td style="width:80px">
                                    @if($item->status === 'published')
                                        <span class="label label-success">{{ trans('marble::admin.published') }}</span>
                                    @else
                                        <span class="label label-default">{{ trans('marble::admin.draft') }}</span>
                                    @endif
                                </td>
                                <td class="text-muted" style="width:140px;white-space:nowrap">
                                    {{ $item->updated_at->diffForHumans() }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
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
                            <a href="{{ url("{$prefix}/blueprint/add") }}" class="btn btn-xs btn-success" style="margin-right:4px">
                                {{ trans('marble::admin.create') }}
                            </a>

                            <a href="{{ url("{$prefix}/blueprint/all") }}" class="btn btn-xs btn-default">
                                {{ trans('marble::admin.list') }}
                            </a>
                        </span>
                    </h2>
                </header>
                <div class="main-box-body clearfix">
                    @if($blueprints->isEmpty())
                        <p class="text-muted" style="padding:20px 0;text-align:center;margin:0">No blueprints yet.</p>
                    @else
                    <table class="table table-hover" style="margin-bottom:0">
                        <tbody>
                            @foreach($blueprints as $blueprint)
                            <tr onclick="window.location='{{ url("{$prefix}/blueprint/edit/{$blueprint->id}") }}'" style="cursor:pointer">
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
                            <a href="{{ url("{$prefix}/user-group/all") }}" class="btn btn-xs btn-default" style="margin-right:4px">
                                {{ trans('marble::admin.usergroups') }}
                            </a>
                            <a href="{{ url("{$prefix}/user/add") }}" class="btn btn-xs btn-success" style="margin-right:4px">
                                {{ trans('marble::admin.create') }}
                            </a>
                            <a href="{{ url("{$prefix}/user/all") }}" class="btn btn-xs btn-default">
                                {{ trans('marble::admin.list') }}
                            </a>
                        </span>
                    </h2>
                </header>
                <div class="main-box-body clearfix">
                    <table class="table table-hover" style="margin-bottom:0">
                        <tbody>
                            @foreach($users as $user)
                            <tr onclick="window.location='{{ url("{$prefix}/user/edit/{$user->id}") }}'" style="cursor:pointer">
                                <td>
                                    @include('marble::components.famicon', ['name' => 'status_online'])
                                    {{ $user->name }}
                                    <small class="text-muted">{{ $user->email }}</small>
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
