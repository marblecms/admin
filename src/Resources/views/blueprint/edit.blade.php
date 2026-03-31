@extends('marble::layouts.app')

@php $prefix = config('marble.route_prefix', 'admin'); @endphp

@section('sidebar')
    <div class="main-box clearfix profile-box-menu">
        <div class="main-box-body clearfix">
            <div class="profile-box-header gray-bg clearfix">
                <h2>Structure</h2>
            </div>
            <div class="profile-box-content clearfix">
                <ul class="menu-items">
                    <li>
                        <a href="{{ route('marble.blueprint.field.edit', $blueprint) }}">
                            @include('marble::components.famicon', ['name' => 'application_form'])
                            Edit Attributes
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <h1>{{ $blueprint->name }}</h1>

    <form action="{{ url("{$prefix}/blueprint/save/{$blueprint->id}") }}" method="post">
        @csrf

        {{-- General --}}
        <div class="main-box">
            <header class="main-box-header clearfix">
                <h2>General</h2>
            </header>
            <div class="main-box-body clearfix">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" name="name" value="{{ $blueprint->name }}" class="form-control" required />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Identifier</label>
                            <input type="text" name="identifier" value="{{ $blueprint->identifier }}" class="form-control" required />
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Group</label>
                            <select name="blueprint_group_id" class="form-control">
                                @foreach($blueprintGroups as $group)
                                    <option value="{{ $group->id }}" {{ $group->id == $blueprint->blueprint_group_id ? 'selected' : '' }}>{{ $group->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Icon</label>
                            <div style="display:flex; align-items:center; gap:10px;">
                                <div style="position:relative; flex:1">
                                    <input type="text" id="icon-search" autocomplete="off" class="form-control"
                                           placeholder="Search icons…"
                                           value="{{ $blueprint->icon ?: '' }}" />
                                    <input type="hidden" name="icon" id="icon-value" value="{{ $blueprint->icon ?: '' }}" />
                                    <ul id="icon-suggestions" style="display:none; position:absolute; z-index:9999; background:#fff; border:1px solid #c0c0c0; border-radius:3px; margin:0; padding:0; list-style:none; width:100%; max-height:220px; overflow-y:auto; box-shadow:0 3px 8px rgba(0,0,0,.15)"></ul>
                                </div>
                                <img id="icon-preview"
                                     src="{{ asset('vendor/marble/assets/images/famicons/' . ($blueprint->icon ?: 'page') . '.svg') }}"
                                     width="28" height="28" alt=""
                                     style="flex-shrink:0; opacity:{{ $blueprint->icon ? '1' : '0.3' }}">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>{{ trans('marble::admin.inherits_from') }}</label>
                    <select name="parent_blueprint_id" class="form-control">
                        <option value="">— {{ trans('marble::admin.none') }} —</option>
                        @foreach($allBlueprints->flatten(1) as $bp)
                            @if($bp->id !== $blueprint->id)
                                <option value="{{ $bp->id }}" {{ $blueprint->parent_blueprint_id == $bp->id ? 'selected' : '' }}>{{ $bp->name }}</option>
                            @endif
                        @endforeach
                    </select>
                    <small class="text-muted">{{ trans('marble::admin.inherits_from_hint') }}</small>
                </div>
            </div>
        </div>

        {{-- Behaviour --}}
        <div class="main-box">
            <header class="main-box-header clearfix">
                <h2>Behaviour</h2>
            </header>
            <div class="main-box-body clearfix">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="checkbox-inline" style="display:flex;align-items:center;gap:8px;font-weight:normal">
                                <input type="hidden" name="allow_children" value="0">
                                <input type="checkbox" name="allow_children" value="1" {{ $blueprint->allow_children ? 'checked' : '' }}>
                                Allow Children
                            </label>
                            <small class="text-muted" style="display:block;margin-top:4px">Items of this type can have child items.</small>
                        </div>
                        <div class="form-group">
                            <label class="checkbox-inline" style="display:flex;align-items:center;gap:8px;font-weight:normal">
                                <input type="hidden" name="list_children" value="0">
                                <input type="checkbox" name="list_children" value="1" {{ $blueprint->list_children ? 'checked' : '' }}>
                                List Children
                            </label>
                            <small class="text-muted" style="display:block;margin-top:4px">Show child items in the admin sidebar.</small>
                        </div>
                        <div class="form-group">
                            <label class="checkbox-inline" style="display:flex;align-items:center;gap:8px;font-weight:normal">
                                <input type="hidden" name="show_in_tree" value="0">
                                <input type="checkbox" name="show_in_tree" value="1" {{ $blueprint->show_in_tree ? 'checked' : '' }}>
                                Show in Tree
                            </label>
                            <small class="text-muted" style="display:block;margin-top:4px">Visible in the navigation tree.</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="checkbox-inline" style="display:flex;align-items:center;gap:8px;font-weight:normal">
                                <input type="hidden" name="locked" value="0">
                                <input type="checkbox" name="locked" value="1" {{ $blueprint->locked ? 'checked' : '' }}>
                                Locked
                            </label>
                            <small class="text-muted" style="display:block;margin-top:4px">Fields cannot be edited in the admin.</small>
                        </div>
                        <div class="form-group">
                            <label class="checkbox-inline" style="display:flex;align-items:center;gap:8px;font-weight:normal">
                                <input type="hidden" name="api_public" value="0">
                                <input type="checkbox" name="api_public" value="1" {{ $blueprint->api_public ? 'checked' : '' }}>
                                Public API
                            </label>
                            <small class="text-muted" style="display:block;margin-top:4px">Expose this blueprint via the public JSON API without authentication.</small>
                        </div>
                        <div class="form-group">
                            <label class="checkbox-inline" style="display:flex;align-items:center;gap:8px;font-weight:normal">
                                <input type="hidden" name="versionable" value="0">
                                <input type="checkbox" name="versionable" value="1" {{ ($blueprint->versionable ?? true) ? 'checked' : '' }}>
                                {{ trans('marble::admin.versionable') }}
                            </label>
                            <small class="text-muted" style="display:block;margin-top:4px">{{ trans('marble::admin.versionable_hint') }}</small>
                        </div>
                        <div class="form-group">
                            <label class="checkbox-inline" style="display:flex;align-items:center;gap:8px;font-weight:normal">
                                <input type="hidden" name="schedulable" value="0">
                                <input type="checkbox" name="schedulable" value="1" {{ $blueprint->schedulable ? 'checked' : '' }}>
                                {{ trans('marble::admin.schedulable') }}
                            </label>
                            <small class="text-muted" style="display:block;margin-top:4px">{{ trans('marble::admin.schedulable_hint') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Workflow --}}
        <div class="main-box">
            <header class="main-box-header clearfix">
                <h2>{{ trans('marble::admin.workflow') }}</h2>
            </header>
            <div class="main-box-body clearfix">
                <div class="form-group">
                    <label>{{ trans('marble::admin.workflow') }}</label>
                    <select name="workflow_id" class="form-control">
                        <option value="">— {{ trans('marble::admin.none') }} —</option>
                        @foreach($workflows as $wf)
                            <option value="{{ $wf->id }}" {{ $blueprint->workflow_id == $wf->id ? 'selected' : '' }}>{{ $wf->name }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">{{ trans('marble::admin.workflow_hint') }}</small>
                </div>
            </div>
        </div>

        {{-- Allowed Children --}}
        <div class="main-box">
            <header class="main-box-header clearfix">
                <h2>Allowed Child Blueprints</h2>
            </header>
            <div class="main-box-body clearfix">
                <select multiple name="allowed_child_blueprints[]" class="form-control" size="10">
                    <option value="all" {{ $blueprint->allowsAllChildren() ? 'selected' : '' }}>— All —</option>
                    @foreach($allBlueprints as $groupName => $bps)
                        <optgroup label="{{ $groupName }}">
                            @foreach($bps as $bp)
                                @if($bp->id !== $blueprint->id)
                                    <option value="{{ $bp->id }}" {{ $blueprint->allowedChildBlueprints->contains($bp->id) ? 'selected' : '' }}>{{ $bp->name }}</option>
                                @endif
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                <small class="text-muted">Hold Ctrl/Cmd to select multiple.</small>
            </div>
        </div>

        {{-- Form Builder --}}
        <div class="main-box">
            <header class="main-box-header clearfix">
                <h2>{{ trans('marble::admin.is_form') }}</h2>
            </header>
            <div class="main-box-body clearfix">
                <div class="form-group">
                    <label class="checkbox-inline" style="display:flex;align-items:center;gap:8px;font-weight:normal">
                        <input type="hidden" name="is_form" value="0">
                        <input type="checkbox" name="is_form" value="1" id="is_form_checkbox" {{ $blueprint->is_form ? 'checked' : '' }}>
                        {{ trans('marble::admin.is_form') }}
                    </label>
                    <small class="text-muted" style="display:block;margin-top:4px">{{ trans('marble::admin.is_form_hint') }}</small>
                </div>
                <div id="form-builder-options" style="{{ $blueprint->is_form ? '' : 'display:none' }}">
                    <div class="form-group">
                        <label>{{ trans('marble::admin.form_recipients') }}</label>
                        <input type="text" name="form_recipients" value="{{ $blueprint->form_recipients }}" class="form-control" placeholder="email@example.com, other@example.com" />
                        <small class="text-muted">{{ trans('marble::admin.form_recipients_hint') }}</small>
                    </div>
                    <div class="form-group">
                        <label>{{ trans('marble::admin.form_success_message') }}</label>
                        <input type="text" name="form_success_message" value="{{ $blueprint->form_success_message }}" class="form-control" placeholder="Thank you for your message!" />
                    </div>
                    <div class="form-group">
                        <label>{{ trans('marble::admin.form_success_redirect') }}</label>
                        <select name="form_success_item_id" class="form-control">
                            <option value="">— {{ trans('marble::admin.none') }} —</option>
                            @foreach($allBlueprints->flatten(1) as $bp)
                                @foreach($bp->items()->where('status','published')->get() as $successItem)
                                    <option value="{{ $successItem->id }}" {{ $blueprint->form_success_item_id == $successItem->id ? 'selected' : '' }}>
                                        {{ $bp->name }}: {{ $successItem->name() }}
                                    </option>
                                @endforeach
                            @endforeach
                        </select>
                        <small class="text-muted">{{ trans('marble::admin.form_success_redirect_hint') }}</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group pull-right">
            <button type="submit" class="btn btn-success">@include('marble::components.famicon', ['name' => 'disk']) {{ trans('marble::admin.save') }}</button>
        </div>
    </form>

    <script>
        document.getElementById('is_form_checkbox').addEventListener('change', function(){
            document.getElementById('form-builder-options').style.display = this.checked ? '' : 'none';
        });

        // Icon search/autocomplete
        (function() {
            var icons    = @json($famicons);
            var baseUrl  = '{{ asset('vendor/marble/assets/images/famicons/') }}/';
            var $input   = $('#icon-search');
            var $hidden  = $('#icon-value');
            var $preview = $('#icon-preview');
            var $list    = $('#icon-suggestions');

            function showSuggestions(query) {
                var q = query.toLowerCase().replace(/\s+/g, '_');
                var matches = q.length === 0
                    ? icons.slice(0, 40)
                    : icons.filter(function(i) { return i.indexOf(q) !== -1; }).slice(0, 40);

                $list.empty();
                if (matches.length === 0) { $list.hide(); return; }

                matches.forEach(function(icon) {
                    var $li = $('<li>').css({
                        display: 'flex', alignItems: 'center', gap: '8px',
                        padding: '5px 10px', cursor: 'pointer', fontSize: '13px'
                    }).hover(
                        function() { $(this).css('background', '#e8f0fb'); },
                        function() { $(this).css('background', ''); }
                    ).on('mousedown', function(e) {
                        e.preventDefault();
                        selectIcon(icon);
                    });
                    $li.append($('<img>').attr('src', baseUrl + icon + '.svg').css({ width: 16, height: 16, flexShrink: 0 }));
                    $li.append($('<span>').text(icon));
                    $list.append($li);
                });
                $list.show();
            }

            function selectIcon(icon) {
                $hidden.val(icon);
                $input.val(icon);
                $preview.attr('src', baseUrl + icon + '.svg').css('opacity', 1);
                $list.hide();
            }

            $input.on('input', function() {
                showSuggestions($(this).val());
            }).on('focus', function() {
                showSuggestions($(this).val());
            }).on('blur', function() {
                setTimeout(function() { $list.hide(); }, 150);
            }).on('keydown', function(e) {
                var $items = $list.children('li');
                var $active = $list.children('li.ac-active');
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    var $next = $active.length ? $active.removeClass('ac-active').next() : $items.first();
                    $next.addClass('ac-active').css('background', '#e8f0fb');
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    var $prev = $active.length ? $active.removeClass('ac-active').prev() : $items.last();
                    $prev.addClass('ac-active').css('background', '#e8f0fb');
                } else if (e.key === 'Enter' && $active.length) {
                    e.preventDefault();
                    selectIcon($active.find('span').text());
                } else if (e.key === 'Escape') {
                    $list.hide();
                }
            });

            $(document).on('click', function(e) {
                if (!$(e.target).closest('#icon-search, #icon-suggestions').length) {
                    $list.hide();
                }
            });
        })();
    </script>
@endsection
