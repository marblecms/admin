@extends('marble::layouts.app')

@php $isNew = !$portalUser->exists; @endphp

@section('content')
    <h1>{{ $isNew ? trans('marble::admin.add_portal_user') : $portalUser->email }}</h1>

    <form action="{{ $isNew ? route('marble.portal-user.store') : route('marble.portal-user.update', $portalUser) }}" method="POST">
        @csrf
        @if(!$isNew) @method('POST') @endif

        <div class="main-box">
            <header class="main-box-header clearfix">
                <h2><b>{{ trans('marble::admin.portal_user') }}</b></h2>
            </header>
            <div class="main-box-body clearfix">

                <div class="form-group {{ $errors->has('email') ? 'has-error' : '' }}">
                    <label>{{ trans('marble::admin.email') }}</label>
                    <input type="email" class="form-control" name="email" value="{{ old('email', $portalUser->email) }}" required />
                    @error('email')<span class="help-block">{{ $message }}</span>@enderror
                </div>

                <div class="form-group {{ $errors->has('password') ? 'has-error' : '' }}">
                    <label>{{ trans('marble::admin.password') }}{{ !$isNew ? ' (' . trans('marble::admin.leave_blank_to_keep') . ')' : '' }}</label>
                    <input type="password" class="form-control" name="password" {{ $isNew ? 'required' : '' }} autocomplete="new-password" />
                    @error('password')<span class="help-block">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label>{{ trans('marble::admin.linked_item') }}</label>
                    <small class="text-muted marble-block marble-mb-xs">{{ trans('marble::admin.portal_user_item_hint') }}</small>
                    <div class="marble-flex-center">
                        <input type="hidden" name="item_id" id="pu_item_id_input" value="{{ old('item_id', $portalUser->item_id) }}" />
                        <input type="text" class="form-control" id="pu_item_id_display"
                               value="{{ $portalUser->item?->name() }}"
                               placeholder="{{ trans('marble::admin.select_item_placeholder') }}"
                               readonly />
                        <button type="button" class="btn btn-default btn-sm"
                                onclick="ObjectBrowser.open(function(node){ document.getElementById('pu_item_id_input').value=node.id; document.getElementById('pu_item_id_display').value=node.name; })">
                            {{ trans('marble::admin.select_object') }}
                        </button>
                        <button type="button" class="btn btn-default btn-sm"
                                onclick="document.getElementById('pu_item_id_input').value=''; document.getElementById('pu_item_id_display').value='';">
                            {{ trans('marble::admin.remove') }}
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label class="perm-checkbox-label">
                        <input type="checkbox" name="enabled" value="1" {{ old('enabled', $portalUser->exists ? $portalUser->enabled : true) ? 'checked' : '' }}>
                        {{ trans('marble::admin.active') }}
                    </label>
                </div>
            </div>
        </div>

        <div class="form-group pull-right">
            <a href="{{ route('marble.portal-user.index') }}" class="btn btn-primary">
                @include('marble::components.famicon', ['name' => 'cancel']) {{ trans('marble::admin.cancel') }}
            </a>
            <button type="submit" class="btn btn-success">
                @include('marble::components.famicon', ['name' => 'disk']) {{ trans('marble::admin.save') }}
            </button>
        </div>
        <div class="clearfix"></div>
    </form>
@endsection
