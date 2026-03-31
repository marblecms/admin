<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ trans('marble::portal.register') }}</title>
</head>
<body>
<div style="max-width:400px;margin:80px auto;padding:20px">
    <h2>{{ trans('marble::portal.register') }}</h2>

    @if($errors->any())
        <div style="color:red;margin-bottom:12px">{{ $errors->first() }}</div>
    @endif

    <form action="{{ route('marble.portal.register.submit') }}" method="POST">
        @csrf
        <div style="margin-bottom:12px">
            <label>{{ trans('marble::portal.email') }}</label><br>
            <input type="email" name="email" value="{{ old('email') }}" style="width:100%;padding:6px;box-sizing:border-box" required>
        </div>
        <div style="margin-bottom:12px">
            <label>{{ trans('marble::portal.password') }}</label><br>
            <input type="password" name="password" style="width:100%;padding:6px;box-sizing:border-box" required minlength="8">
        </div>
        <div style="margin-bottom:12px">
            <label>{{ trans('marble::portal.password_confirm') }}</label><br>
            <input type="password" name="password_confirmation" style="width:100%;padding:6px;box-sizing:border-box" required>
        </div>
        <button type="submit" style="padding:8px 16px">{{ trans('marble::portal.register') }}</button>
    </form>

    <p style="margin-top:16px">
        <a href="{{ route('marble.portal.login') }}">{{ trans('marble::portal.back_to_login') }}</a>
    </p>
</div>
</body>
</html>
