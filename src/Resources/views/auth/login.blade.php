@extends('marble::layouts.login')

@section('content')
    <br /><br /><br />
    <div class="main-box">
        <header class="main-box-header clearfix">
            <h2>Login</h2>
        </header>
        <div class="main-box-body clearfix">
            <form action="{{ route('marble.login.submit') }}" method="post">
                @csrf

                @if($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif

                <div class="form-group">
                    <label>E-Mail</label>
                    <input type="text" value="{{ old('email') }}" name="email" class="form-control"/>
                </div>
                <div class="form-group">
                    <label>{{ trans('marble::admin.password') }}</label>
                    <input type="password" name="password" class="form-control"/>
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="remember" /> {{ trans('marble::admin.remember_me') }}
                    </label>
                </div>
                <div class="form-group">
                    <input type="submit" class="btn btn-success" value="{{ trans('marble::admin.login') }}" />
                </div>
            </form>
        </div>
    </div>
@endsection
