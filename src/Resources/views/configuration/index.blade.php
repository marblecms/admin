@extends('marble::layouts.app')

@section('content')
    <h1>{{ trans('marble::admin.configuration') }}</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    {{-- General Settings --}}
    <div class="main-box">
        <header class="main-box-header clearfix">
            <h2>@include('marble::components.famicon', ['name' => 'wrench']) {{ trans('marble::admin.general_settings') }}</h2>
        </header>
        <div class="main-box-body clearfix">
            <form method="POST" action="{{ route('marble.configuration.settings.save') }}">
                @csrf
                <table class="table" style="margin-bottom:0">
                    <tbody>
                        <tr>
                            <td style="width:50%;vertical-align:middle">
                                <strong>{{ trans('marble::admin.frontend_url') }}</strong>
                                <p class="text-muted" style="font-size:11px;margin:2px 0 0">{{ trans('marble::admin.frontend_url_hint') }}</p>
                            </td>
                            <td>
                                <input type="url" name="frontend_url" class="form-control input-sm"
                                       value="{{ $settings['frontend_url'] ?? config('marble.frontend_url') }}"
                                       placeholder="https://example.com" style="max-width:300px" />
                            </td>
                        </tr>
                        <tr>
                            <td style="vertical-align:middle">
                                <strong>{{ trans('marble::admin.primary_locale') }}</strong>
                                <p class="text-muted" style="font-size:11px;margin:2px 0 0">{{ trans('marble::admin.primary_locale_hint') }}</p>
                            </td>
                            <td>
                                <select name="primary_locale" class="form-control input-sm" style="width:160px">
                                    @foreach($languages as $lang)
                                        <option value="{{ $lang->code }}"
                                            {{ ($settings['primary_locale'] ?? config('marble.primary_locale')) === $lang->code ? 'selected' : '' }}>
                                            {{ $lang->name }} ({{ $lang->code }})
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td style="vertical-align:middle">
                                <strong>{{ trans('marble::admin.uri_locale_prefix') }}</strong>
                                <p class="text-muted" style="font-size:11px;margin:2px 0 0">{{ trans('marble::admin.uri_locale_prefix_hint') }}</p>
                            </td>
                            <td style="vertical-align:middle">
                                <input type="checkbox" name="uri_locale_prefix" value="1"
                                       {{ filter_var($settings['uri_locale_prefix'] ?? config('marble.uri_locale_prefix'), FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' }} />
                            </td>
                        </tr>
                        <tr>
                            <td style="vertical-align:middle">
                                <strong>{{ trans('marble::admin.autosave') }}</strong>
                                <p class="text-muted" style="font-size:11px;margin:2px 0 0">{{ trans('marble::admin.autosave_interval_hint') }}</p>
                            </td>
                            <td style="vertical-align:middle">
                                <input type="checkbox" name="autosave" value="1"
                                       {{ filter_var($settings['autosave'] ?? config('marble.autosave'), FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' }} />
                                &nbsp;
                                <input type="number" name="autosave_interval" class="form-control input-sm" style="width:70px;display:inline-block"
                                       value="{{ $settings['autosave_interval'] ?? config('marble.autosave_interval') }}"
                                       min="5" max="300" />
                                <span class="text-muted" style="font-size:12px;white-space:nowrap">{{ trans('marble::admin.seconds') }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td style="vertical-align:middle">
                                <strong>{{ trans('marble::admin.lock_ttl') }}</strong>
                                <p class="text-muted" style="font-size:11px;margin:2px 0 0">{{ trans('marble::admin.lock_ttl_hint') }}</p>
                            </td>
                            <td style="vertical-align:middle;white-space:nowrap">
                                <input type="number" name="lock_ttl" class="form-control input-sm" style="width:80px;display:inline-block"
                                       value="{{ $settings['lock_ttl'] ?? config('marble.lock_ttl') }}"
                                       min="30" max="3600" />
                                <span class="text-muted" style="font-size:12px">{{ trans('marble::admin.seconds') }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td style="vertical-align:middle">
                                <strong>{{ trans('marble::admin.cache_ttl') }}</strong>
                                <p class="text-muted" style="font-size:11px;margin:2px 0 0">{{ trans('marble::admin.cache_ttl_hint') }}</p>
                            </td>
                            <td style="vertical-align:middle;white-space:nowrap">
                                <input type="number" name="cache_ttl" class="form-control input-sm" style="width:80px;display:inline-block"
                                       value="{{ $settings['cache_ttl'] ?? config('marble.cache_ttl') }}"
                                       min="0" max="86400" />
                                <span class="text-muted" style="font-size:12px">{{ trans('marble::admin.seconds') }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div style="padding:12px 15px">
                    <button type="submit" class="btn btn-success btn-sm">
                        @include('marble::components.famicon', ['name' => 'disk']) {{ trans('marble::admin.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Languages --}}
    <div class="main-box">
        <header class="main-box-header clearfix">
            <h2>@include('marble::components.famicon', ['name' => 'world']) {{ trans('marble::admin.languages') }}</h2>
        </header>
        <div class="main-box-body clearfix">
            <p class="text-muted" style="font-size:12px;margin:0 0 12px">{{ trans('marble::admin.content_languages_hint') }}</p>
            <form method="POST" action="{{ route('marble.configuration.languages.save') }}">
                @csrf
                <table class="table" style="margin-bottom:0">
                    <thead>
                        <tr>
                            <th>{{ trans('marble::admin.language') }}</th>
                            <th style="width:80px">{{ trans('marble::admin.code') }}</th>
                            <th style="width:80px;text-align:center">{{ trans('marble::admin.active') }}</th>
                            <th style="width:60px"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($languages as $lang)
                        <tr>
                            <td>@include('marble::components.famicon', ['name' => 'world']) {{ $lang->name }}</td>
                            <td><code>{{ $lang->code }}</code></td>
                            <td style="text-align:center">
                                <input type="checkbox" name="active_languages[]" value="{{ $lang->id }}" {{ $lang->is_active ? 'checked' : '' }} />
                            </td>
                            <td>
                                @if($languages->count() > 1)
                                <form method="POST" action="{{ route('marble.configuration.languages.delete', $lang) }}" onsubmit="return confirm('{{ trans('marble::admin.are_you_sure') }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger">@include('marble::components.famicon', ['name' => 'bin'])</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                        {{-- Add new language inline --}}
                        <tr style="background:#f9f9f9">
                            <td>
                                <input type="text" form="add-language-form" name="name" class="form-control input-sm" placeholder="{{ trans('marble::admin.language') }} (e.g. English)" style="width:180px" />
                            </td>
                            <td>
                                <input type="text" form="add-language-form" name="code" class="form-control input-sm" placeholder="en" maxlength="8" style="width:60px" />
                            </td>
                            <td style="text-align:center">
                                <input type="checkbox" disabled checked title="{{ trans('marble::admin.active') }}" />
                            </td>
                            <td>
                                <button type="submit" form="add-language-form" class="btn btn-xs btn-success">
                                    @include('marble::components.famicon', ['name' => 'add'])
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div style="padding:12px 15px">
                    <button type="submit" class="btn btn-success btn-sm">
                        @include('marble::components.famicon', ['name' => 'disk']) {{ trans('marble::admin.save') }}
                    </button>
                </div>
            </form>
            <form id="add-language-form" method="POST" action="{{ route('marble.configuration.languages.add') }}">
                @csrf
            </form>
        </div>
    </div>
@endsection
