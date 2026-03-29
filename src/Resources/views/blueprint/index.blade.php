@extends('marble::layouts.app')

@php $prefix = config('marble.route_prefix', 'admin'); @endphp

@section('content')
    <h1>
        {{ trans('marble::admin.classes') }}
        <div class="pull-right">
            <a href="{{ url("{$prefix}/blueprint/group/add") }}" class="btn btn-xs btn-success">@include('marble::components.famicon', ['name' => 'folder']) {{ trans('marble::admin.add_classgroup') }}</a>
            <a href="{{ url("{$prefix}/blueprint/add") }}" class="btn btn-xs btn-success">@include('marble::components.famicon', ['name' => 'brick']) {{ trans('marble::admin.add_class') }}</a>
        </div>
    </h1>

    @foreach($groups as $group)
        <div class="main-box">
            <header class="main-box-header clearfix">
                <h2 class="pull-left"><b>{{ $group->name }}</b></h2>
                <div class="pull-right">
                    <a href="{{ url("{$prefix}/blueprint/group/edit/{$group->id}") }}" class="btn btn-xs btn-info">@include('marble::components.famicon', ['name' => 'pencil']) {{ trans('marble::admin.edit') }}</a>
                    <form method="POST" action="{{ url("{$prefix}/blueprint/group/delete/{$group->id}") }}" style="display:inline" onsubmit="return confirm('{{ trans('marble::admin.are_you_sure') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-xs btn-danger">@include('marble::components.famicon', ['name' => 'bin']) {{ trans('marble::admin.delete') }}</button>
                    </form>
                </div>
                <div class="clearfix"></div>
            </header>
            <div class="main-box-body clearfix">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <tbody>
                            @foreach($group->blueprints as $blueprint)
                            <tr>
                                <td>
                                    <span style="position:relative;display:inline-block;margin-right:6px;vertical-align:middle" class="icon-preview-wrap">
                                        <img src="{{ asset('vendor/marble/assets/images/famicons/' . ($blueprint->icon ?: 'brick') . '.svg') }}" width="16" height="16" style="vertical-align:middle">
                                        <span class="icon-preview-tooltip">
                                            <img src="{{ asset('vendor/marble/assets/images/famicons/' . ($blueprint->icon ?: 'brick') . '.svg') }}" width="48" height="48">
                                        </span>
                                    </span>
                                    <a href="{{ url("{$prefix}/blueprint/edit/{$blueprint->id}") }}">{{ $blueprint->name }}</a>
                                </td>
                                <td class="text-right">
                                    <div class="btn-group">
                                        <a href="{{ url("{$prefix}/blueprint/{$blueprint->id}/field/edit") }}" class="btn btn-primary btn-xs">@include('marble::components.famicon', ['name' => 'application_form']) {{ trans('marble::admin.edit_attributes') }}</a>
                                        <a href="{{ url("{$prefix}/blueprint/edit/{$blueprint->id}") }}" class="btn btn-info btn-xs">@include('marble::components.famicon', ['name' => 'pencil']) {{ trans('marble::admin.edit') }}</a>
                                        <form method="POST" action="{{ url("{$prefix}/blueprint/delete/{$blueprint->id}") }}" style="display:inline" onsubmit="return confirm('{{ trans('marble::admin.are_you_sure') }}')">
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
    @endforeach
@endsection
