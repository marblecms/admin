@extends('marble::layouts.app')

@php $prefix = config('marble.route_prefix', 'admin'); @endphp

@section('sidebar')
    <div class="main-box clearfix profile-box-menu">
        <div class="main-box-body clearfix">
            <div class="profile-box-header gray-bg clearfix">
                <h2>{{ trans('marble::admin.classgroups') }}</h2>
            </div>
            <div class="profile-box-content clearfix marble-p-0">
                @if($groups->isEmpty())
                    <p class="text-muted marble-sidebar-empty marble-text-sm">{{ trans('marble::admin.no_items') }}</p>
                @else
                    <table class="table table-hover marble-table-flush">
                        @foreach($groups as $group)
                        <tr>
                            <td class="marble-vmid">
                                @include('marble::components.famicon', ['name' => 'folder'])
                                {{ $group->name }}
                                <small class="text-muted">({{ $group->blueprints->count() }})</small>
                            </td>
                            <td class="text-right marble-vmid marble-nowrap">
                                <a href="{{ route('marble.blueprint.group.edit', $group) }}" class="btn btn-xs btn-info">
                                    @include('marble::components.famicon', ['name' => 'pencil'])
                                </a>
                                <form method="POST" action="{{ route('marble.blueprint.group.delete', $group) }}" class="marble-inline-form" onsubmit="return confirm('{{ trans('marble::admin.are_you_sure') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger">
                                        @include('marble::components.famicon', ['name' => 'bin'])
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </table>
                @endif
                <div class="marble-box-body clearfix">
                    <a href="{{ route('marble.blueprint.group.add') }}" class="btn btn-xs btn-success pull-right">
                        @include('marble::components.famicon', ['name' => 'folder_add']) {{ trans('marble::admin.add_classgroup') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <h1>
        {{ trans('marble::admin.classes') }}
        <div class="pull-right">
            <a href="{{ url("{$prefix}/blueprint/add") }}" class="btn btn-xs btn-success">@include('marble::components.famicon', ['name' => 'brick']) {{ trans('marble::admin.add_class') }}</a>
        </div>
    </h1>

    @foreach($groups as $group)
        <div class="main-box">
            <header class="main-box-header clearfix">
                <h2><b>{{ $group->name }}</b></h2>
            </header>
            <div class="main-box-body clearfix">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <tbody>
                            @foreach($group->blueprints as $blueprint)
                            <tr>
                                <td>
                                    <span class="icon-preview-wrap marble-icon-preview-wrap">
                                        <img src="{{ asset('vendor/marble/assets/images/famicons/' . ($blueprint->icon ?: 'brick') . '.svg') }}" width="16" height="16" class="marble-vmid" alt="">
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
                                        <form method="POST" action="{{ route('marble.blueprint.duplicate', $blueprint) }}" class="marble-inline-form">
                                            @csrf
                                            <button type="submit" class="btn btn-xs btn-default" title="{{ trans('marble::admin.duplicate') }}">@include('marble::components.famicon', ['name' => 'page_copy'])</button>
                                        </form>
                                        <form method="POST" action="{{ url("{$prefix}/blueprint/delete/{$blueprint->id}") }}" class="marble-inline-form" onsubmit="return confirm('{{ trans('marble::admin.are_you_sure') }}')">
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
