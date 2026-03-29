<!DOCTYPE html>
<html lang="en">
<head>
    <title>{{ trans('marble::admin.title', [], 'Administration') }}</title>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" href="{{ asset('vendor/marble/assets/css/bootstrap.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('vendor/marble/assets/css/font-awesome.css') }}"/>
    <link rel="stylesheet" href="{{ asset('vendor/marble/assets/css/layout.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('vendor/marble/assets/css/elements.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('vendor/marble/assets/css/custom.css') }}"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="//fonts.googleapis.com/css?family=Open+Sans:400,600,700,300|Titillium+Web:200,300,400" rel="stylesheet" type="text/css">
    <script type="text/javascript" src="{{ asset('vendor/marble/assets/javascript/jquery.js') }}"></script>
</head>
<body class="theme-blue-gradient">
    <header class="navbar" id="header-navbar">
        <div class="container">
            <a href="#" id="logo" class="navbar-brand">
                <img src="{{ asset('vendor/marble/assets/images/logo.png') }}" alt="" class="normal-logo logo-white">
            </a>
            <div class="clearfix"></div>
        </div>
    </header>
    <div id="page-wrapper" class="container container-login">
        <div class="row">
            <div id="content-wrapper">
                <div class="row">
                    <div class="col-sm-4">&nbsp;</div>
                    <div class="col-sm-4">
                        @yield('content')
                    </div>
                    <div class="col-sm-4">&nbsp;</div>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript" src="{{ asset('vendor/marble/assets/js/bootstrap.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('vendor/marble/assets/js/scripts.js') }}"></script>
</body>
</html>
