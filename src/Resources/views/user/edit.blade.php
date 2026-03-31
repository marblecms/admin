@extends('marble::layouts.app')

@php
    $prefix  = config('marble.route_prefix', 'admin');
    $isNew   = $user === null;
    $saveUrl = $isNew ? url("{$prefix}/user/create") : url("{$prefix}/user/save/{$user->id}");
@endphp

@section('content')
    <h1>{{ $isNew ? trans('marble::admin.add_user') : $user->name }}</h1>

    <form action="{{ $saveUrl }}" method="post">
        @csrf

        <div class="main-box">
            <header class="main-box-header clearfix">
                <h2><b>{{ $isNew ? trans('marble::admin.add_user') : $user->name }}</b></h2>
            </header>
            <div class="main-box-body clearfix">
                <div class="form-group">
                    <label>{{ trans('marble::admin.name') }}</label>
                    <input type="text" class="form-control" name="name" value="{{ old('name', $user?->name) }}" required />
                </div>
                <div class="form-group">
                    <label>{{ trans('marble::admin.email') }}</label>
                    <input type="text" class="form-control" name="email" value="{{ old('email', $user?->email) }}" required />
                </div>
                <div class="form-group">
                    <label>{{ $isNew ? trans('marble::admin.password') : trans('marble::admin.new_password') }}</label>
                    <input type="password" class="form-control" name="password" value="" {{ $isNew ? 'required' : '' }} />
                </div>
                <div class="form-group">
                    <label>{{ trans('marble::admin.group') }}</label>
                    <select name="user_group_id" class="form-control">
                        @foreach($userGroups as $group)
                            <option value="{{ $group->id }}" {{ old('user_group_id', $user?->user_group_id) == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="form-group pull-right">
            <a href="{{ url("{$prefix}/user/all") }}" class="btn btn-primary">@include('marble::components.famicon', ['name' => 'cancel']) {{ trans('marble::admin.cancel') }}</a>
            <button type="submit" class="btn btn-success">@include('marble::components.famicon', ['name' => 'disk']) {{ trans('marble::admin.save') }}</button>
        </div>
        <div class="clearfix"></div>
    </form>
@endsection
