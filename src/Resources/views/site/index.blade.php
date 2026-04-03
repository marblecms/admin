@extends('marble::layouts.app')

@section('content')
    <h1>
        <span class="pull-left">{{ trans('marble::admin.sites') }}</span>
        <a href="{{ route('marble.site.create') }}" class="btn btn-success pull-right">
            @include('marble::components.famicon', ['name' => 'add']) {{ trans('marble::admin.add_site') }}
        </a>
        <div class="clearfix"></div>
    </h1>

    <div class="main-box">
        <div class="main-box-body clearfix">
            @if($sites->isEmpty())
                <p class="text-muted marble-empty-state">{{ trans('marble::admin.no_sites') }}</p>
            @else
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>{{ trans('marble::admin.name') }}</th>
                            <th>{{ trans('marble::admin.domain') }}</th>
                            <th>{{ trans('marble::admin.root_item') }}</th>
                            <th>{{ trans('marble::admin.language') }}</th>
                            <th>{{ trans('marble::admin.status') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sites as $site)
                            <tr>
                                <td><a href="{{ route('marble.site.edit', $site) }}">{{ $site->name }}</a></td>
                                <td>
                                    @if($site->domain)
                                        <code>{{ $site->domain }}</code>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                    @if($site->is_default)
                                        <span class="label label-primary marble-ml-xs">{{ trans('marble::admin.default') }}</span>
                                    @endif
                                </td>
                                <td>{{ $site->rootItem?->name() ?? '—' }}</td>
                                <td>{{ $site->defaultLanguage?->name ?? '—' }}</td>
                                <td>
                                    @if($site->active)
                                        <span class="label label-success">{{ trans('marble::admin.active') }}</span>
                                    @else
                                        <span class="label label-default">{{ trans('marble::admin.inactive') }}</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('marble.site.edit', $site) }}" class="btn btn-xs btn-info">
                                        @include('marble::components.famicon', ['name' => 'pencil']) {{ trans('marble::admin.edit') }}
                                    </a>
                                    <form method="POST" action="{{ route('marble.site.delete', $site) }}" class="marble-inline-form" onsubmit="return confirm('{{ trans('marble::admin.are_you_sure') }}')">
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
            @endif
        </div>
    </div>
@endsection
