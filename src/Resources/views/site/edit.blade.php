@extends('marble::layouts.app')

@php
    $isNew   = $site === null;
    $saveUrl = $isNew ? route('marble.site.store') : route('marble.site.update', $site);
@endphp

@section('content')
    <h1>{{ $isNew ? trans('marble::admin.add_site') : trans('marble::admin.edit_site') }}</h1>

    <form action="{{ $saveUrl }}" method="POST">
        @csrf

        <div class="main-box">
            <div class="main-box-body clearfix">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{ trans('marble::admin.name') }}</label>
                            <input type="text" name="name" value="{{ old('name', $site?->name) }}" class="form-control" required />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{ trans('marble::admin.domain') }}</label>
                            <input type="text" name="domain" value="{{ old('domain', $site?->domain) }}" class="form-control" placeholder="example.com" />
                            <p class="help-block">{{ trans('marble::admin.domain_hint') }}</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{ trans('marble::admin.root_item') }}</label>
                            <select name="root_item_id" class="form-control">
                                <option value="">— {{ trans('marble::admin.none') }} —</option>
                                @foreach($items as $item)
                                    <option value="{{ $item->id }}" {{ old('root_item_id', $site?->root_item_id) == $item->id ? 'selected' : '' }}>
                                        {{ str_repeat('— ', $item->_depth) }}{{ $item->name() ?: '[' . $item->id . ']' }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="help-block">{{ trans('marble::admin.root_item_hint') }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{ trans('marble::admin.settings_item') }}</label>
                            <select name="settings_item_id" class="form-control">
                                <option value="">— {{ trans('marble::admin.none') }} —</option>
                                @foreach($items as $item)
                                    <option value="{{ $item->id }}" {{ old('settings_item_id', $site?->settings_item_id) == $item->id ? 'selected' : '' }}>
                                        {{ str_repeat('— ', $item->_depth) }}{{ $item->name() ?: '[' . $item->id . ']' }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="help-block">{{ trans('marble::admin.settings_item_hint') }}</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{ trans('marble::admin.default_language') }}</label>
                            <select name="default_language_id" class="form-control">
                                <option value="">— {{ trans('marble::admin.none') }} —</option>
                                @foreach($languages as $lang)
                                    <option value="{{ $lang->id }}" {{ old('default_language_id', $site?->default_language_id) == $lang->id ? 'selected' : '' }}>
                                        {{ $lang->name }} ({{ $lang->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="active" value="1" {{ old('active', $site?->active ?? true) ? 'checked' : '' }} />
                        {{ trans('marble::admin.active') }}
                    </label>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_default" value="1" {{ old('is_default', $site?->is_default ?? false) ? 'checked' : '' }} />
                        {{ trans('marble::admin.is_default_site') }}
                    </label>
                    <p class="help-block">{{ trans('marble::admin.is_default_site_hint') }}</p>
                </div>
            </div>
        </div>

        <div class="clearfix">
            <button type="submit" class="btn btn-success pull-right">
                @include('marble::components.famicon', ['name' => 'disk']) {{ trans('marble::admin.save') }}
            </button>
        </div>
    </form>
@endsection
