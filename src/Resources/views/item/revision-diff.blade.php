@extends('marble::layouts.app')

@section('sidebar')

    {{-- Item info --}}
    <div class="main-box clearfix profile-box-menu">
        <div class="main-box-body clearfix">
            <div class="profile-box-header gray-bg clearfix">
                <h2>Item</h2>
            </div>
            <div class="profile-box-content clearfix">
                <table class="table"class="marble-table-flush">
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
                               class="{{ $rev->id === $revision->id ? 'marble-fw-bold' : '' }}">
                                {{ $rev->created_at->format('d.m.Y H:i') }}
                                @if($rev->user) <small class="marble-meta">· {{ $rev->user->name }}</small> @endif
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
    </h1>

    {{-- Breadcrumb --}}
    @if($breadcrumb->count() > 1)
        <div class="marble-breadcrumb">
            @foreach($breadcrumb as $crumb)
                <a href="{{ route('marble.item.edit', $crumb) }}" class="marble-link">{{ $crumb->name() ?: '—' }}</a>
                <span class="marble-breadcrumb-sep">›</span>
            @endforeach
            <span class="text-muted">{{ trans('marble::admin.diff') }}</span>
        </div>
    @endif

    @if(empty($diff))
        <div class="main-box">
            <div class="main-box-body clearfix">
                <p class="text-muted marble-pad-md">{{ trans('marble::admin.diff_no_changes') }}</p>
            </div>
        </div>
    @else
        <div class="main-box">
            <header class="main-box-header clearfix">
                <h2>
                    <span class="marble-diff-before">{{ trans('marble::admin.diff_before') }}</span>
                    &rarr;
                    <span class="marble-diff-after">{{ trans('marble::admin.diff_after') }}</span>
                    @if($previous)
                        <small class="marble-fw-normal marble-meta marble-ml-sm">
                            {{ trans('marble::admin.diff_compared_to') }} {{ $previous->created_at->format('d.m.Y H:i') }}
                        </small>
                    @else
                        <small class="marble-fw-normal marble-meta marble-ml-sm">
                            {{ trans('marble::admin.diff_first_revision') }}
                        </small>
                    @endif
                </h2>
            </header>
            <div class="main-box-body clearfix">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="marble-col-date">{{ trans('marble::admin.name') }}</th>
                            <th class="marble-col-sm">{{ trans('marble::admin.language') }}</th>
                            <th>{{ trans('marble::admin.diff_before') }}</th>
                            <th>{{ trans('marble::admin.diff_after') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($diff as $change)
                            <tr>
                                <td><b>{{ $change['field'] }}</b></td>
                                <td class="text-muted">{{ $change['language'] }}</td>
                                <td class="marble-diff-cell-before marble-break-all">
                                    @if($change['old'] === null)
                                        <em class="text-muted">—</em>
                                    @else
                                        <span class="marble-diff-before">{{ is_array($change['old']) ? json_encode($change['old']) : $change['old'] }}</span>
                                    @endif
                                </td>
                                <td class="marble-diff-cell-after marble-break-all">
                                    @if($change['new'] === null)
                                        <em class="text-muted">—</em>
                                    @else
                                        <span class="marble-diff-after">{{ is_array($change['new']) ? json_encode($change['new']) : $change['new'] }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="marble-mt-sm">
        <form method="POST" action="{{ route('marble.item.revert', [$item, $revision]) }}" onsubmit="return confirm('{{ trans('marble::admin.are_you_sure') }}')">
            @csrf
            <span class="pull-right">
                <button type="submit" class="btn btn-success">
                    @include('marble::components.famicon', ['name' => 'arrow_rotate_cw']) {{ trans('marble::admin.restore') }}
                </button>
            </span>
        </form>
    </div>
@endsection
