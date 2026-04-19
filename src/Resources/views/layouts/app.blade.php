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
    <link rel="stylesheet" href="{{ asset('vendor/marble/assets/css/custom.css') }}?v=3"/>
    <link rel="stylesheet" href="{{ asset('vendor/marble/assets/css/admin-theme-' . $adminTheme . '.css') }}"/>
    @foreach(app('marble.admin')->getAssets('css') as $pluginCss)
    <link rel="stylesheet" href="{{ $pluginCss }}"/>
    @endforeach
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script type="text/javascript" src="{{ asset('vendor/marble/assets/javascript/jquery.js') }}"></script>
    <script type="text/javascript" src="{{ asset('vendor/marble/assets/js/jquery-ui.js') }}"></script>
    <script type="text/javascript" src="{{ asset('vendor/marble/assets/js/object-browser.js') }}"></script>
    <script type="text/javascript" src="{{ asset('vendor/marble/assets/js/cropper.js') }}"></script>
    <script type="text/javascript" src="{{ asset('vendor/marble/assets/js/image-editor.js') }}"></script>
    <script type="text/javascript" src="{{ asset('vendor/marble/assets/ckeditor/ckeditor.js') }}"></script>
    <script type="text/javascript" src="{{ asset('vendor/marble/assets/js/marble-media-picker.js') }}?v=3"></script>
    <script type="text/javascript" src="{{ asset('vendor/marble/assets/js/marble-media-folder-picker.js') }}"></script>
    @yield('javascript-head')
    <script>
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });
        var marbleMediaPickerJsonUrl = '{{ route('marble.media.picker-json') }}';
    </script>
