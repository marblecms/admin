@extends('marble::layouts.app')

@php $prefix = config('marble.route_prefix', 'admin'); @endphp

@section('content')
    <h1>
        <span class="pull-left">{{ trans('marble::admin.webhooks') }}</span>
        <a href="{{ route('marble.webhook.create') }}" class="btn btn-success pull-right">
            @include('marble::components.famicon', ['name' => 'add']) {{ trans('marble::admin.add_webhook') }}
        </a>
        <div class="clearfix"></div>
    </h1>

    @if($webhooks->isEmpty())
        <div class="main-box">
            <div class="main-box-body clearfix">
                <p class="text-muted marble-empty-state">{{ trans('marble::admin.no_webhooks') }}</p>
            </div>
        </div>
    @else
        <div class="main-box">
            <div class="main-box-body clearfix">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>{{ trans('marble::admin.name') }}</th>
                            <th>URL</th>
                            <th>{{ trans('marble::admin.webhook_events') }}</th>
                            <th>{{ trans('marble::admin.status') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($webhooks as $webhook)
                            <tr>
                                <td>{{ $webhook->name }}</td>
                                <td><small class="text-muted">{{ $webhook->url }}</small></td>
                                <td>
                                    @foreach($webhook->events as $event)
                                        <span class="label label-default">{{ $event }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    @if($webhook->active)
                                        <span class="label label-success">{{ trans('marble::admin.active') }}</span>
                                    @else
                                        <span class="label label-default">{{ trans('marble::admin.inactive') }}</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('marble.webhook.edit', $webhook) }}" class="btn btn-xs btn-info">
                                        @include('marble::components.famicon', ['name' => 'pencil']) {{ trans('marble::admin.edit') }}
                                    </a>
                                    <form method="POST" action="{{ route('marble.webhook.delete', $webhook) }}" class="marble-inline-form" onsubmit="return confirm('{{ trans('marble::admin.are_you_sure') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-danger">
                                            @include('marble::components.famicon', ['name' => 'bin']) {{ trans('marble::admin.delete') }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
