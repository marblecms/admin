@extends('marble::layouts.app')

@section('sidebar')

    {{-- Item info --}}
    <div class="main-box clearfix profile-box-menu">
        <div class="main-box-body clearfix">
            <div class="profile-box-header gray-bg clearfix">
                <h2>Item</h2>
            </div>
            <div class="profile-box-content clearfix">
                <table class="table" style="margin-bottom:0">
                    <tr>
                        <td class="text-muted">Name</td>
                        <td><a href="{{ route('marble.item.edit', $item) }}">{{ $item->name() ?: '—' }}</a></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Blueprint</td>
                        <td>{{ $item->blueprint?->name }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ trans('marble::admin.date') }}</td>
                        <td>{{ $revision->created_at->format('d.m.Y H:i') }}</td>
                    </tr>
                    @if($revision->user)
                    <tr>
                        <td class="text-muted">{{ trans('marble::admin.version_by') }}</td>
                        <td>{{ $revision->user->name }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- Revisions list --}}
    <div class="main-box clearfix profile-box-menu">
        <div class="main-box-body clearfix">
            <div class="profile-box-header gray-bg clearfix">
                <h2>{{ trans('marble::admin.versions') }}</h2>
            </div>
            <div class="profile-box-content clearfix">
                <ul class="menu-items">
                    @foreach($revisions as $rev)
                        <li class="{{ $rev->id === $revision->id ? 'active' : '' }}">
                            <a href="{{ route('marble.item.diff', [$item, $rev]) }}"
                               style="{{ $rev->id === $revision->id ? 'font-weight:bold' : '' }}">
                                {{ $rev->created_at->format('d.m.Y H:i') }}
                                @if($rev->user) <small style="color:#999">· {{ $rev->user->name }}</small> @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

@endsection

@section('content')
    <h1>
        {{ trans('marble::admin.revision_diff') }}
        <small style="font-size:14px;font-weight:normal;color:#999">
            {{ $revision->created_at->format('d.m.Y H:i') }}
            @if($revision->user) · {{ $revision->user->name }} @endif
        </small>
    </h1>

    {{-- Breadcrumb --}}
    @if($breadcrumb->count() > 1)
        <div style="margin:-6px 0 12px;font-size:12px;color:#888">
            @foreach($breadcrumb as $crumb)
                <a href="{{ route('marble.item.edit', $crumb) }}" style="color:#5580B0">{{ $crumb->name() ?: '—' }}</a>
                <span style="margin:0 4px;color:#bbb">›</span>
            @endforeach
            <span style="color:#555">{{ trans('marble::admin.diff') }}</span>
        </div>
    @endif

    @if(empty($diff))
        <div class="main-box">
            <div class="main-box-body clearfix">
                <p class="text-muted" style="padding:16px 0">{{ trans('marble::admin.diff_no_changes') }}</p>
            </div>
        </div>
    @else
        <div class="main-box">
            <header class="main-box-header clearfix">
                <h2>
                    <span style="color:#c0392b">{{ trans('marble::admin.diff_before') }}</span>
                    &rarr;
                    <span style="color:#27ae60">{{ trans('marble::admin.diff_after') }}</span>
                    @if($previous)
                        <small style="font-weight:normal;color:#999;margin-left:10px">
                            {{ trans('marble::admin.diff_compared_to') }} {{ $previous->created_at->format('d.m.Y H:i') }}
                        </small>
                    @else
                        <small style="font-weight:normal;color:#999;margin-left:10px">
                            {{ trans('marble::admin.diff_first_revision') }}
                        </small>
                    @endif
                </h2>
            </header>
            <div class="main-box-body clearfix">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width:140px">{{ trans('marble::admin.name') }}</th>
                            <th style="width:80px">{{ trans('marble::admin.language') }}</th>
                            <th>{{ trans('marble::admin.diff_before') }}</th>
                            <th>{{ trans('marble::admin.diff_after') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($diff as $change)
                            <tr>
                                <td><b>{{ $change['field'] }}</b></td>
                                <td class="text-muted">{{ $change['language'] }}</td>
                                <td style="background:#fff5f5;max-width:300px;word-break:break-word">
                                    @if($change['old'] === null)
                                        <em class="text-muted">—</em>
                                    @else
                                        <span style="color:#c0392b">{{ is_array($change['old']) ? json_encode($change['old']) : $change['old'] }}</span>
                                    @endif
                                </td>
                                <td style="background:#f5fff8;max-width:300px;word-break:break-word">
                                    @if($change['new'] === null)
                                        <em class="text-muted">—</em>
                                    @else
                                        <span style="color:#27ae60">{{ is_array($change['new']) ? json_encode($change['new']) : $change['new'] }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div style="margin-top:8px">
        <form method="POST" action="{{ route('marble.item.revert', [$item, $revision]) }}" onsubmit="return confirm('{{ trans('marble::admin.are_you_sure') }}')">
            @csrf
            <span class="pull-right">
                <button type="submit" class="btn btn-success">
                    @include('marble::components.famicon', ['name' => 'resultset_previous']) {{ trans('marble::admin.restore') }}
                </button>
            </span>
            <a href="{{ route('marble.item.edit', $item) }}" class="btn btn-default">
                {{ trans('marble::admin.cancel') }}
            </a>
        </form>
    </div>
@endsection
