<!DOCTYPE html>
<html lang="en">
<head>
    <title>{{ trans('marble::admin.title') }}</title>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" href="{{ asset('vendor/marble/assets/css/bootstrap.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('vendor/marble/assets/css/bootstrap.datepicker.css') }}"/>
    <link rel="stylesheet" href="{{ asset('vendor/marble/assets/css/font-awesome.css') }}"/>
    <link rel="stylesheet" href="{{ asset('vendor/marble/assets/css/layout.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('vendor/marble/assets/css/elements.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('vendor/marble/assets/css/morris.css') }}"/>
    <link rel="stylesheet" href="{{ asset('vendor/marble/assets/css/jquery-ui.css') }}"/>
    <link rel="stylesheet" href="{{ asset('vendor/marble/assets/css/cropper.css') }}"/>
    <link rel="stylesheet" href="{{ asset('vendor/marble/assets/css/custom.css') }}"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="//fonts.googleapis.com/css?family=Open+Sans:400,600,700,300|Titillium+Web:200,300,400" rel="stylesheet" type="text/css">
    <script type="text/javascript" src="{{ asset('vendor/marble/assets/javascript/jquery.js') }}"></script>
    <script type="text/javascript" src="{{ asset('vendor/marble/assets/js/jquery-ui.js') }}"></script>
    <script type="text/javascript" src="{{ asset('vendor/marble/assets/js/object-browser.js') }}"></script>
    <script type="text/javascript" src="{{ asset('vendor/marble/assets/js/cropper.js') }}"></script>
    <script type="text/javascript" src="{{ asset('vendor/marble/assets/js/image-editor.js') }}"></script>
    <script type="text/javascript" src="{{ asset('vendor/marble/assets/ckeditor/ckeditor.js') }}"></script>
    @yield('javascript-head')
    <script>
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });
    </script>
</head>
<body class="x-theme-blue">
    @php
        $prefix = config('marble.route_prefix', 'admin');
        $entryItemId = config('marble.entry_item_id', 1);
        $currentUser = Auth::guard('marble')->user();
    @endphp

    <header class="navbar" id="header-navbar">
        <div class="container">
            <a href="{{ url("{$prefix}/item/edit/{$entryItemId}") }}" id="logo" class="navbar-brand">
                <img src="{{ asset('vendor/marble/assets/images/logo.png') }}" style="margin-right: 10px" /> 
                Marble
            </a>
            <div class="clearfix">
                @php
                    $isContent   = request()->routeIs('marble.media.*', 'marble.trash.*', 'marble.redirect.*', 'marble.item.import*', 'marble.package.*');
                    $isStructure = request()->routeIs('marble.blueprint.*', 'marble.site.*', 'marble.workflow.*');
                    $isUsers     = request()->routeIs('marble.user.*', 'marble.user-group.*');
                    $isSystem    = request()->routeIs('marble.activity-log.*', 'marble.webhook.*', 'marble.api-token.*', 'marble.configuration.*');
                    $isDashboard = request()->routeIs('marble.dashboard');
                @endphp
                <div class="nav-no-collapse navbar-left pull-left hidden-sm hidden-xs">
                    <ul class="nav navbar-nav pull-left">
                        <li class="{{ $isDashboard ? 'active' : '' }}">
                            <a class="btn" href="{{ url("{$prefix}/dashboard") }}">
                                @include('marble::components.famicon', ['name' => 'house'])
                                <span>{{ trans('marble::admin.dashboard') }}</span>
                            </a>
                        </li>

                        {{-- Content --}}
                        <li class="dropdown {{ $isContent ? 'active' : '' }}">
                            <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
                                @include('marble::components.famicon', ['name' => 'folder_page'])
                                <span>{{ trans('marble::admin.content') }}</span>
                                <span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a href="{{ route('marble.media.index') }}">@include('marble::components.famicon', ['name' => 'pictures']) {{ trans('marble::admin.media_library') }}</a></li>
                                <li><a href="{{ route('marble.trash.index') }}">@include('marble::components.famicon', ['name' => 'bin']) {{ trans('marble::admin.trash') }}</a></li>
                                <li><a href="{{ route('marble.redirect.index') }}">@include('marble::components.famicon', ['name' => 'arrow_right']) {{ trans('marble::admin.redirects') }}</a></li>
                                <li role="separator" class="divider"></li>
                                <li><a href="{{ route('marble.item.import-form') }}">@include('marble::components.famicon', ['name' => 'page_white_paste']) {{ trans('marble::admin.import') }}</a></li>
                                <li><a href="{{ route('marble.package.import') }}">@include('marble::components.famicon', ['name' => 'box']) {{ trans('marble::admin.packages') }}</a></li>
                            </ul>
                        </li>

                        {{-- Structure --}}
                        <li class="dropdown {{ $isStructure ? 'active' : '' }}">
                            <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
                                @include('marble::components.famicon', ['name' => 'brick'])
                                <span>{{ trans('marble::admin.structure') }}</span>
                                <span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a href="{{ route('marble.blueprint.index') }}">@include('marble::components.famicon', ['name' => 'brick']) {{ trans('marble::admin.classes') }}</a></li>
                                <li><a href="{{ route('marble.workflow.index') }}">@include('marble::components.famicon', ['name' => 'chart_bar']) {{ trans('marble::admin.workflows') }}</a></li>
                                <li><a href="{{ route('marble.site.index') }}">@include('marble::components.famicon', ['name' => 'application_xp']) {{ trans('marble::admin.sites') }}</a></li>
                            </ul>
                        </li>

                        {{-- Users --}}
                        <li class="dropdown {{ $isUsers ? 'active' : '' }}">
                            <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
                                @include('marble::components.famicon', ['name' => 'status_online'])
                                <span>{{ trans('marble::admin.users') }}</span>
                                <span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a href="{{ route('marble.user.index') }}">@include('marble::components.famicon', ['name' => 'status_online']) {{ trans('marble::admin.users') }}</a></li>
                                <li><a href="{{ route('marble.user-group.index') }}">@include('marble::components.famicon', ['name' => 'group']) {{ trans('marble::admin.usergroups') }}</a></li>
                            </ul>
                        </li>

                        {{-- System --}}
                        <li class="dropdown {{ $isSystem ? 'active' : '' }}">
                            <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
                                @include('marble::components.famicon', ['name' => 'server'])
                                <span>{{ trans('marble::admin.system') }}</span>
                                <span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a href="{{ route('marble.configuration.index') }}">@include('marble::components.famicon', ['name' => 'wrench']) {{ trans('marble::admin.configuration') }}</a></li>
                                <li role="separator" class="divider"></li>
                                <li><a href="{{ route('marble.activity-log.index') }}">@include('marble::components.famicon', ['name' => 'time']) {{ trans('marble::admin.activity_log') }}</a></li>
                                <li><a href="{{ route('marble.webhook.index') }}">@include('marble::components.famicon', ['name' => 'connect']) {{ trans('marble::admin.webhooks') }}</a></li>
                                <li><a href="{{ route('marble.api-token.index') }}">@include('marble::components.famicon', ['name' => 'key']) API Tokens</a></li>
                                <li><a href="{{ route('marble.package.export') }}">@include('marble::components.famicon', ['name' => 'box']) {{ trans('marble::admin.packages') }}</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
                <div class="nav-no-collapse pull-right" id="header-nav">
                    <ul class="nav navbar-nav pull-right">
                        <li class="autocomplete-container">
                            <input type="text" class="form-control" id="search-field" placeholder="{{ trans('marble::admin.search_placeholder') }}" />
                            <ul class="list-group"></ul>
                        </li>
                        {{-- Notification bell --}}
                        <li class="dropdown" id="notification-bell-li">
                            <a class="btn dropdown-toggle" data-toggle="dropdown" href="#" id="notification-bell" style="position:relative">
                                @include('marble::components.famicon', ['name' => 'bell'])
                                <span id="notification-badge" style="display:none; position:absolute; top:4px; right:4px; background:#d9534f; color:#fff; border-radius:50%; width:16px; height:16px; font-size:10px; line-height:16px; text-align:center; font-weight:bold"></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-right" id="notification-dropdown" style="min-width:320px; max-height:400px; overflow-y:auto; padding:0; border-radius:3px">
                                <li style="padding:10px 15px; border-bottom:1px solid #eee; display:flex; align-items:center; justify-content:space-between">
                                    <strong style="font-size:13px">{{ trans('marble::admin.notifications') }}</strong>
                                    <a href="#" id="mark-all-read" style="font-size:12px; color:#999">{{ trans('marble::admin.mark_all_read') }}</a>
                                </li>
                                <li id="notification-list-empty" style="padding:20px 15px; text-align:center; color:#999; font-size:13px">
                                    {{ trans('marble::admin.no_notifications') }}
                                </li>
                            </ul>
                        </li>

                        {{-- Language switcher --}}
                        @php $adminLanguages = \Marble\Admin\Models\Language::all(); @endphp
                        <li class="dropdown">
                            <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
                                @include('marble::components.famicon', ['name' => 'world'])
                                {{ strtoupper($currentUser->language ?? 'en') }}
                                <span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-right" style="min-width:160px;padding:0;border-radius:3px;overflow:hidden">
                                @foreach($adminLanguages as $lang)
                                    <li style="display:block;border-bottom:1px solid #e7ebee;margin:0">
                                        <form method="POST" action="{{ route('marble.user.set-language') }}" style="margin:0">
                                            @csrf
                                            <input type="hidden" name="language" value="{{ $lang->code }}">
                                            <button type="submit" style="display:block;width:100%;text-align:left;padding:0 20px;height:40px;line-height:40px;border:none;background:{{ ($currentUser->language ?? 'en') === $lang->code ? '#f0f4f8' : 'none' }};font-size:12px;cursor:pointer;font-weight:{{ ($currentUser->language ?? 'en') === $lang->code ? 'bold' : 'normal' }}">
                                                {{ $lang->name }} ({{ strtoupper($lang->code) }})
                                            </button>
                                        </form>
                                    </li>
                                @endforeach
                            </ul>
                        </li>

                        {{-- User dropdown --}}
                        <li class="dropdown">
                            <a class="btn dropdown-toggle" data-toggle="dropdown" href="#" style="padding-top:8px;padding-bottom:8px">
                                @include('marble::components.famicon', ['name' => 'user'])
                                <span style="margin-left:4px">{{ $currentUser->name }}</span>
                                <small class="text-muted" style="margin-left:4px">{{ $currentUser->userGroup?->name ?? 'Admin' }}</small>
                                <span class="caret" style="margin-left:4px"></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <li><a href="{{ url("{$prefix}/user/edit/{$currentUser->id}") }}">@include('marble::components.famicon', ['name' => 'user_edit']) {{ trans('marble::admin.profile') }}</a></li>
                                <li role="separator" class="divider"></li>
                                <li><a href="{{ url("{$prefix}/logout") }}">@include('marble::components.famicon', ['name' => 'disconnect']) {{ trans('marble::admin.logout') }}</a></li>
                            </ul>
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
                            @include('marble::layouts.tree', ['nodes' => \Marble\Admin\Helpers\TreeHelper::generate(), 'isRoot' => true, 'isModal' => false, 'selectedItem' => isset($item) ? $item->id : -1])
                        </div>
                    </div>
                </section>
            </div>

            <div id="content-wrapper">
                <div class="row">
                    <div class="@yield('content_class', 'col-lg-9')">
                        @yield('content')
                    </div>
                    @hasSection('content_class')
                    @else
                    <div class="col-lg-3">
                        @yield('sidebar')
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Object Browser Modal --}}
    <div class="modal fade" id="object-browser-modal-add">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{{ trans('marble::admin.select_object') }}</h4>
                </div>
                <div class="modal-body">
                    <div class="collapse navbar-collapse navbar-ex1-collapse" id="sidebar-nav" style="background:#2c3e50">
                        @include('marble::layouts.tree', ['nodes' => \Marble\Admin\Helpers\TreeHelper::generate(), 'isRoot' => true, 'isModal' => true, 'selectedItem' => null])
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">@include('marble::components.famicon', ['name' => 'cancel']) {{ trans('marble::admin.cancel') }}</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Image Editor Modal --}}
    <div class="modal fade" id="image-editor-modal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{{ trans('marble::admin.edit_image') }}</h4>
                </div>
                <div class="modal-body">
                    <div id="image-editor">
                        <div class="canvas"><img class="editor-image" /></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">@include('marble::components.famicon', ['name' => 'cancel']) {{ trans('marble::admin.cancel') }}</button>
                    <button type="button" class="btn save-image btn-success" data-dismiss="modal">@include('marble::components.famicon', ['name' => 'disk']) {{ trans('marble::admin.save') }}</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Media Browser Modal --}}
    <div class="modal fade" id="media-browser-modal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{{ trans('marble::admin.select_from_library') }}</h4>
                </div>
                <div class="modal-body">
                    <div id="media-browser-grid" class="media-library-grid" style="min-height:200px">
                        <p class="text-muted text-center" style="padding:40px 0">Loading...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">
                        @include('marble::components.famicon', ['name' => 'cancel']) {{ trans('marble::admin.cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript" src="{{ asset('vendor/marble/assets/js/bootstrap.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('vendor/marble/assets/js/bootstrap.datepicker.js') }}"></script>
    <script type="text/javascript" src="{{ asset('vendor/marble/assets/js/scripts.js') }}"></script>

    @yield('javascript')

    <script type="text/javascript">
        ObjectBrowser.init();
        ImageEditor.init();

        // Media library browser
        var MarbleMedia = {
            _callback: null,
            open: function(callback) {
                this._callback = callback;
                var $grid = $('#media-browser-grid');
                $grid.html('<p class="text-muted text-center" style="padding:40px 0">Loading...</p>');
                $.getJSON('{{ route('marble.media.json') }}', function(items) {
                    $grid.html('');
                    if (!items.length) {
                        $grid.html('<p class="text-muted text-center" style="padding:40px 0">{{ trans('marble::admin.no_media') }}</p>');
                        return;
                    }
                    $.each(items, function(i, media) {
                        var $item = $('<div class="media-library-item media-browser-selectable" style="cursor:pointer"></div>');
                        $item.append('<div class="media-library-thumb"><img src="' + media.thumbnail + '" loading="lazy" /></div>');
                        $item.append('<div class="media-library-info"><div class="media-library-name" title="' + media.original_filename + '">' + media.original_filename + '</div></div>');
                        $item.data('media', media);
                        $grid.append($item);
                    });
                });
                $('#media-browser-modal').modal('show');
            }
        };

        $(document).on('click', '.media-browser-selectable', function() {
            var media = $(this).data('media');
            if (MarbleMedia._callback) MarbleMedia._callback(media);
            $('#media-browser-modal').modal('hide');
        });
        $(".datepicker").datepicker();

        // Notification bell polling
        (function () {
            var countUrl  = '{{ route('marble.notification.count') }}';
            var recentUrl = '{{ route('marble.notification.recent') }}';
            var markAllUrl= '{{ route('marble.notification.mark-all-read') }}';
            var csrfToken = $('meta[name="csrf-token"]').attr('content');

            function renderNotifications(items) {
                var $list  = $('#notification-dropdown');
                var $empty = $('#notification-list-empty');
                $list.find('.notif-item').remove();

                if (!items.length) {
                    $empty.show();
                    return;
                }
                $empty.hide();

                items.forEach(function (n) {
                    var $li = $('<li class="notif-item" style="border-bottom:1px solid #f0f0f0">')
                        .css('background', n.read ? '' : '#f8fbff');
                    var inner = '<a href="' + (n.url || '#') + '" style="display:block; padding:10px 15px; white-space:normal">' +
                        '<div style="display:flex; justify-content:space-between; gap:8px">' +
                        '<strong style="font-size:13px">' + $('<div>').text(n.title).html() + '</strong>' +
                        '<small style="color:#aaa; white-space:nowrap">' + n.created_at + '</small>' +
                        '</div>' +
                        (n.body ? '<div style="font-size:12px; color:#666; margin-top:2px">' + $('<div>').text(n.body).html() + '</div>' : '') +
                        '</a>';
                    $li.html(inner);
                    $list.append($li);
                });
            }

            function pollCount() {
                $.getJSON(countUrl, function (data) {
                    var $badge = $('#notification-badge');
                    if (data.count > 0) {
                        $badge.text(data.count > 9 ? '9+' : data.count).show();
                    } else {
                        $badge.hide();
                    }
                });
            }

            $('#notification-bell').on('click', function () {
                $.getJSON(recentUrl, function (items) {
                    renderNotifications(items);
                });
            });

            $('#mark-all-read').on('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                $.post(markAllUrl, { _token: csrfToken }, function () {
                    $('#notification-badge').hide();
                    $('#notification-dropdown .notif-item').css('background', '');
                    pollCount();
                });
            });

            pollCount();
            setInterval(pollCount, 30000);
        })();

        CKEDITOR.plugins.addExternal('marblelink', '{{ asset('vendor/marble/assets/ckeditor/plugins/marblelink/') }}/', 'plugin.js');

        CKEDITOR.replaceAll(function(textarea, config) {
            if ($(textarea).hasClass("rich-text-editor")) {
                config.extraPlugins = 'marblelink';
                config.filebrowserImageUploadUrl = '{{ route('marble.media.ckeditor-upload') }}?_token={{ csrf_token() }}';
                config.toolbar = [
                    { name: 'clipboard', items: ['Undo', 'Redo'] },
                    { name: 'styles', items: ['Styles', 'Format'] },
                    { name: 'basicstyles', items: ['Bold', 'Italic', 'Strike', '-', 'RemoveFormat'] },
                    { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote'] },
                    { name: 'links', items: ['Link', 'Unlink', 'MarbleLink'] },
                    { name: 'insert', items: ['Image', 'Table'] },
                    { name: 'tools', items: ['Maximize'] },
                ];
            }
        });
    </script>
</body>
</html>
