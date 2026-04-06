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
    <link rel="stylesheet" href="{{ asset('vendor/marble/assets/css/admin-theme-' . $adminTheme . '.css') }}"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
                <img src="{{ asset('vendor/marble/assets/images/logo.png') }}" class="marble-mr-xs" />
                Marble
            </a>
            <div class="clearfix">
                @php
                    $isContent   = request()->routeIs('marble.media.*', 'marble.trash.*', 'marble.redirect.*', 'marble.item.import*', 'marble.package.*');
                    $isStructure = request()->routeIs('marble.blueprint.*', 'marble.site.*', 'marble.workflow.*');
                    $isUsers     = request()->routeIs('marble.user.*', 'marble.user-group.*', 'marble.portal-user.*');
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
                                <li><a href="{{ route('marble.portal-user.index') }}">@include('marble::components.famicon', ['name' => 'user']) {{ trans('marble::admin.portal_users') }}</a></li>
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
                            <a class="btn dropdown-toggle marble-bell-btn" data-toggle="dropdown" href="#" id="notification-bell">
                                @include('marble::components.famicon', ['name' => 'bell'])
                                <span id="notification-badge" class="marble-hidden"></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-right marble-notif-dropdown" id="notification-dropdown">
                                <li class="marble-notif-header marble-flex-between">
                                    <strong class="marble-text-sm">{{ trans('marble::admin.notifications') }}</strong>
                                    <a href="#" id="mark-all-read" class="marble-meta">{{ trans('marble::admin.mark_all_read') }}</a>
                                </li>
                                <li id="notification-list-empty" class="marble-notif-empty">
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
                            <ul class="dropdown-menu dropdown-menu-right marble-lang-dropdown">
                                @foreach($adminLanguages as $lang)
                                    <li class="marble-lang-item">
                                        <form method="POST" action="{{ route('marble.user.set-language') }}" class="marble-mb-0">
                                            @csrf
                                            <input type="hidden" name="language" value="{{ $lang->code }}">
                                            <button type="submit" class="marble-lang-btn {{ ($currentUser->language ?? 'en') === $lang->code ? 'is-current' : '' }}">
                                                {{ $lang->name }} ({{ strtoupper($lang->code) }})
                                            </button>
                                        </form>
                                    </li>
                                @endforeach
                            </ul>
                        </li>

                        {{-- User dropdown --}}
                        <li class="dropdown">
                            <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
                                @include('marble::components.famicon', ['name' => 'user'])
                                <span class="marble-mr-xs">{{ $currentUser->name }}</span>
                                <small class="text-muted marble-mr-xs">{{ $currentUser->userGroup?->name ?? 'Admin' }}</small>
                                <span class="caret"></span>
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
                    <div class="collapse navbar-collapse navbar-ex1-collapse" id="sidebar-nav">
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
                    <div id="media-browser-grid" class="media-library-grid marble-browser-grid">
                        <p class="text-muted text-center marble-browser-loading">Loading...</p>
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
                $grid.html('<p class="text-muted text-center marble-browser-loading">Loading...</p>');
                $.getJSON('{{ route('marble.media.json') }}', function(items) {
                    $grid.html('');
                    if (!items.length) {
                        $grid.html('<p class="text-muted text-center marble-browser-loading">{{ trans('marble::admin.no_media') }}</p>');
                        return;
                    }
                    $.each(items, function(i, media) {
                        var $item = $('<div class="media-library-item media-browser-selectable" ></div>');
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
                    var $li = $('<li class="notif-item marble-notif-item' + (n.read ? '' : ' marble-notif-item-unread') + '">');
                    var inner = '<a href="' + (n.url || '#') + '" class="marble-notif-link">' +
                        '<div class="marble-flex-between">' +
                        '<strong class="marble-text-sm">' + $('<div>').text(n.title).html() + '</strong>' +
                        '<small class="marble-meta marble-nowrap">' + n.created_at + '</small>' +
                        '</div>' +
                        (n.body ? '<div class="marble-notif-body">' + $('<div>').text(n.body).html() + '</div>' : '') +
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

            $('#notification-bell-li').on('mouseenter click', function () {
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
            if (!$(textarea).hasClass("rich-text-editor")) {
                return false;
            }
            var rows = $(textarea).attr('rows') || 10;
            
            // Höhe berechnen: Zeilenanzahl * Pixel pro Zeile (ca. 20-25px ist ein guter Richtwert)
            config.height = (rows * 20) + "px";
            
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
        });
    </script>

    {{-- Toast notification --}}
    <div id="marble-toast"></div>
    @if(session('success') || session('error'))
    <script>
        $(function() {
            var $t = $('#marble-toast');
            @if(session('error'))
            $t.text({{ Js::from(session('error')) }}).addClass('marble-toast-error');
            @else
            $t.text({{ Js::from(session('success')) }});
            @endif
            $t.addClass('marble-toast-show');
            setTimeout(function() { $t.removeClass('marble-toast-show'); }, 3500);
        });
    </script>
    @endif
</body>
</html>
