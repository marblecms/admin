@extends('marble::layouts.app')

@section('javascript-head')
    <script type="text/javascript" src="{{ asset('vendor/marble/assets/js/attributes/attributes.js') }}"></script>
    <style>
        #marble-autosave-toast {
            display: none;
            position: fixed;
            top: 70px;
            right: 24px;
            padding: 10px 18px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 600;
            z-index: 99999;
            box-shadow: 0 4px 12px rgba(0,0,0,.2);
            color: #fff;
        }
        #marble-autosave-toast.toast-success { background: #27ae60; }
        #marble-autosave-toast.toast-error   { background: #c0392b; }
        #marble-autosave-toast.toast-saving  { background: #7f8c8d; }

        .marble-spinner {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 2px solid rgba(255,255,255,.4);
            border-top-color: #fff;
            border-radius: 50%;
            animation: marble-spin .6s linear infinite;
            vertical-align: middle;
        }
        @keyframes marble-spin { to { transform: rotate(360deg); } }
    </style>
@endsection

@section('javascript')
    <script type="text/javascript" src="{{ asset('vendor/marble/assets/js/language-switch.js') }}"></script>
    <script type="text/javascript" src="{{ asset('vendor/marble/assets/js/attributes/attributes-init.js') }}"></script>
    <script>
        // Unsaved changes warning
        var formDirty = false;
        $(function() {
            $('#marble-edit-form :input').on('change input', function() { formDirty = true; });
            window.onbeforeunload = function() {
                if (formDirty) return 'Du hast ungespeicherte Änderungen.';
            };
            $('#marble-edit-form').on('submit', function() {
                formDirty = false;
                marbleReleaseLock();
            });
        });

        // Content locking — acquire on load, refresh every 2 min, release on leave
        var lockUrl     = '{{ route('marble.item.lock', $item) }}';
        var unlockUrl   = '{{ route('marble.item.unlock', $item) }}';

        function marbleAcquireLock() {
            $.post(lockUrl);
        }
        function marbleReleaseLock() {
            navigator.sendBeacon(unlockUrl + '?_method=DELETE&_token={{ csrf_token() }}');
        }

        $(function() {
            marbleAcquireLock();
            setInterval(marbleAcquireLock, 120000); // refresh every 2 min
            $(window).on('beforeunload', function() { marbleReleaseLock(); });
        });

        // Slug auto-generation from name field
        $(function() {
            var $nameInput = $('[data-field-identifier="name"] input[type=text], [data-field-identifier="name"] textarea').first();
            var $slugInput = $('[data-field-identifier="slug"] input[type=text]').first();

            if ($nameInput.length && $slugInput.length) {
                $nameInput.on('input', function() {
                    if ($slugInput.val() !== '') return;
                    var slug = $(this).val()
                        .toLowerCase().trim()
                        .replace(/[äöüß]/g, function(c) { return {ä:'ae',ö:'oe',ü:'ue',ß:'ss'}[c]; })
                        .replace(/[^a-z0-9\s-]/g, '')
                        .replace(/\s+/g, '-')
                        .replace(/-+/g, '-');
                    $slugInput.val(slug);
                });
            }
        });

        // URL Alias — add new row
        $(function() {
            var aliasIndex = {{ $aliases->count() }};
            var languages = @json($languages->map(fn($l) => ['id' => $l->id, 'code' => strtoupper($l->code)]));

            $('#add-alias-btn').on('click', function() {
                var opts = languages.map(function(l) {
                    return '<option value="' + l.id + '">' + l.code + '</option>';
                }).join('');
                var row = '<div class="alias-row" style="display:flex;gap:6px;margin-bottom:6px;align-items:center">'
                    + '<input type="hidden" name="aliases[' + aliasIndex + '][id]" value="" />'
                    + '<input type="text" name="aliases[' + aliasIndex + '][alias]" placeholder="/kampagne" class="form-control input-sm" style="flex:1" />'
                    + '<select name="aliases[' + aliasIndex + '][language_id]" class="form-control input-sm" style="width:60px">' + opts + '</select>'
                    + '<a href="javascript:;" onclick="this.closest(\'.alias-row\').remove()" style="color:#c0392b;font-size:16px;line-height:1">&times;</a>'
                    + '</div>';
                $('#aliases-list').append(row);
                aliasIndex++;
            });
        });

        @if(config('marble.autosave', false))
        // Autosave
        var autosaveDelay = {{ config('marble.autosave_interval', 30) * 1000 }};
        var autosaveTimer = null;

        function marbleShowToast(msg, type) {
            var $toast = $('#marble-autosave-toast');
            $toast.removeClass('toast-success toast-error').addClass(type === 'error' ? 'toast-error' : 'toast-success');
            $toast.text(msg).stop(true).fadeIn(200);
            if (type !== 'error') {
                setTimeout(function() { $toast.fadeOut(400); }, 2000);
            }
        }

        function marbleAutosave() {
            var $form = $('#marble-edit-form');
            marbleShowToast('Saving…', 'saving');
            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: $form.serialize(),
                success: function() {
                    formDirty = false;
                    marbleShowToast('Autosaved ✓', 'success');
                },
                error: function() {
                    marbleShowToast('Autosave failed', 'error');
                }
            });
        }

        $(function() {
            $('#marble-edit-form :input').on('change input', function() {
                clearTimeout(autosaveTimer);
                autosaveTimer = setTimeout(marbleAutosave, autosaveDelay);
            });
        });

        // Also trigger autosave on CKEditor content changes
        if (typeof CKEDITOR !== 'undefined') {
            CKEDITOR.on('instanceCreated', function(e) {
                e.editor.on('change', function() {
                    clearTimeout(autosaveTimer);
                    autosaveTimer = setTimeout(marbleAutosave, autosaveDelay);
                });
            });
        }
        @endif

        // Save button spinner
        $(function() {
            $('form').on('submit', function() {
                var $btn = $(this).find('.marble-save-btn');
                if ($btn.length) {
                    $btn.prop('disabled', true).html('<span class="marble-spinner"></span> {{ trans('marble::admin.saving') }}');
                }
            });
        });
    </script>