</head>
<body class="x-theme-blue">
    @php
        $prefix = config('marble.route_prefix', 'admin');
        $entryItemId = config('marble.entry_item_id', 1);
        $currentUser = Auth::guard('marble')->user();
        $pluginRegistry = app('marble.admin');
    @endphp


    <header class="navbar" id="header-navbar">
        <div class="container">
            <a href="{{ url("{$prefix}/item/edit/{$entryItemId}") }}" id="logo" class="navbar-brand">
                <img src="{{ asset('vendor/marble/assets/images/logo.png') }}" class="marble-mr-xs" />
                Marble
            </a>
            <div class="clearfix">
                @php
                    $isContent      = request()->routeIs('marble.media.*', 'marble.trash.*', 'marble.redirect.*', 'marble.item.import*', 'marble.calendar.*', 'marble.bundle.*', ...$pluginRegistry->getNavActivePatterns('content'));
                    $isStructure    = request()->routeIs('marble.blueprint.*', 'marble.site.*', 'marble.workflow.*', ...$pluginRegistry->getNavActivePatterns('structure'));
                    $isUsers        = request()->routeIs('marble.user.*', 'marble.user-group.*', 'marble.portal-user.*', ...$pluginRegistry->getNavActivePatterns('users'));
                    $isSystem       = request()->routeIs('marble.activity-log.*', 'marble.webhook.*', 'marble.api-token.*', 'marble.configuration.*', 'marble.plugin.*', 'marble.package.*', ...$pluginRegistry->getNavActivePatterns('system'));
                    $isDashboard    = request()->routeIs('marble.dashboard');
                    $topNavSections = $pluginRegistry->getTopNavSections();
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
                                <li><a href="{{ route('marble.calendar.index') }}">@include('marble::components.famicon', ['name' => 'date']) {{ trans('marble::admin.calendar') }}</a></li>
                                <li><a href="{{ route('marble.bundle.index') }}">@include('marble::components.famicon', ['name' => 'package']) {{ trans('marble::admin.bundles') }}</a></li>
                                <li><a href="{{ route('marble.trash.index') }}">@include('marble::components.famicon', ['name' => 'bin']) {{ trans('marble::admin.trash') }}</a></li>
                                <li><a href="{{ route('marble.redirect.index') }}">@include('marble::components.famicon', ['name' => 'arrow_right']) {{ trans('marble::admin.redirects') }}</a></li>
                                <li role="separator" class="divider"></li>
                                <li><a href="{{ route('marble.item.import-form') }}">@include('marble::components.famicon', ['name' => 'page_white_paste']) {{ trans('marble::admin.import') }}</a></li>
                                @foreach($pluginRegistry->getNavItems('content') as $pluginNav)
                                <li role="separator" class="divider"></li>
                                <li><a href="{{ route($pluginNav['route']) }}">@include('marble::components.famicon', ['name' => $pluginNav['icon']]) {{ $pluginNav['label'] }}</a></li>
                                @endforeach
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
                                @foreach($pluginRegistry->getNavItems('structure') as $pluginNav)
                                <li role="separator" class="divider"></li>
                                <li><a href="{{ route($pluginNav['route']) }}">@include('marble::components.famicon', ['name' => $pluginNav['icon']]) {{ $pluginNav['label'] }}</a></li>
                                @endforeach
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
                                @foreach($pluginRegistry->getNavItems('users') as $pluginNav)
                                <li role="separator" class="divider"></li>
                                <li><a href="{{ route($pluginNav['route']) }}">@include('marble::components.famicon', ['name' => $pluginNav['icon']]) {{ $pluginNav['label'] }}</a></li>
                                @endforeach
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
                                <li><a href="{{ route('marble.plugin.index') }}">@include('marble::components.famicon', ['name' => 'plugin']) {{ trans('marble::admin.plugins') }}</a></li>
                                <li role="separator" class="divider"></li>
                                <li><a href="{{ route('marble.activity-log.index') }}">@include('marble::components.famicon', ['name' => 'time']) {{ trans('marble::admin.activity_log') }}</a></li>
                                <li><a href="{{ route('marble.webhook.index') }}">@include('marble::components.famicon', ['name' => 'connect']) {{ trans('marble::admin.webhooks') }}</a></li>
                                <li><a href="{{ route('marble.api-token.index') }}">@include('marble::components.famicon', ['name' => 'key']) API Tokens</a></li>
                                <li><a href="{{ route('marble.package.index') }}">@include('marble::components.famicon', ['name' => 'box']) {{ trans('marble::admin.packages') }}</a></li>
                                @foreach($pluginRegistry->getNavItems('system') as $pluginNav)
                                <li role="separator" class="divider"></li>
                                <li><a href="{{ route($pluginNav['route']) }}">@include('marble::components.famicon', ['name' => $pluginNav['icon']]) {{ $pluginNav['label'] }}</a></li>
                                @endforeach
                            </ul>
                        </li>

                        {{-- Plugin top-level nav sections (e.g. Shop) --}}
                        @foreach($topNavSections as $topSection)
                        @php $isTopSection = request()->routeIs(...$topSection['patterns']); @endphp
                        <li class="dropdown {{ $isTopSection ? 'active' : '' }}">
                            <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
                                @include('marble::components.famicon', ['name' => $topSection['icon']])
                                <span>{{ $topSection['label'] }}</span>
                                <span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu">
                                @foreach($topSection['items'] as $topItem)
                                <li><a href="{{ route($topItem['route']) }}">@include('marble::components.famicon', ['name' => $topItem['icon']]) {{ $topItem['label'] }}</a></li>
                                @endforeach
                            </ul>
                        </li>
                        @endforeach
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
                                <li>
                                    <form method="POST" action="{{ route('marble.logout') }}" style="margin:0">
                                        @csrf
                                        <button type="submit" class="marble-logout-btn">@include('marble::components.famicon', ['name' => 'disconnect']) {{ trans('marble::admin.logout') }}</button>
                                    </form>
                                </li>
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

    <script type="text/javascript" src="{{ asset('vendor/marble/assets/js/bootstrap.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('vendor/marble/assets/js/bootstrap.datepicker.js') }}"></script>
    <script type="text/javascript" src="{{ asset('vendor/marble/assets/js/scripts.js') }}"></script>
    @foreach(app('marble.admin')->getAssets('js') as $pluginJs)
    <script type="text/javascript" src="{{ $pluginJs }}"></script>
    @endforeach

    @yield('javascript')

    <script type="text/javascript">
        ObjectBrowser.init();
        ImageEditor.init();

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

        window.MarbleFamiconUrl = '{{ asset('vendor/marble/assets/images/famicons') }}';
        CKEDITOR.plugins.addExternal('marblelink', '{{ asset('vendor/marble/assets/ckeditor/plugins/marblelink/') }}/', 'plugin.js');
        CKEDITOR.plugins.addExternal('marbleai',   '{{ asset('vendor/marble/assets/ckeditor/plugins/marbleai/') }}/',   'plugin.js');
        @foreach(app('marble.admin')->getCkEditorPlugins() as $ckPlugin)
        CKEDITOR.plugins.addExternal('{{ $ckPlugin['name'] }}', '{{ $ckPlugin['url'] }}', 'plugin.js');
        @endforeach

        CKEDITOR.replaceAll(function(textarea, config) {
            if (!$(textarea).hasClass("rich-text-editor")) {
                return false;
            }
            var rows = $(textarea).attr('rows') || 10;

            config.height = (rows * 20) + "px";

            var extraPlugins = 'marblelink,marbleai';
            @foreach(app('marble.admin')->getCkEditorPlugins() as $ckPlugin)
            extraPlugins += ',{{ $ckPlugin['name'] }}';
            @endforeach
            config.extraPlugins = extraPlugins;

            config.filebrowserImageUploadUrl = '{{ route('marble.media.ckeditor-upload') }}?_token={{ csrf_token() }}';

            var marbleToolbarItems = ['MarbleAI'];
            @foreach(app('marble.admin')->getCkEditorPlugins() as $ckPlugin)
            @if(!empty($ckPlugin['buttons']))
            @foreach($ckPlugin['buttons'] as $btn)
            marbleToolbarItems.push('{{ $btn }}');
            @endforeach
            @endif
            @endforeach

            config.toolbar = [
                { name: 'clipboard',   items: ['Undo', 'Redo'] },
                { name: 'styles',      items: ['Styles', 'Format'] },
                { name: 'basicstyles', items: ['Bold', 'Italic', 'Strike', '-', 'RemoveFormat'] },
                { name: 'paragraph',   items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote'] },
                { name: 'links',       items: ['Link', 'Unlink', 'MarbleLink'] },
                { name: 'insert',      items: ['Image', 'Table'] },
                { name: 'marble',      items: marbleToolbarItems },
                { name: 'tools',       items: ['Maximize'] },
            ];
        });

        /* ── Marble AI Assistant ──────────────────────────────────────────── */
        window.MarbleAI = (function () {
            var $overlay, $prompt, $result, $resultWrap, $insertBtn, $spinner, $error;
            var activeEditor = null;
            var aiUrl = '{{ route('marble.ai.generate') }}';
            var csrfToken = '{{ csrf_token() }}';

            function build() {
                if ($overlay) return;
                $overlay = $([
                    '<div id="marble-ai-overlay">',
                    '  <div id="marble-ai-modal">',
                    '    <div id="marble-ai-header">',
                    '      <strong>&#9733; AI Assistant</strong>',
                    '      <button id="marble-ai-close">&times;</button>',
                    '    </div>',
                    '    <div id="marble-ai-body">',
                    '      <div id="marble-ai-chips">',
                    '        <button class="marble-ai-chip" data-prompt="Improve the writing and clarity of this text">Improve writing</button>',
                    '        <button class="marble-ai-chip" data-prompt="Make this text shorter and more concise">Make shorter</button>',
                    '        <button class="marble-ai-chip" data-prompt="Expand this text with more detail and examples">Expand</button>',
                    '        <button class="marble-ai-chip" data-prompt="Translate this text to German">→ German</button>',
                    '        <button class="marble-ai-chip" data-prompt="Write a short teaser / summary for this content (2-3 sentences, plain text)">Write teaser</button>',
                    '      </div>',
                    '      <textarea id="marble-ai-prompt" rows="3" placeholder="What would you like to do with this content?"></textarea>',
                    '      <div id="marble-ai-spinner" style="display:none">Generating…</div>',
                    '      <div id="marble-ai-error"  style="display:none"></div>',
                    '      <div id="marble-ai-result-wrap" style="display:none">',
                    '        <label>Result</label>',
                    '        <div id="marble-ai-result"></div>',
                    '      </div>',
                    '    </div>',
                    '    <div id="marble-ai-footer">',
                    '      <button id="marble-ai-generate" class="btn btn-success btn-sm">Generate</button>',
                    '      <button id="marble-ai-insert"   class="btn btn-primary btn-sm" style="display:none">Insert into editor</button>',
                    '      <button id="marble-ai-cancel"   class="btn btn-default btn-sm">Cancel</button>',
                    '    </div>',
                    '  </div>',
                    '</div>',
                ].join(''));

                $('body').append($overlay);
                $prompt     = $('#marble-ai-prompt');
                $result     = $('#marble-ai-result');
                $resultWrap = $('#marble-ai-result-wrap');
                $insertBtn  = $('#marble-ai-insert');
                $spinner    = $('#marble-ai-spinner');
                $error      = $('#marble-ai-error');

                $('#marble-ai-close, #marble-ai-cancel').on('click', close);
                $('#marble-ai-overlay').on('click', function(e) { if (e.target === this) close(); });

                $('#marble-ai-chips').on('click', '.marble-ai-chip', function() {
                    $prompt.val($(this).data('prompt'));
                });

                $('#marble-ai-generate').on('click', generate);
                $prompt.on('keydown', function(e) {
                    if (e.ctrlKey && e.key === 'Enter') generate();
                });

                $insertBtn.on('click', function() {
                    if (activeEditor && $result.html()) {
                        activeEditor.insertHtml($result.html());
                    }
                    close();
                });
            }

            function generate() {
                var prompt = $prompt.val().trim();
                if (!prompt) return;

                var context = '';
                if (activeEditor) {
                    context = activeEditor.getSelection().getSelectedText() || activeEditor.getData();
                }

                $spinner.show();
                $error.hide().text('');
                $resultWrap.hide();
                $insertBtn.hide();
                $('#marble-ai-generate').prop('disabled', true);

                $.ajax({
                    url:    aiUrl,
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    data:   { prompt: prompt, context: context },
                    success: function(data) {
                        $result.html(data.result);
                        $resultWrap.show();
                        $insertBtn.show();
                    },
                    error: function(xhr) {
                        var msg = (xhr.responseJSON && xhr.responseJSON.error) ? xhr.responseJSON.error : 'An error occurred.';
                        $error.text(msg).show();
                    },
                    complete: function() {
                        $spinner.hide();
                        $('#marble-ai-generate').prop('disabled', false);
                    }
                });
            }

            function open(editor) {
                build();
                activeEditor = editor;
                $prompt.val('');
                $result.html('');
                $resultWrap.hide();
                $insertBtn.hide();
                $error.hide().text('');
                $spinner.hide();
                $overlay.show();
                $prompt.focus();
            }

            function close() {
                if ($overlay) $overlay.hide();
                activeEditor = null;
            }

            return { open: open };
        })();
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
    @stack('modals')
</body>
</html>
