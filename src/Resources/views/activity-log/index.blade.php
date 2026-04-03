@extends('marble::layouts.app')

@section('content')
    <h1>{{ trans('marble::admin.activity_log') }}</h1>

    <div class="main-box">
        <div class="main-box-body clearfix">
            @if($logs->isEmpty())
                <p class="text-muted marble-empty-state">{{ trans('marble::admin.no_activity') }}</p>
            @else
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th class="marble-col-date">{{ trans('marble::admin.date') }}</th>
                            <th class="marble-col-md">{{ trans('marble::admin.users') }}</th>
                            <th class="marble-col-lg">{{ trans('marble::admin.action') }}</th>
                            <th>{{ trans('marble::admin.name') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                            <tr>
                                <td class="text-muted">{{ $log->created_at->format('d.m.Y H:i') }}</td>
                                <td>{{ $log->user?->name ?? '—' }}</td>
                                <td>
                                    <span class="label {{ match(true) {
                                        str_contains($log->action, 'deleted') || str_contains($log->action, 'expired') => 'label-danger',
                                        str_contains($log->action, 'published') => 'label-success',
                                        str_contains($log->action, 'draft') => 'label-default',
                                        default => 'label-info'
                                    } }}">{{ $log->action }}</span>
                                </td>
                                <td>
                                    @if($log->item_id)
                                        <a href="{{ route('marble.item.edit', $log->item_id) }}">{{ $log->item_name }}</a>
                                    @else
                                        {{ $log->item_name ?? '—' }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="marble-mt-xs marble-mb-xs">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