@endsection

@section('sidebar')

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom:10px">{{ session('success') }}</div>
    @endif
    @if($errors->has('delete'))
        <div class="alert alert-danger" style="margin-bottom:10px">{{ $errors->first('delete') }}</div>
    @endif
    @if($errors->has('aliases'))
        <div class="alert alert-danger" style="margin-bottom:10px">{{ $errors->first('aliases') }}</div>
    @endif

    {{-- Lock warning --}}
    @if($lockedByOther)
        <div class="alert alert-warning" style="margin-bottom:10px">
            @include('marble::components.famicon', ['name' => 'lock'])
            <strong>{{ $lockUser->name }}</strong> {{ trans('marble::admin.lock_editing') }}
        </div>
    @endif

    {{-- Status + Header --}}
    <div class="main-box clearfix profile-box-menu">
        <div class="main-box-body clearfix">
            @php $slug = $item->rawValue('slug'); @endphp
            <div class="profile-box-header {{ $item->status === 'published' ? 'green-bg' : 'gray-bg' }} clearfix" style="display:flex; align-items:center;">
                <div>
                    <h2>{{ $item->name() }}</h2>
                    <div class="job-position">{{ $item->blueprint->name }}</div>
                </div>
                @if($slug)
                    @php $frontendUrl = config('marble.frontend_url', ''); @endphp
                    <a href="{{ $frontendUrl . $item->slug() }}" target="_blank" class="btn btn-xs btn-default" style="margin-left:auto">
                        @include('marble::components.famicon', ['name' => 'monitor']) {{ trans('marble::admin.preview') }}
                    </a>
                @endif
            </div>
            <div class="profile-box-content clearfix">
                <ul class="menu-items">

                    {{-- Toggle status --}}
                    <li class="menu-item-action">
                        <span>
                            @if($item->status === 'published')
                                @include('marble::components.famicon', ['name' => 'tick'])
                                {{ trans('marble::admin.published') }}
                            @else
                                @include('marble::components.famicon', ['name' => 'pencil'])
                                <span style="color:#999;font-weight:bold">{{ trans('marble::admin.draft') }}</span>
                            @endif
                        </span>
                        <form method="POST" action="{{ route('marble.item.toggle-status', $item) }}" style="display:inline">
                            @csrf
                            <button type="submit" class="btn btn-xs btn-info">
                                {{ $item->status === 'published' ? trans('marble::admin.set_draft') : trans('marble::admin.set_published') }}
                            </button>
                        </form>
                    </li>

                    {{-- Show in Nav toggle --}}
                    @if($item->blueprint->show_in_tree)
                        <li class="menu-item-action">
                            <span>
                                @include('marble::components.famicon', ['name' => $item->show_in_nav ? 'application_side_tree' : 'application_side_tree'])
                                @if($item->show_in_nav)
                                    {{ trans('marble::admin.show_in_navigation') }}
                                @else
                                    <span style="color:#999;font-weight:bold">{{ trans('marble::admin.hidden_in_navigation') }}</span>
                                @endif
                            </span>
                            <form method="POST" action="{{ route('marble.item.toggle-nav', $item) }}" style="display:inline">
                                @csrf
                                <button type="submit" class="btn btn-xs btn-info">
                                    {{ $item->show_in_nav ? trans('marble::admin.hide_in_nav') : trans('marble::admin.show_in_nav') }}
                                </button>
                            </form>
                        </li>
                    @endif

                    {{-- Add child --}}
                    @if($item->blueprint->allow_children)
                        <li>
                            <a href="{{ route('marble.item.add', $item) }}" class="clearfix">
                                @include('marble::components.famicon', ['name' => 'add']) {{ trans('marble::admin.add_children') }}
                            </a>
                        </li>
                    @endif

                    {{-- Duplicate --}}
                    <li>
                        <form method="POST" action="{{ route('marble.item.duplicate', $item) }}">
                            @csrf
                            <button type="submit">
                                @include('marble::components.famicon', ['name' => 'page_white_copy']) {{ trans('marble::admin.duplicate') }}
                            </button>
                        </form>
                    </li>

                    {{-- Move --}}
                    @if($item->parent_id)
                        <li>
                            <a href="{{ route('marble.item.move-form', $item) }}" class="clearfix">
                                @include('marble::components.famicon', ['name' => 'application_side_expand']) {{ trans('marble::admin.move') }}
                            </a>
                        </li>
                    @endif

                    {{-- Export --}}
                    <li>
                        <a href="{{ route('marble.item.export', $item) }}" class="clearfix">
                            @include('marble::components.famicon', ['name' => 'page_white_paste']) {{ trans('marble::admin.export') }}
                        </a>
                    </li>

                    {{-- Delete --}}
                    @if($item->parent_id)
                        <li>
                            <form method="POST" action="{{ route('marble.item.delete', $item) }}" onsubmit="return confirm('{{ trans('marble::admin.are_you_sure') }}'); formDirty=false;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="danger">
                                    @include('marble::components.famicon', ['name' => 'bin']) {{ trans('marble::admin.delete_node') }}
                                </button>
                            </form>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
    
    {{-- Workflow Timeline --}}
    @include('marble::item.partials.workflow-timeline')

    {{-- URL Aliases --}}
    <div class="main-box clearfix profile-box-menu">
        <div class="main-box-body clearfix">
            <div class="profile-box-header gray-bg clearfix" style="padding:0 15px 15px">
                <h2>{{ trans('marble::admin.url_aliases') }}</h2>
            </div>
            <div class="profile-box-content clearfix" style="padding:12px 15px">
                <form method="POST" action="{{ route('marble.item.aliases.save', $item) }}" id="aliases-form">
                    @csrf
                    <div id="aliases-list">
                        @foreach($aliases as $alias)
                        <div class="alias-row" style="display:flex;gap:6px;margin-bottom:6px;align-items:center">
                            <input type="hidden" name="aliases[{{ $loop->index }}][id]" value="{{ $alias->id }}" />
                            <input type="text"
                                   name="aliases[{{ $loop->index }}][alias]"
                                   value="{{ $alias->alias }}"
                                   placeholder="/kampagne"
                                   class="form-control input-sm"
                                   style="flex:1" />
                            <select name="aliases[{{ $loop->index }}][language_id]" class="form-control input-sm" style="width:60px">
                                @foreach($languages as $lang)
                                    <option value="{{ $lang->id }}" {{ $alias->language_id == $lang->id ? 'selected' : '' }}>{{ strtoupper($lang->code) }}</option>
                                @endforeach
                            </select>
                            <a href="javascript:;" onclick="this.closest('.alias-row').remove()" style="color:#c0392b;font-size:16px;line-height:1">&times;</a>
                        </div>
                        @endforeach
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:4px">
                        <button type="button" id="add-alias-btn" class="btn btn-xs btn-info">
                            {{ trans('marble::admin.add_alias') }}
                        </button>
                        <button type="submit" class="btn btn-xs btn-success">
                            @include('marble::components.famicon', ['name' => 'disk']) {{ trans('marble::admin.save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Draft Preview --}}
    @if($item->status !== 'published')
    <div class="main-box clearfix profile-box-menu">
        <div class="main-box-body clearfix">
            <div class="profile-box-header gray-bg clearfix" style="padding:0 15px 15px">
                <h2>{{ trans('marble::admin.draft_preview') }}</h2>
            </div>
            <div class="profile-box-content clearfix" style="padding:10px 15px">
                @if(session('preview_url'))
                    <div style="margin-bottom:8px">
                        <a href="{{ session('preview_url') }}" target="_blank" class="btn btn-xs btn-success btn-block">
                            @include('marble::components.famicon', ['name' => 'monitor']) {{ trans('marble::admin.open_preview') }}
                        </a>
                        <small class="text-muted" style="display:block;margin-top:4px;word-break:break-all">{{ session('preview_url') }}</small>
                    </div>
                @elseif($item->preview_token)
                    @php $frontendUrl = rtrim(config('marble.frontend_url', ''), '/'); @endphp
                    <div style="margin-bottom:8px">
                        <a href="{{ $frontendUrl }}/marble-preview/{{ $item->preview_token }}" target="_blank" class="btn btn-xs btn-success btn-block">
                            @include('marble::components.famicon', ['name' => 'monitor']) {{ trans('marble::admin.open_preview') }}
                        </a>
                    </div>
                @endif
                <form method="POST" action="{{ route('marble.item.preview.generate', $item) }}">
                    @csrf
                    <button type="submit" class="btn btn-xs btn-default btn-block">
                        @include('marble::components.famicon', ['name' => 'key']) {{ $item->preview_token ? trans('marble::admin.refresh_preview_token') : trans('marble::admin.generate_preview') }}
                    </button>
                </form>
                <small class="text-muted" style="display:block;margin-top:6px">{{ trans('marble::admin.draft_preview_hint') }}</small>
            </div>
        </div>
    </div>
    @endif

    {{-- Copy Language --}}
    @if(count($languages) > 1)
    <div class="main-box clearfix profile-box-menu">
        <div class="main-box-body clearfix">
            <div class="profile-box-header gray-bg clearfix" style="padding:0 15px 15px">
                <h2>{{ trans('marble::admin.copy_language') }}</h2>
            </div>
            <div class="profile-box-content clearfix" style="padding:10px 15px">
                <form method="POST" action="{{ route('marble.item.copy-language', $item) }}">
                    @csrf
                    <div style="display:flex;gap:6px;align-items:center;margin-bottom:8px">
                        <select name="from_language_id" class="form-control input-sm" style="flex:1">
                            @foreach($languages as $lang)
                                <option value="{{ $lang->id }}">{{ strtoupper($lang->code) }}</option>
                            @endforeach
                        </select>
                        <span style="color:#aaa">→</span>
                        <select name="to_language_id" class="form-control input-sm" style="flex:1">
                            @foreach($languages as $lang)
                                <option value="{{ $lang->id }}" {{ $loop->index === 1 ? 'selected' : '' }}>{{ strtoupper($lang->code) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-xs btn-default btn-block"
                            onclick="return confirm('{{ trans('marble::admin.copy_language_confirm') }}')">
                        @include('marble::components.famicon', ['name' => 'page_copy']) {{ trans('marble::admin.copy_language') }}
                    </button>
                </form>
                <small class="text-muted" style="display:block;margin-top:6px">{{ trans('marble::admin.copy_language_hint') }}</small>
            </div>
        </div>
    </div>
    @endif

    {{-- Scheduling --}}
    @if($item->blueprint->schedulable)
    <div class="main-box clearfix profile-box-menu">
        <div class="main-box-body clearfix">
            <div class="profile-box-header gray-bg clearfix" style="padding:0 15px 15px">
                <h2>{{ trans('marble::admin.scheduling') }}</h2>
            </div>
            <div class="profile-box-content clearfix" style="padding:10px 15px">
                <form method="POST" action="{{ route('marble.item.save', $item) }}" id="scheduling-form">
                    @csrf
                    <div class="form-group" style="margin-bottom:8px">
                        <label style="color:#777;display:block">{{ trans('marble::admin.publish_at') }}</label>
                        <input type="datetime-local" name="published_at" class="form-control input-sm"
                            value="{{ $item->published_at ? $item->published_at->format('Y-m-d\TH:i') : '' }}" />
                    </div>
                    <div class="form-group" style="margin-bottom:8px">
                        <label style="color:#777;display:block">{{ trans('marble::admin.expires_at') }}</label>
                        <input type="datetime-local" name="expires_at" class="form-control input-sm"
                            value="{{ $item->expires_at ? $item->expires_at->format('Y-m-d\TH:i') : '' }}" />
                    </div>
                    <div style="text-align:right">
                        <button type="submit" class="btn btn-xs btn-info">{{ trans('marble::admin.save_schedule') }}</button>
                    </div>
                </form>
                @if($item->published_at || $item->expires_at)
                    <div style="margin-top:6px;color:#999">
                        @if($item->published_at)
                            <div>{{ trans('marble::admin.publish_at') }}: {{ $item->published_at->format('d.m.Y H:i') }}</div>
                        @endif
                        @if($item->expires_at)
                            <div>{{ trans('marble::admin.expires_at') }}: {{ $item->expires_at->format('d.m.Y H:i') }}</div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    @endif

    {{-- Meta --}}
    <div class="main-box clearfix profile-box-menu">
        <div class="main-box-body clearfix">
            <div class="profile-box-header gray-bg clearfix" style="padding:0 15px 15px">
                <h2>{{ trans('marble::admin.meta_information') }}</h2>
            </div>
            <div class="profile-box-content clearfix">
                <ul class="menu-items">
                    <li><a href="#"><b>ID:</b> {{ $item->id }}</a></li>
                    <li><a href="#"><b>Blueprint:</b> {{ $item->blueprint->name }}</a></li>
                    <li><a href="#"><b>Parent ID:</b> {{ $item->parent_id }}</a></li>
                    @if($slug)
                        <li><a href="#"><b>Slug:</b> {{ $slug }}</a></li>
                    @endif
                </ul>
            </div>
        </div>
    </div>


    {{-- Reachable via --}}
    @php
        $frontendUrl = rtrim(config('marble.frontend_url', ''), '/');
        $reachableRoutes = [];

        foreach ($languages as $lang) {
            $slug = $item->slug($lang->id);
            if ($slug) $reachableRoutes[] = ['type' => 'slug', 'lang' => strtoupper($lang->code), 'path' => $slug];
        }

        // Mount-point paths
        foreach ($mountPoints as $mount) {
            foreach ($languages as $lang) {
                $mountSlug = $item->slug($lang->id, $mount->mount_parent_id);
                if ($mountSlug) {
                    $reachableRoutes[] = ['type' => 'mount', 'lang' => strtoupper($lang->code), 'path' => $mountSlug];
                }
            }
        }

        foreach ($aliases as $alias) {
            $reachableRoutes[] = ['type' => 'alias', 'lang' => strtoupper($alias->language->code ?? ''), 'path' => '/' . ltrim($alias->alias, '/')];
        }

        foreach ($inboundRedirects as $redirect) {
            $reachableRoutes[] = ['type' => 'redirect', 'lang' => null, 'path' => '/' . ltrim($redirect->source_path, '/'), 'code' => $redirect->status_code];
        }
    @endphp
    @if(count($reachableRoutes))
    <div class="main-box clearfix profile-box-menu">
        <div class="main-box-body clearfix">
            <div class="profile-box-header gray-bg clearfix" style="padding:0 15px 15px">
                <h2>{{ trans('marble::admin.reachable_via') }}</h2>
            </div>
            <div class="profile-box-content clearfix" style="padding:8px 15px">
                @foreach($reachableRoutes as $route)
                <div style="display:flex;align-items:baseline;gap:6px;padding:4px 0;border-bottom:1px dotted #eee;font-size:12px">
                    @if($route['type'] === 'slug')
                        <span style="color:#999;min-width:42px">{{ trans('marble::admin.slug') }} {{ $route['lang'] }}</span>
                    @elseif($route['type'] === 'mount')
                        <span style="color:#777;min-width:42px">{{ trans('marble::admin.mount') }} {{ $route['lang'] }}</span>
                    @elseif($route['type'] === 'alias')
                        <span style="color:#999;min-width:42px">{{ trans('marble::admin.alias') }} {{ $route['lang'] }}</span>
                    @else
                        <span style="color:#999;min-width:42px">{{ trans('marble::admin.redirect') }} {{ $route['code'] }}</span>
                    @endif
                    <a href="{{ $frontendUrl . $route['path'] }}" target="_blank" style="word-break:break-all">{{ $route['path'] }}</a>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Mount Points --}}
    @if($item->blueprint->allow_children || $mountPoints->isNotEmpty())
    <div class="main-box clearfix profile-box-menu">
        <div class="main-box-body clearfix">
            <div class="profile-box-header gray-bg clearfix" style="padding:0 15px 15px">
                <h2>{{ trans('marble::admin.mount_points') }}</h2>
            </div>
            <div class="profile-box-content clearfix" style="padding:8px 15px">
                @if($mountPoints->isEmpty())
                    <p class="text-muted" style="font-size:12px;margin:0 0 10px">{{ trans('marble::admin.mount_points_hint') }}</p>
                @else
                    @foreach($mountPoints as $mount)
                        <div style="display:flex;align-items:center;gap:6px;padding:4px 0;border-bottom:1px dotted #eee;font-size:12px">
                            <span style="flex:1">
                                🔗
                                @if($mount->mountParent)
                                    <a href="{{ route('marble.item.edit', $mount->mountParent) }}">{{ $mount->mountParent->name() }}</a>
                                @else
                                    <span class="text-muted">–</span>
                                @endif
                            </span>
                            <form method="POST" action="{{ route('marble.item.mount.destroy', [$item, $mount]) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-xs btn-danger" title="{{ trans('marble::admin.mount_remove') }}">✕</button>
                            </form>
                        </div>
                    @endforeach
                @endif

                {{-- Add mount point via object browser --}}
                <div style="margin-top:10px">
                    <input type="hidden" id="new-mount-parent-id" value="" />
                    <input type="text" id="new-mount-parent-name" class="form-control input-sm"
                           placeholder="{{ trans('marble::admin.mount_select_parent') }}"
                           readonly style="cursor:pointer;background:#fff"
                           onclick="ObjectBrowser.open(function(node){ document.getElementById('new-mount-parent-id').value=node.id; document.getElementById('new-mount-parent-name').value=node.name; })" />
                    <form method="POST" action="{{ route('marble.item.mount.store', $item) }}" id="add-mount-form" style="margin-top:6px">
                        @csrf
                        <input type="hidden" name="mount_parent_id" id="add-mount-parent-hidden" value="" />
                        <button type="button" class="btn btn-xs btn-default" onclick="
                            var id = document.getElementById('new-mount-parent-id').value;
                            if (!id) return;
                            document.getElementById('add-mount-parent-hidden').value = id;
                            document.getElementById('add-mount-form').submit();
                        ">
                            @include('marble::components.famicon', ['name' => 'add']) {{ trans('marble::admin.mount_add') }}
                        </button>
                    </form>
                    @error('mount') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Used by (Reverse Relations) --}}
    @if($usedBy->isNotEmpty())
        <div class="main-box clearfix profile-box-menu">
            <div class="main-box-body clearfix">
                <div class="profile-box-header gray-bg clearfix" style="padding:0 15px 15px">
                    <h2>{{ trans('marble::admin.used_by') }} ({{ $usedBy->count() }})</h2>
                </div>
                <div class="profile-box-content clearfix">
                    <ul class="menu-items">
                        @foreach($usedBy as $ref)
                            <li>
                                <a href="{{ route('marble.item.edit', $ref) }}" class="clearfix">
                                    @if($ref->blueprint && $ref->blueprint->icon)
                                        @include('marble::components.famicon', ['name' => $ref->blueprint->icon])
                                    @endif
                                    {{ $ref->name() }}
                                    <small style="color:#999">{{ $ref->blueprint->name ?? '' }}</small>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- Revisions --}}
    @if($item->blueprint->versionable)
    <div class="main-box clearfix profile-box-menu">
        <div class="main-box-body clearfix">
            <div class="profile-box-header gray-bg clearfix" style="padding:0 15px 15px">
                <h2>{{ trans('marble::admin.versions') }} @if($revisions->count()) ({{ $revisions->count() }}) @endif</h2>
            </div>
            <div class="profile-box-content clearfix">
                @if($revisions->isEmpty())
                    <ul class="menu-items">
                        <li><a href="#" style="color:#999">{{ trans('marble::admin.no_versions') }}</a></li>
                    </ul>
                @else
                    <ul class="menu-items">
                        @foreach($revisions as $revision)
                            <form id="revert-{{ $revision->id }}"
                                  method="POST"
                                  action="{{ route('marble.item.revert', [$item, $revision]) }}"
                                  style="display:none">
                                @csrf
                            </form>
                            <li class="menu-item-action">
                                <span>
                                    {{ $revision->created_at->format('d.m. H:i') }}
                                    @if($revision->user)
                                        <small style="color:#999;margin-left:4px">{{ $revision->user->name }}</small>
                                    @endif
                                </span>
                                <div class="btn-group btn-group-xs">
                                    <a href="{{ route('marble.item.diff', [$item, $revision]) }}" class="btn btn-xs btn-default">{{ trans('marble::admin.diff') }}</a>
                                    <button type="submit" form="revert-{{ $revision->id }}" class="btn btn-xs btn-info"
                                            onclick="formDirty=false;">{{ trans('marble::admin.restore') }}</button>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>

    @endif

