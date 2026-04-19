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
            <a href="{{ url("{$prefix}/user/all") }}" class="btn btn-default">@include('marble::components.famicon', ['name' => 'cancel']) {{ trans('marble::admin.cancel') }}</a>
            <button type="submit" class="btn btn-success">@include('marble::components.famicon', ['name' => 'disk']) {{ trans('marble::admin.save') }}</button>
        </div>
        <div class="clearfix"></div>
    </form>

    @if(!$isNew)
    {{-- Two-Factor Authentication ----------------------------------------- --}}
    <div class="main-box" style="margin-top:20px">
        <header class="main-box-header clearfix">
            <h2>@include('marble::components.famicon', ['name' => 'lock']) {{ trans('marble::admin.two_factor_auth') }}</h2>
        </header>
        <div class="main-box-body clearfix">

            @if(session('two_factor_backup_codes'))
                <div class="alert alert-success">
                    <strong>{{ trans('marble::admin.two_factor_enabled') }}</strong><br>
                    {{ trans('marble::admin.two_factor_backup_codes_save') }}<br><br>
                    @foreach(session('two_factor_backup_codes') as $code)
                        <code style="display:inline-block;margin:2px 4px">{{ $code }}</code>
                    @endforeach
                </div>
            @endif

            @if($user->two_factor_enabled)
                <p>
                    <span class="label label-success">{{ trans('marble::admin.two_factor_active') }}</span>
                    &nbsp;{{ trans('marble::admin.two_factor_active_hint') }}
                </p>
                <form method="POST" action="{{ route('marble.two-factor.disable', $user) }}">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm"
                            onclick="return confirm('{{ trans('marble::admin.two_factor_disable_confirm') }}')">
                        @include('marble::components.famicon', ['name' => 'lock_open']) {{ trans('marble::admin.two_factor_disable') }}
                    </button>
                </form>
            @else
                <p class="text-muted">{{ trans('marble::admin.two_factor_setup_hint') }}</p>

                <div id="marble-2fa-setup" style="display:none">
                    <div id="marble-2fa-qr-wrap" style="margin-bottom:12px">
                        <img id="marble-2fa-qr" src="" alt="QR Code" style="display:block;width:180px;height:180px;border:1px solid #ddd">
                        <p class="text-muted" style="font-size:12px;margin-top:6px">
                            {{ trans('marble::admin.two_factor_manual_key') }}:
                            <strong id="marble-2fa-secret" style="font-family:monospace;letter-spacing:.1em"></strong>
                        </p>
                    </div>
                    <form method="POST" action="{{ route('marble.two-factor.enable', $user) }}">
                        @csrf
                        @error('code')<div class="alert alert-danger">{{ $message }}</div>@enderror
                        <div class="form-group">
                            <label>{{ trans('marble::admin.two_factor_enter_code') }}</label>
                            <input type="text" name="code" class="form-control input-sm"
                                   style="width:140px;letter-spacing:.15em;font-size:1.1em"
                                   autocomplete="one-time-code" inputmode="numeric"
                                   maxlength="6" placeholder="000000" autofocus />
                        </div>
                        <button type="submit" class="btn btn-success btn-sm">
                            @include('marble::components.famicon', ['name' => 'tick']) {{ trans('marble::admin.two_factor_confirm_enable') }}
                        </button>
                    </form>
                </div>

                <button id="marble-2fa-start" class="btn btn-default btn-sm">
                    @include('marble::components.famicon', ['name' => 'lock']) {{ trans('marble::admin.two_factor_setup') }}
                </button>
            @endif
        </div>
    </div>
    @endif
@endsection

@if(!$isNew && !$user->two_factor_enabled)
@section('javascript')
<script>
$('#marble-2fa-start').on('click', function() {
    var $btn = $(this);
    $btn.prop('disabled', true).text('Loading…');

    $.getJSON('{{ route('marble.two-factor.generate-secret', $user) }}', function(data) {
        $('#marble-2fa-qr').attr('src', data.qr_url);
        $('#marble-2fa-secret').text(data.secret);
        $('#marble-2fa-setup').show();
        $btn.hide();
    }).fail(function() {
        $btn.prop('disabled', false).text('{{ trans('marble::admin.two_factor_setup') }}');
        alert('Could not generate secret. Please try again.');
    });
});
</script>
@endsection
@endif
