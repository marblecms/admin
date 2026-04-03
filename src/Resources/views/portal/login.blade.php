<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ trans('marble::portal.login') }}</title>
    <style>
        .portal-wrap    { max-width: 400px; margin: 80px auto; padding: 20px; }
        .portal-error   { color: red; margin-bottom: 12px; }
        .portal-field   { margin-bottom: 12px; }
        .portal-input   { width: 100%; padding: 6px; box-sizing: border-box; }
        .portal-btn     { padding: 8px 16px; }
        .portal-footer  { margin-top: 16px; }
    </style>
</head>
<body>
<div class="portal-wrap">
    <h2>{{ trans('marble::portal.login') }}</h2>

    @if($errors->any())
        <div class="portal-error">{{ $errors->first() }}</div>
    @endif

    <form action="{{ route('marble.portal.login.submit') }}" method="POST">
        @csrf
        <div class="portal-field">
            <label>{{ trans('marble::portal.email') }}</label><br>
            <input type="email" name="email" value="{{ old('email') }}" class="portal-input" required>
        </div>
        <div class="portal-field">
            <label>{{ trans('marble::portal.password') }}</label><br>
            <input type="password" name="password" class="portal-input" required>
        </div>
        <div class="portal-field">
            <label>
                <input type="checkbox" name="remember"> {{ trans('marble::portal.remember_me') }}
            </label>
        </div>
        <button type="submit" class="portal-btn">{{ trans('marble::portal.login') }}</button>
    </form>

    @if(config('marble.portal_registration', false))
        <p class="portal-footer">
            <a href="{{ route('marble.portal.register') }}">{{ trans('marble::portal.register') }}</a>
        </p>
    @endif
</div>
</body>
</html>
