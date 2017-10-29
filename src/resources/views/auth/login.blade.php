@extends('admin::layouts.login')

@section('content')
    
    <br /><br /><br />
    <div class="main-box">
        <header class="main-box-header clearfix">
            <h2>Login</h2>
        </header>
        <div class="main-box-body clearfix">
            <form action="{{url("admin/login")}}" method="post">
                
                {!! csrf_field() !!}
                <div class="form-group">
                    <label>E-Mail</label>
                    <input type="text" value="{{ old('email') }}" name="email" class="form-control"/>
                </div>
                
                <div class="form-group">
                    <label>Passwort</label>
                    <input type="password" name="password" class="form-control"/>
                </div>
                
                <div class="form-group">
                    <label>Benutzer merken?</label>
                    <input type="checkbox" name="remember" class="form-control"/>
                </div>
                <div class="form-group">
                    <input type="submit" class="btn btn-success" value="Absenden" />
                </div>
            </form>
        </div>
    </div>

@endsection