@extends('marble::layouts.app')

@php $prefix = config('marble.route_prefix', 'admin'); @endphp

@section('content')
    <div class="clearfix">
        <h1 class="pull-left">{{ trans('marble::admin.workflows') }}</h1>
        <div class="pull-right">
            <form method="POST" action="{{ url("{$prefix}/workflow/create") }}">
                @csrf
                <button type="submit" class="btn btn-success btn-sm">
                    @include('marble::components.famicon', ['name' => 'add'])
                    {{ trans('marble::admin.add_workflow') }}
                </button>
            </form>
        </div>
    </div>
    <div class="main-box">
        <header class="main-box-header">
            <h2>{{ trans('marble::admin.workflows') }}</h2>
        </header>
        <div class="main-box-body clearfix">
            @if($workflows->isEmpty())
                <p class="text-muted marble-p-20">{{ trans('marble::admin.no_workflows') }}</p>
            @else
                <table class="table table-hover marble-table-flush">
                    <thead>
                        <tr>
                            <th>{{ trans('marble::admin.name') }}</th>
                            <th>{{ trans('marble::admin.workflow_steps') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($workflows as $workflow)
                            <tr>
                                <td>
                                    <a href="{{ route('marble.workflow.edit', $workflow) }}">{{ $workflow->name }}</a>
                                </td>
                                <td>
                                    @foreach($workflow->steps as $step)
                                        <span class="label label-default marble-mr-xs">{{ $step->name }}</span>
                                    @endforeach
                                    <span class="label label-success">{{ trans('marble::admin.published') }}</span>
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('marble.workflow.edit', $workflow) }}" class="btn btn-xs btn-default">
                                        @include('marble::components.famicon', ['name' => 'pencil']) {{ trans('marble::admin.edit') }}
                                    </a>
                                    <form method="POST" action="{{ route('marble.workflow.delete', $workflow) }}" class="marble-inline-form" onsubmit="return confirm('{{ trans('marble::admin.confirm_delete') }}')">
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
