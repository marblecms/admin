@extends('marble::layouts.app')

@php
    $prefix  = config('marble.route_prefix', 'admin');
    $isNew   = $user === null;
    $saveUrl = $isNew ? route('marble.user.create') : route('marble.user.save', $user);
@endphp

@section('content')
    <h1>{{ $isNew ? trans('marble::admin.add_user') : $user->name }}</h1>

    <form action="{{ $saveUrl }}" method="post">
        @csrf

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="marble-mb-0 marble-error-list">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

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
                <div class="form-group @error('password') has-error @enderror">
                    <label>{{ $isNew ? trans('marble::admin.password') : trans('marble::admin.new_password') }}</label>
                    <input type="password" class="form-control" name="password" value="" {{ $isNew ? 'required' : '' }} autocomplete="new-password" />
                    @if(!$isNew)
                        <span class="help-block">{{ trans('marble::admin.password_leave_blank') }}</span>
                    @endif
                    @error('password')<span class="help-block text-danger">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label>{{ trans('marble::admin.group') }}</label>
                    <select name="user_group_id" class="form-control">
                        @foreach($userGroups as $group)
                            <option value="{{ $group->id }}" {{ old('user_group_id', $user?->user_group_id) == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>{{ trans('marble::admin.theme') }}</label>
                    <select name="theme" class="form-control">
                        <option value="98" {{ old('theme', $user?->theme ?? 'xp') === '98' ? 'selected' : '' }}>1998</option>
                        <option value="xp" {{ old('theme', $user?->theme ?? 'xp') === 'xp' ? 'selected' : '' }}>2000</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="marble-check-label">
                        <input type="checkbox" name="active" value="1" {{ old('active', $user?->active ?? true) ? 'checked' : '' }}>
                        {{ trans('marble::admin.active') }}
                    </label>
                </div>
                @if(!$isNew && $user->last_login_at)
                <div class="form-group">
                    <label>{{ trans('marble::admin.last_login') }}</label>
                    <p class="form-control-static text-muted">{{ $user->last_login_at->format('d.m.Y H:i') }}</p>
                </div>
                @endif
            </div>
        </div>

        <div class="form-group pull-right">
            <a href="{{ url("{$prefix}/user/all") }}" class="btn btn-primary">@include('marble::components.famicon', ['name' => 'cancel']) {{ trans('marble::admin.cancel') }}</a>
            <button type="submit" class="btn btn-success">@include('marble::components.famicon', ['name' => 'disk']) {{ trans('marble::admin.save') }}</button>
        </div>
        <div class="clearfix"></div>
    </form>
@endsection
