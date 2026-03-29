@extends('marble::layouts.app')

@php
    $prefix = config('marble.route_prefix', 'admin');
    $permissions = [
        'users' => ['create_users', 'edit_users', 'delete_users', 'list_users'],
        'blueprints' => ['create_blueprints', 'edit_blueprints', 'delete_blueprints', 'list_blueprints'],
        'groups' => ['create_groups', 'edit_groups', 'delete_groups', 'list_groups'],
    ];
@endphp

@section('content')
    <h1>{{ $group->name }}</h1>

    <form action="{{ url("{$prefix}/user-group/save/{$group->id}") }}" method="post">
        @csrf

        <div class="main-box">
            <header class="main-box-header clearfix">
                <h2><b>{{ $group->name }}</b></h2>
            </header>
            <div class="main-box-body clearfix">
                <div class="form-group">
                    <label>{{ trans('marble::admin.name') }}</label>
                    <input type="text" class="form-control" name="name" value="{{ $group->name }}" />
                </div>

                <div class="row">
                    @foreach($permissions as $section => $perms)
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>{{ ucfirst($section) }}</label>
                                <br />
                                @foreach($perms as $perm)
                                    @php $action = str_replace("_{$section}", '', $perm); @endphp
                                    <label class="perm-checkbox-label">
                                        <input type="checkbox" name="can_{{ $perm }}" value="1" {{ $group->{"can_{$perm}"} ? 'checked' : '' }}>
                                        {{ ucfirst($action) }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="form-group">
                    <label>{{ trans('marble::admin.allowed_classes') }}</label>
                    <select multiple name="allowed_blueprints[]" class="form-control" size="10">
                        <option value="all" {{ $group->allowsAllBlueprints() ? 'selected' : '' }}>- All -</option>
                        @foreach(\Marble\Admin\Models\Blueprint::all() as $bp)
                            <option value="{{ $bp->id }}" {{ $group->allowedBlueprints->contains('id', $bp->id) ? 'selected' : '' }}>{{ $bp->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="form-group pull-right">
            <a href="{{ url("{$prefix}/user-group/all") }}" class="btn btn-primary">@include('marble::components.famicon', ['name' => 'cancel']) {{ trans('marble::admin.cancel') }}</a>
            <button type="submit" class="btn btn-success">@include('marble::components.famicon', ['name' => 'disk']) {{ trans('marble::admin.save') }}</button>
        </div>
        <div class="clearfix"></div>
    </form>
@endsection
