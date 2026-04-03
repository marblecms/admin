@extends('marble::layouts.app')

@php $prefix = config('marble.route_prefix', 'admin'); @endphp

@section('content')
    <h1>
        {{ trans('marble::admin.usergroups') }}
        <div class="pull-right">
            <a href="{{ url("{$prefix}/user-group/add") }}" class="btn btn-xs btn-success">@include('marble::components.famicon', ['name' => 'add']) {{ trans('marble::admin.add_usergroup') }}</a>
        </div>
    </h1>

    <div class="main-box">
        <header class="main-box-header clearfix">
            <h2>{{ trans('marble::admin.usergroups') }}</h2>
        </header>
        <div class="main-box-body clearfix">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>{{ trans('marble::admin.name') }}</th>
                            <th class="text-right">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($groups as $group)
                        <tr>
                            <td><a href="{{ url("{$prefix}/user-group/edit/{$group->id}") }}">{{ $group->name }}</a></td>
                            <td class="text-right">
                                <div class="btn-group">
                                    <a href="{{ url("{$prefix}/user-group/edit/{$group->id}") }}" class="btn btn-info btn-xs">@include('marble::components.famicon', ['name' => 'pencil']) {{ trans('marble::admin.edit') }}</a>
                                    <form method="POST" action="{{ url("{$prefix}/user-group/delete/{$group->id}") }}" class="marble-inline-form" onsubmit="return confirm('{{ trans('marble::admin.are_you_sure') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-danger">@include('marble::components.famicon', ['name' => 'bin']) {{ trans('marble::admin.delete') }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
