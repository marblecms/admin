@extends('marble::layouts.login')

@section('content')

<form action="{{ route('marble.two-factor.verify') }}" method="post" autocomplete="off">
    @csrf

    <div class="form-group">
        <h2 style="margin-top:0">{{ trans('marble::admin.two_factor_title') }}</h2>
        <p class="text-muted">{{ trans('marble::admin.two_factor_hint') }}</p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="form-group">
        <label>{{ trans('marble::admin.two_factor_code') }}</label>
        <input type="text" name="code" class="form-control" autofocus
               autocomplete="one-time-code"
               inputmode="numeric"
               placeholder="000000"
               maxlength="9"
               style="letter-spacing:.2em;font-size:1.3em;text-align:center" />
    </div>

    <div class="form-group">
        <button type="submit" class="btn btn-success btn-block">
            {{ trans('marble::admin.two_factor_verify') }}
        </button>
    </div>

    <p class="text-muted" style="font-size:12px;margin-top:12px">
        {{ trans('marble::admin.two_factor_backup_hint') }}
    </p>
</form>

@endsection
