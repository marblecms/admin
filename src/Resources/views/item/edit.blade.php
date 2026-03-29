@extends('marble::layouts.app')

@section('javascript-head')
    <script type="text/javascript" src="{{ asset('vendor/marble/assets/js/attributes/attributes.js') }}"></script>
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

        @if(config('marble.autosave', false))
        // Autosave
        var autosaveDelay = {{ config('marble.autosave_interval', 30) * 1000 }};
        var autosaveTimer = null;

        function marbleAutosave() {
            var $form = $('#marble-edit-form');
            $('#autosave-indicator').text('Saving…').show();
            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: $form.serialize(),
                success: function() {
                    formDirty = false;
                    $('#autosave-indicator').text('Saved').fadeOut(2000);
                },
                error: function() {
                    $('#autosave-indicator').text('Autosave failed').show();
                }
            });
        }

        $(function() {
            $('#marble-edit-form :input').on('change input', function() {
                clearTimeout(autosaveTimer);
                autosaveTimer = setTimeout(marbleAutosave, autosaveDelay);
            });
        });
        @endif
    </script>
@endsection

@section('sidebar')

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
                                <span style="color:#5cb85c;font-weight:bold">{{ trans('marble::admin.published') }}</span>
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
                                @include('marble::components.famicon', ['name' => 'application_side_tree'])
                                {{ trans('marble::admin.show_in_nav') }}
                            </span>
                            <form method="POST" action="{{ route('marble.item.toggle-nav', $item) }}" style="display:inline">
                                @csrf
                                <button type="submit" class="btn btn-xs {{ $item->show_in_nav ? 'btn-success' : 'btn-default' }}">
                                    {{ $item->show_in_nav ? trans('marble::admin.yes') : trans('marble::admin.no') }}
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
            <small id="autosave-indicator" style="display:none; color:#999; font-weight:normal; margin-left:10px"></small>
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
                <button type="submit" class="btn btn-success">@include('marble::components.famicon', ['name' => 'disk']) {{ trans('marble::admin.save') }}</button>
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
                        @if($field->_inherited ?? false)
                            <div style="opacity:0.6; pointer-events:none; position:relative">
                                <span style="position:absolute;top:4px;right:8px;font-size:10px;color:#999;z-index:1">
                                    @include('marble::components.famicon', ['name' => 'brick']) {{ trans('marble::admin.inherited') }}
                                </span>
                                @include('marble::item.edit_field', ['field' => $field, 'item' => $item, 'languages' => $languages])
                            </div>
                        @else
                            @include('marble::item.edit_field', ['field' => $field, 'item' => $item, 'languages' => $languages])
                        @endif
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
@endsection
