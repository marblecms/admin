<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN'
'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='de' lang='de'>
    <head>
		<title>Administration</title>
		<meta http-equiv='Content-type' content='text/html; charset=utf-8' />
        <link rel='stylesheet' href='{{ URL::asset('vendor/admin/css/bootstrap.min.css') }}'/>
        <link rel='stylesheet' href='{{ URL::asset('vendor/admin/css/bootstrap.datepicker.css') }}'/>
        <link rel='stylesheet' href='{{ URL::asset('vendor/admin/css/font-awesome.css') }}'/>
        <link rel='stylesheet' href='{{ URL::asset('vendor/admin/css/layout.min.css') }}'/>
        <link rel='stylesheet' href='{{ URL::asset('vendor/admin/css/elements.min.css') }}'/>
        <link rel='stylesheet' href='{{ URL::asset('vendor/admin/css/morris.css') }}'/>
        <link rel='stylesheet' href='{{ URL::asset('vendor/admin/css/jquery-ui.css') }}'/>
        <link rel='stylesheet' href='{{ URL::asset('vendor/admin/css/custom.css') }}'/>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        

        <link href='//fonts.googleapis.com/css?family=Open+Sans:400,600,700,300|Titillium+Web:200,300,400' rel='stylesheet' type='text/css'>

        <script type="text/javascript" src="{{ URL::asset('vendor/admin/js/jquery.js') }}"></script>
	</head>
    <body class="theme-blue-gradient">
        
        <header class="navbar" id="header-navbar">
            <div class="container">
                <a href="index.html" id="logo" class="navbar-brand">
                    <img src="{{URL::asset('vendor/admin/images/logo.png')}}" alt="" class="normal-logo logo-white">
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
        

        <script type="text/javascript" src="{{ URL::asset('vendor/admin/js/bootstrap.min.js') }}"></script>
        <script type="text/javascript" src="{{ URL::asset('vendor/admin/js/scripts.js') }}"></script>
        
	</body>
</html>