@endsection

@section('content')
    <h1>
        {{ $item->name() }}
        @if(config('marble.autosave', false))
            @if(config('marble.autosave', false))
            <small id="autosave-indicator" style="display:none; color:#999; font-weight:normal; margin-left:10px"></small>
            @endif
        @endif
    </h1>

    @if($breadcrumb->count() > 1)
        <div style="margin:-6px 0 12px;font-size:12px;color:#888">
            @foreach($breadcrumb as $crumb)
                @if(!$loop->last)
                    <a href="{{ route('marble.item.edit', $crumb) }}" style="color:#5580B0">{{ $crumb->name() ?: '—' }}</a>
                    <span style="margin:0 4px;color:#bbb">›</span>
                @else
                    <span style="color:#555">{{ $crumb->name() ?: '—' }}</span>
                @endif
            @endforeach
        </div>
    @endif

    @if($item->blueprint->is_form)
        {{-- Name & Slug — needed for tree placement and frontend URL --}}
        <form id="marble-edit-form" action="{{ route('marble.item.save', $item) }}" enctype="multipart/form-data" method="post">
            @csrf
            <div class="main-box">
                <div class="main-box-body clearfix">
                    @foreach($groupedFields as $group)
                        @foreach($group['fields'] as $field)
                            @if(in_array($field->identifier, ['name', 'slug']))
                                @include('marble::item.edit_field', ['field' => $field, 'item' => $item, 'languages' => $languages])
                            @endif
                        @endforeach
                    @endforeach
                </div>
            </div>
            <div class="form-group pull-right" style="margin-bottom:16px">
                <button type="submit" class="btn btn-success marble-save-btn">@include('marble::components.famicon', ['name' => 'disk']) {{ trans('marble::admin.save') }}</button>
            </div>
            <div class="clearfix"></div>
        </form>

        @include('marble::form.submissions-table', ['item' => $item, 'submissions' => $submissions])
    @elseif(!$item->blueprint->locked)
    <form id="marble-edit-form" action="{{ route('marble.item.save', $item) }}" enctype="multipart/form-data" method="post">
        @csrf

        @foreach($groupedFields as $group)
            <div class="main-box">
                @if($group['group'])
                    <header class="main-box-header clearfix">
                        <h2><b>{{ $group['group']->name }}</b></h2>
                    </header>
                @else
                    <br />
                @endif

                <div class="main-box-body clearfix">
                    @foreach($group['fields'] as $field)
                        @continue($field->locked)
                        @include('marble::item.edit_field', ['field' => $field, 'item' => $item, 'languages' => $languages])
                    @endforeach
                </div>
            </div>
        @endforeach

        <div class="form-group pull-right">
            <button type="submit" class="btn btn-success">@include('marble::components.famicon', ['name' => 'disk']) {{ trans('marble::admin.save') }}</button>
        </div>
        <div class="clearfix"></div>
        <br /><br /><br />
    </form>
    @endif {{-- !is_form && !locked --}}

    @if($item->blueprint->list_children && $childItems)
        @include('marble::item.children', ['item' => $item, 'childItems' => $childItems])
    @endif

    @if(config('marble.autosave', false))
        <div id="marble-autosave-toast" class="toast-success"></div>
    @endif
@endsection
