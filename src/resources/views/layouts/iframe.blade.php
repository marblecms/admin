<!doctype html>
<html>
    <head>
		<title>Administration</title>
		<meta http-equiv='Content-type' content='text/html; charset=utf-8' />
        <link rel='stylesheet' href='{{ URL::asset('assets/admin/css/bootstrap.min.css') }}'/>
        <link rel='stylesheet' href='{{ URL::asset('assets/admin/css/bootstrap.datepicker.css') }}'/>
        <link rel='stylesheet' href='{{ URL::asset('assets/admin/css/font-awesome.css') }}'/>
        <link rel='stylesheet' href='{{ URL::asset('assets/admin/css/layout.min.css') }}'/>
        <link rel='stylesheet' href='{{ URL::asset('assets/admin/css/elements.min.css') }}'/>
        <link rel='stylesheet' href='{{ URL::asset('assets/admin/css/morris.css') }}'/>
        <link rel='stylesheet' href='{{ URL::asset('assets/admin/css/jquery-ui.css') }}'/>
        <link rel='stylesheet' href='{{ URL::asset('assets/admin/css/cropper.css') }}'/>
        <link rel='stylesheet' href='{{ URL::asset('assets/admin/css/custom.css') }}'/>
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <link href='//fonts.googleapis.com/css?family=Open+Sans:400,600,700,300|Titillium+Web:200,300,400' rel='stylesheet' type='text/css'>

        <script type="text/javascript" src="{{ URL::asset('assets/admin/js/jquery.js') }}"></script>
        <script type="text/javascript" src="{{ URL::asset('assets/admin/js/jquery-ui.js') }}"></script>


        <script type="text/javascript" src="{{ URL::asset('assets/admin/js/object-browser.js') }}"></script>
        <script type="text/javascript" src="{{ URL::asset('assets/admin/js/cropper.js') }}"></script>
        <script type="text/javascript" src="{{ URL::asset('assets/admin/js/image-editor.js') }}"></script>

        @yield("javascript-head")

        <script>
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        </script>
    </head>
    <body class="x-theme-blue iframe">
        <div id="page-wrapper" class="container">
            @yield('content')
        </div>

        <div class="modal fade" id="object-browser-modal-add">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">{{trans("admin.select_object")}}</h4>
                    </div>
                    <div class="modal-body">
                        <div class="collapse navbar-collapse navbar-ex1-collapse" id="sidebar-nav" style="background:#2c3e50">
                            @include("admin::layouts.tree", array("nodes" => \App\TreeHelper::generate(), "isRoot" => true, "isModal" => true, "selectedNode" => null))
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">{{trans("admin.cancel")}}</button>
                    </div>
                </div>
            </div>
        </div>
        
        <script type="text/javascript" src="{{ URL::asset('assets/admin/js/bootstrap.min.js') }}"></script>
        <script type="text/javascript" src="{{ URL::asset('assets/admin/js/bootstrap.datepicker.js') }}"></script>
        <script type="text/javascript" src="{{ URL::asset('assets/admin/js/scripts.js') }}"></script>
        <script type="text/javascript" src="{{ URL::asset('assets/admin/ckeditor/ckeditor.js') }}"></script>

        @yield("javascript")

        <script type="text/javascript">

            ObjectBrowser.init();
            ImageEditor.init();

            $(".datepicker").datepicker();

        </script>
    </body>
</html>