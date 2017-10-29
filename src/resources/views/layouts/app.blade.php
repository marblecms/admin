<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN'
'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='de' lang='de'>
    <head>
		<title>{{trans("admin::admin.title")}}</title>
		<meta http-equiv='Content-type' content='text/html; charset=utf-8' />
        <link rel='stylesheet' href='{{ URL::asset('vendor/admin/css/bootstrap.min.css') }}'/>
        <link rel='stylesheet' href='{{ URL::asset('vendor/admin/css/bootstrap.datepicker.css') }}'/>
        <link rel='stylesheet' href='{{ URL::asset('vendor/admin/css/font-awesome.css') }}'/>
        <link rel='stylesheet' href='{{ URL::asset('vendor/admin/css/layout.min.css') }}'/>
        <link rel='stylesheet' href='{{ URL::asset('vendor/admin/css/elements.min.css') }}'/>
        <link rel='stylesheet' href='{{ URL::asset('vendor/admin/css/morris.css') }}'/>
        <link rel='stylesheet' href='{{ URL::asset('vendor/admin/css/jquery-ui.css') }}'/>
        <link rel='stylesheet' href='{{ URL::asset('vendor/admin/css/cropper.css') }}'/>
        <link rel='stylesheet' href='{{ URL::asset('vendor/admin/css/custom.css') }}'/>
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <link href='//fonts.googleapis.com/css?family=Open+Sans:400,600,700,300|Titillium+Web:200,300,400' rel='stylesheet' type='text/css'>

        <script type="text/javascript" src="{{ URL::asset('vendor/admin/js/jquery.js') }}"></script>
        <script type="text/javascript" src="{{ URL::asset('vendor/admin/js/jquery-ui.js') }}"></script>


        <script type="text/javascript" src="{{ URL::asset('vendor/admin/js/object-browser.js') }}"></script>
        <script type="text/javascript" src="{{ URL::asset('vendor/admin/js/cropper.js') }}"></script>
        <script type="text/javascript" src="{{ URL::asset('vendor/admin/js/image-editor.js') }}"></script>

        <script type="text/javascript" src="{{ URL::asset('vendor/admin/ckeditor/ckeditor.js') }}"></script>

        @yield("javascript-head")

        <script>
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        </script>
	</head>
    <body class="x-theme-blue">
        
        <header class="navbar" id="header-navbar">
            <div class="container">
                <a href="{{url("admin/node/edit/" . \Marble\Admin\App\Helpers\PermissionHelper::entryNodeId())}}" id="logo" class="navbar-brand">
                    <img src="{{URL::asset('vendor/admin/images/logo.png')}}" alt="" class="normal-logo logo-white">
                </a>
                <div class="clearfix">
                    <button class="navbar-toggle" data-target=".navbar-ex1-collapse" data-toggle="collapse" type="button">
                        <span class="sr-only">
                            Toggle navigation
                        </span>
                        <span class="fa fa-bars">
                        </span>
                    </button>
                    <div class="nav-no-collapse navbar-left pull-left hidden-sm hidden-xs">
                        <ul class="nav navbar-nav pull-left">
                            <li>
                                <a class="btn" href="{{url("admin/dashboard")}}">
                                    <i class="fa fa-dashboard">
                                    </i>
                                    <span>{{trans('admin::admin.dashboard')}}</span>
                                </a>
                            </li>
                            @if(Marble\Admin\App\Helpers\PermissionHelper::allowed("listClass"))
                                <li>
                                    <a class="btn" href="{{url("admin/nodeclass/all")}}">
                                        <i class="fa fa-folder">
                                        </i>
                                        <span>{{trans('admin::admin.classes')}}</span>
                                    </a>
                                </li>
                            @endif
                            @if(Marble\Admin\App\Helpers\PermissionHelper::allowed("listUser"))
                                <li>
                                    <a class="btn" href="{{url("admin/user/all")}}">
                                        <i class="fa fa-user">
                                        </i>
                                        <span>{{trans('admin::admin.users')}}</span>
                                    </a>
                                </li>
                            @endif
                            @if(Marble\Admin\App\Helpers\PermissionHelper::allowed("listGroup"))
                                <li>
                                    <a class="btn" href="{{url("admin/usergroup/all")}}">
                                        <i class="fa fa-users">
                                        </i>
                                        <span>{{trans('admin::admin.usergroups')}}</span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>
                    <div class="nav-no-collapse pull-right" id="header-nav">
                        <ul class="nav navbar-nav pull-right">
                            <li class="autocomplete-container">
                                <input type="text" class="form-control" id="search-field" placeholder="{{trans('admin::admin.search_placeholder')}}" />
                                <ul class="list-group">
                                </ul>
                            </li>
                            <li class="hidden-xxs">
                                <a class="btn" href="{{url("/admin/logout")}}">
                                    <i class="fa fa-power-off">
                                    </i>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </header>
        
        <div id="page-wrapper" class="container">
            <div class="row">
                <div id="nav-col">
                    <section id="col-left" class="col-left-nano">
                        <div id="col-left-inner" class="col-left-nano-content">
                            <div class="collapse navbar-collapse navbar-ex1-collapse" id="sidebar-nav">


                                @include("admin::layouts.tree", array("nodes" => \Marble\Admin\App\Helpers\TreeHelper::generate(), "isRoot" => true, "isModal" => false, "selectedNode" => isset($node) ? $node->id : -1))


                            </div>
                        </div>
                    </section>
                </div>
                
                
                <div id="content-wrapper">
                    <div class="row">
                        <div class="col-lg-9">
                            @yield('content')
                        </div>
                        <div class="col-lg-3">
                            <h1>&nbsp;</h2>

                            @yield('sidebar')
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
        

        <div class="modal fade" id="object-browser-modal-add">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">{{trans("admin::admin.select_object")}}</h4>
                    </div>
                    <div class="modal-body">
                        <div class="collapse navbar-collapse navbar-ex1-collapse" id="sidebar-nav" style="background:#2c3e50">
                            @include("admin::layouts.tree", array("nodes" => \Marble\Admin\App\Helpers\TreeHelper::generate(), "isRoot" => true, "isModal" => true, "selectedNode" => null))
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">{{trans("admin::admin.cancel")}}</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="object-browser-modal-create">
            <div class="modal-dialog modal-max">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">{{trans("admin::admin.create_object")}}</h4>
                    </div>
                    <div class="modal-body">
                           <iframe data-src="{{url("admin/node/addiframe")}}" width="100%" style="height:600px" frameborder="0" ></iframe>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">{{trans("admin::admin.cancel")}}</button>
                        <button type="button" class="btn save-created-object btn-success disabled" data-dismiss="modal">{{trans("admin::admin.save")}}</button>
                    </div>
                </div>
            </div>
        </div>


        <div class="modal fade" id="image-editor-modal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">{{trans("admin::admin.edit_image")}}</h4>
                    </div>
                    <div class="modal-body">
                        <div id="image-editor">
                            <div class="canvas">
                                <img class="editor-image" />
                            </div>
                        </div>
                        
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">{{trans("admin::admin.cancel")}}</button>
                        <button type="button" class="btn save-image btn-success" data-dismiss="modal">{{trans("admin::admin.save")}}</button>
                    </div>
                </div>
            </div>
        </div>

        <script type="text/javascript" src="{{ URL::asset('vendor/admin/js/bootstrap.min.js') }}"></script>
        <script type="text/javascript" src="{{ URL::asset('vendor/admin/js/bootstrap.datepicker.js') }}"></script>
        <script type="text/javascript" src="{{ URL::asset('vendor/admin/js/scripts.js') }}"></script>

        @yield("javascript")

        <script type="text/javascript">

            ObjectBrowser.init();
            ImageEditor.init();

            $(".datepicker").datepicker();
            
            CKEDITOR.replaceAll(function( textarea, config ) {
                if( $(textarea).hasClass("rich-text-editor") ){
                    config.toolbar = [
            			{ name: 'clipboard', items: [ 'Undo', 'Redo' ] },
            			{ name: 'styles', items: [ 'Styles', 'Format' ] },
            			{ name: 'basicstyles', items: [ 'Bold', 'Italic', 'Strike', '-', 'RemoveFormat' ] },
            			{ name: 'paragraph', items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote' ] },
            			{ name: 'links', items: [ 'Link', 'Unlink' ] },
            			{ name: 'insert', items: [ 'Image', 'Table', 'MarbleLink' ] },
            			{ name: 'tools', items: [ 'Maximize' ] },
            			{ name: 'editing', items: [ 'Scayt' ] }
            		];
                    config.extraPlugins = 'marblelink';
                }
            });
        </script>
	</body>
</html>