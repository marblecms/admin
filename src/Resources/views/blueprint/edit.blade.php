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
                                <select name="icon" class="form-control" onchange="document.getElementById('icon-preview').src='{{ asset('vendor/marble/assets/images/famicons/') }}/' + this.value + '.svg'">
                                    @foreach($famicons as $icon)
                                        <option value="{{ $icon }}" {{ $blueprint->icon === $icon ? 'selected' : '' }}>{{ $icon }}</option>
                                    @endforeach
                                </select>
                                <img id="icon-preview" src="{{ asset('vendor/marble/assets/images/famicons/' . ($blueprint->icon ?: 'page') . '.svg') }}" width="24" height="24" alt="">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>{{ trans('marble::admin.inherits_from') }}</label>
                    <select name="parent_blueprint_id" class="form-control">
                        <option value="">— {{ trans('marble::admin.none') }} —</option>
                        @foreach($allBlueprints as $bp)
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

        {{-- Allowed Children --}}
        <div class="main-box">
            <header class="main-box-header clearfix">
                <h2>Allowed Child Blueprints</h2>
            </header>
            <div class="main-box-body clearfix">
                <select multiple name="allowed_child_blueprints[]" class="form-control" size="8">
                    <option value="all" {{ $blueprint->allowsAllChildren() ? 'selected' : '' }}>— All —</option>
                    @foreach($allBlueprints as $bp)
                        <option value="{{ $bp->id }}" {{ $blueprint->allowedChildBlueprints->contains($bp->id) ? 'selected' : '' }}>{{ $bp->name }}</option>
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
                            @foreach($allBlueprints as $bp)
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

        <div class="form-group">
            <a class="btn btn-danger" href="{{ url("{$prefix}/blueprint/all") }}">@include('marble::components.famicon', ['name' => 'cancel']) {{ trans('marble::admin.cancel') }}</a>
            <button type="submit" class="btn btn-success">@include('marble::components.famicon', ['name' => 'disk']) {{ trans('marble::admin.save') }}</button>
        </div>
    </form>

    <script>
        document.getElementById('is_form_checkbox').addEventListener('change', function(){
            document.getElementById('form-builder-options').style.display = this.checked ? '' : 'none';
        });
    </script>
@endsection
