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
                <table class="table"class="marble-table-flush">
                    <tbody>
                        <tr>
                            <td class="marble-config-label">
                                <strong>{{ trans('marble::admin.frontend_url') }}</strong>
                                <p class="text-muted marble-hint marble-mb-0">{{ trans('marble::admin.frontend_url_hint') }}</p>
                            </td>
                            <td>
                                <input type="url" name="frontend_url" class="form-control input-sm marble-input-md-w"
                                       value="{{ $settings['frontend_url'] ?? config('marble.frontend_url') }}"
                                       placeholder="https://example.com" />
                            </td>
                        </tr>
                        <tr>
                            <td class="marble-vmid">
                                <strong>{{ trans('marble::admin.primary_locale') }}</strong>
                                <p class="text-muted marble-hint marble-mb-0">{{ trans('marble::admin.primary_locale_hint') }}</p>
                            </td>
                            <td>
                                <select name="primary_locale" class="form-control input-sm marble-col-lg">
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
                            <td class="marble-vmid">
                                <strong>{{ trans('marble::admin.uri_locale_prefix') }}</strong>
                                <p class="text-muted marble-hint marble-mb-0">{{ trans('marble::admin.uri_locale_prefix_hint') }}</p>
                            </td>
                            <td class="marble-vmid">
                                <input type="checkbox" name="uri_locale_prefix" value="1"
                                       {{ filter_var($settings['uri_locale_prefix'] ?? config('marble.uri_locale_prefix'), FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' }} />
                            </td>
                        </tr>
                        <tr>
                            <td class="marble-vmid">
                                <strong>{{ trans('marble::admin.autosave') }}</strong>
                                <p class="text-muted marble-hint marble-mb-0">{{ trans('marble::admin.autosave_interval_hint') }}</p>
                            </td>
                            <td class="marble-vmid">
                                <input type="checkbox" name="autosave" value="1"
                                       {{ filter_var($settings['autosave'] ?? config('marble.autosave'), FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' }} />
                                &nbsp;
                                <input type="number" name="autosave_interval" class="form-control input-sm marble-input-num-xs"
                                       value="{{ $settings['autosave_interval'] ?? config('marble.autosave_interval') }}"
                                       min="5" max="300" />
                                <span class="text-muted marble-text-sm marble-nowrap">{{ trans('marble::admin.seconds') }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="marble-vmid">
                                <strong>{{ trans('marble::admin.lock_ttl') }}</strong>
                                <p class="text-muted marble-hint marble-mb-0">{{ trans('marble::admin.lock_ttl_hint') }}</p>
                            </td>
                            <td class="marble-vmid marble-nowrap">
                                <input type="number" name="lock_ttl" class="form-control input-sm marble-input-num-sm"
                                       value="{{ $settings['lock_ttl'] ?? config('marble.lock_ttl') }}"
                                       min="30" max="3600" />
                                <span class="text-muted marble-text-sm">{{ trans('marble::admin.seconds') }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="marble-vmid">
                                <strong>{{ trans('marble::admin.cache_ttl') }}</strong>
                                <p class="text-muted marble-hint marble-mb-0">{{ trans('marble::admin.cache_ttl_hint') }}</p>
                            </td>
                            <td class="marble-vmid marble-nowrap">
                                <input type="number" name="cache_ttl" class="form-control input-sm marble-input-num-sm"
                                       value="{{ $settings['cache_ttl'] ?? config('marble.cache_ttl') }}"
                                       min="0" max="86400" />
                                <span class="text-muted marble-text-sm">{{ trans('marble::admin.seconds') }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="marble-box-body">
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
            <p class="text-muted marble-text-sm marble-mb-sm">{{ trans('marble::admin.content_languages_hint') }}</p>
            <form method="POST" action="{{ route('marble.configuration.languages.save') }}">
                @csrf
                <table class="table"class="marble-table-flush">
                    <thead>
                        <tr>
                            <th>{{ trans('marble::admin.language') }}</th>
                            <th class="marble-col-sm">{{ trans('marble::admin.code') }}</th>
                            <th class="marble-col-sm text-center">{{ trans('marble::admin.active') }}</th>
                            <th class="marble-col-xs"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($languages as $lang)
                        <tr>
                            <td>@include('marble::components.famicon', ['name' => 'world']) {{ $lang->name }}</td>
                            <td><code>{{ $lang->code }}</code></td>
                            <td class="text-center">
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
                        <tr class="marble-row-new">
                            <td>
                                <input type="text" form="add-language-form" name="name" class="form-control input-sm marble-lang-name-input" placeholder="{{ trans('marble::admin.language') }} (e.g. English)" />
                            </td>
                            <td>
                                <input type="text" form="add-language-form" name="code" class="form-control input-sm marble-lang-select" placeholder="en" maxlength="8" />
                            </td>
                            <td class="text-center">
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
                <div class="marble-box-body">
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

    {{-- Media Blueprints --}}
    <div class="main-box">
        <header class="main-box-header clearfix">
            <h2>@include('marble::components.famicon', ['name' => 'pictures']) {{ trans('marble::admin.media_blueprints') }}</h2>
        </header>
        <div class="main-box-body clearfix">
            <p class="text-muted marble-hint">{{ trans('marble::admin.media_blueprints_hint') }}</p>
            <form method="POST" action="{{ route('marble.configuration.media-blueprints.save') }}" id="media-blueprints-form">
                @csrf
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ trans('marble::admin.mime_pattern') }}</th>
                            <th>{{ trans('marble::admin.blueprint') }}</th>
                            <th class="marble-col-xs"></th>
                        </tr>
                    </thead>
                    <tbody id="media-blueprint-rules-body">
                        @foreach($mediaBlueprintRules as $i => $rule)
                            <tr>
                                <td>
                                    <input type="text" name="rules[{{ $i }}][mime_pattern]"
                                           value="{{ $rule->mime_pattern }}"
                                           class="form-control input-sm"
                                           placeholder="image/*, application/pdf, video/mp4" />
                                </td>
                                <td>
                                    <select name="rules[{{ $i }}][blueprint_id]" class="form-control input-sm">
                                        @foreach($blueprints as $bp)
                                            <option value="{{ $bp->id }}" {{ $rule->blueprint_id == $bp->id ? 'selected' : '' }}>{{ $bp->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-danger btn-xs marble-remove-rule">
                                        @include('marble::components.famicon', ['name' => 'bin'])
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                        {{-- Add new rule inline --}}
                        <tr class="marble-row-new" id="media-rule-new-row">
                            <td>
                                <input type="text" id="new-rule-mime" class="form-control input-sm" placeholder="image/*, application/pdf, video/mp4" />
                            </td>
                            <td>
                                <select id="new-rule-blueprint" class="form-control input-sm">
                                    @foreach($blueprints as $bp)
                                        <option value="{{ $bp->id }}">{{ $bp->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <button type="button" class="btn btn-success btn-xs" id="marble-add-rule">
                                    @include('marble::components.famicon', ['name' => 'add'])
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="form-group marble-mt-sm">
                    <label><strong>{{ trans('marble::admin.default_blueprint') }}</strong></label>
                    <p class="text-muted marble-hint">{{ trans('marble::admin.default_blueprint_hint') }}</p>
                    <select name="media_default_blueprint_id" class="form-control input-sm marble-input-md-w">
                        <option value="">— {{ trans('marble::admin.none') }} —</option>
                        @foreach($blueprints as $bp)
                            <option value="{{ $bp->id }}"
                                {{ ($settings['media_default_blueprint_id'] ?? '') == $bp->id ? 'selected' : '' }}>
                                {{ $bp->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group pull-right">
                    <button type="submit" class="btn btn-success">
                        @include('marble::components.famicon', ['name' => 'disk']) {{ trans('marble::admin.save') }}
                    </button>
                </div>
                <div class="clearfix"></div>
            </form>
        </div>
    </div>
    {{-- Smart Crops --}}
    <div class="main-box">
        <header class="main-box-header clearfix">
            <h2>@include('marble::components.famicon', ['name' => 'pictures']) {{ trans('marble::admin.smart_crops') }}</h2>
        </header>
        <div class="main-box-body clearfix">
            <p class="text-muted marble-hint">{{ trans('marble::admin.smart_crops_hint') }}</p>
            <form method="POST" action="{{ route('marble.configuration.crop-presets.save') }}" id="crop-presets-form">
                @csrf
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ trans('marble::admin.crop_name') }}</th>
                            <th>{{ trans('marble::admin.crop_label') }}</th>
                            <th class="marble-col-sm">{{ trans('marble::admin.crop_width') }}</th>
                            <th class="marble-col-sm">{{ trans('marble::admin.crop_height') }}</th>
                            <th class="marble-col-xs"></th>
                        </tr>
                    </thead>
                    <tbody id="crop-presets-body">
                        @foreach($cropPresets as $i => $preset)
                            <tr>
                                <td>
                                    <input type="text" name="presets[{{ $i }}][name]"
                                           value="{{ $preset->name }}"
                                           class="form-control input-sm"
                                           placeholder="hero" />
                                </td>
                                <td>
                                    <input type="text" name="presets[{{ $i }}][label]"
                                           value="{{ $preset->label }}"
                                           class="form-control input-sm"
                                           placeholder="Hero 16:9" />
                                </td>
                                <td>
                                    <input type="number" name="presets[{{ $i }}][width]"
                                           value="{{ $preset->width }}"
                                           class="form-control input-sm marble-input-num-sm"
                                           min="1" max="8000" />
                                </td>
                                <td>
                                    <input type="number" name="presets[{{ $i }}][height]"
                                           value="{{ $preset->height }}"
                                           class="form-control input-sm marble-input-num-sm"
                                           min="1" max="8000" />
                                </td>
                                <td>
                                    <button type="button" class="btn btn-danger btn-xs marble-remove-crop">
                                        @include('marble::components.famicon', ['name' => 'bin'])
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                        {{-- Add new crop inline --}}
                        <tr class="marble-row-new" id="crop-new-row">
                            <td><input type="text" id="new-crop-name"   class="form-control input-sm" placeholder="hero" /></td>
                            <td><input type="text" id="new-crop-label"  class="form-control input-sm" placeholder="Hero 16:9" /></td>
                            <td><input type="number" id="new-crop-width"  class="form-control input-sm marble-input-num-sm" min="1" max="8000" placeholder="1920" /></td>
                            <td><input type="number" id="new-crop-height" class="form-control input-sm marble-input-num-sm" min="1" max="8000" placeholder="1080" /></td>
                            <td>
                                <button type="button" class="btn btn-success btn-xs" id="marble-add-crop">
                                    @include('marble::components.famicon', ['name' => 'add'])
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="form-group pull-right">
                    <button type="submit" class="btn btn-success">
                        @include('marble::components.famicon', ['name' => 'disk']) {{ trans('marble::admin.save') }}
                    </button>
                </div>
                <div class="clearfix"></div>
            </form>
        </div>
    </div>

    {{-- AI Assistant --}}
    <div class="main-box">
        <header class="main-box-header clearfix">
            <h2>@include('marble::components.famicon', ['name' => 'star']) {{ trans('marble::admin.ai_settings') }}</h2>
        </header>
        <div class="main-box-body clearfix">
            <p class="text-muted marble-text-sm marble-mb-sm">{{ trans('marble::admin.ai_settings_hint') }}</p>
            <form method="POST" action="{{ route('marble.configuration.ai-settings.save') }}">
                @csrf
                <table class="table marble-table-flush">
                    <tbody>
                        <tr>
                            <td class="marble-config-label marble-vmid">
                                <strong>{{ trans('marble::admin.ai_provider') }}</strong>
                            </td>
                            <td>
                                <select name="ai_provider" class="form-control input-sm marble-col-lg" id="ai-provider-select">
                                    <option value="disabled" {{ ($settings['ai_provider'] ?? 'disabled') === 'disabled' ? 'selected' : '' }}>{{ trans('marble::admin.disabled') }}</option>
                                    <option value="openai"   {{ ($settings['ai_provider'] ?? '') === 'openai'   ? 'selected' : '' }}>OpenAI</option>
                                    <option value="anthropic"{{ ($settings['ai_provider'] ?? '') === 'anthropic'? 'selected' : '' }}>Anthropic (Claude)</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="marble-config-label marble-vmid">
                                <strong>{{ trans('marble::admin.ai_api_key') }}</strong>
                                <p class="text-muted marble-hint marble-mb-0">{{ trans('marble::admin.ai_api_key_hint') }}</p>
                            </td>
                            <td>
                                <input type="password" name="ai_api_key" class="form-control input-sm marble-input-md-w"
                                       value="{{ !empty($settings['ai_api_key']) ? '••••••••' : '' }}"
                                       autocomplete="new-password"
                                       placeholder="{{ trans('marble::admin.ai_api_key_placeholder') }}" />
                            </td>
                        </tr>
                        <tr>
                            <td class="marble-config-label marble-vmid">
                                <strong>{{ trans('marble::admin.ai_model') }}</strong>
                                <p class="text-muted marble-hint marble-mb-0">{{ trans('marble::admin.ai_model_hint') }}</p>
                            </td>
                            <td>
                                <input type="text" name="ai_model" class="form-control input-sm marble-input-md-w"
                                       value="{{ $settings['ai_model'] ?? '' }}"
                                       placeholder="gpt-4o / claude-sonnet-4-6" />
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="marble-box-body">
                    <button type="submit" class="btn btn-success btn-sm">
                        @include('marble::components.famicon', ['name' => 'disk']) {{ trans('marble::admin.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('javascript')
<script>
(function () {
    function reindex() {
        $('#media-blueprint-rules-body tr:not(#media-rule-new-row)').each(function(i) {
            $(this).find('input[type=text]').attr('name', 'rules[' + i + '][mime_pattern]');
            $(this).find('select').attr('name', 'rules[' + i + '][blueprint_id]');
        });
    }

    $('#marble-add-rule').on('click', function () {
        var mime = $('#new-rule-mime').val().trim();
        var bpId = $('#new-rule-blueprint').val();
        if (!mime) { $('#new-rule-mime').focus(); return; }

        var i = $('#media-blueprint-rules-body tr:not(#media-rule-new-row)').length;
        // Clone the select from the new-row to get a properly populated copy
        var $newSelect = $('#new-rule-blueprint').clone()
            .attr('name', 'rules[' + i + '][blueprint_id]')
            .val(bpId);
        var row = $('<tr></tr>');
        row.append($('<td></td>').append(
            $('<input type="text" class="form-control input-sm">').attr('name', 'rules[' + i + '][mime_pattern]').attr('placeholder', 'image/*, application/pdf, video/mp4').val(mime)
        ));
        row.append($('<td></td>').append($newSelect));
        row.append('<td><button type="button" class="btn btn-danger btn-xs marble-remove-rule">&times;</button></td>');
        $('#media-rule-new-row').before(row);

        $('#new-rule-mime').val('');
        $('#new-rule-blueprint').prop('selectedIndex', 0);
    });

    $(document).on('click', '.marble-remove-rule', function () {
        $(this).closest('tr').remove();
        reindex();
    });
})();

(function () {
    function reindexCrops() {
        $('#crop-presets-body tr:not(#crop-new-row)').each(function(i) {
            $(this).find('input[name*="[name]"]').attr('name',   'presets[' + i + '][name]');
            $(this).find('input[name*="[label]"]').attr('name',  'presets[' + i + '][label]');
            $(this).find('input[name*="[width]"]').attr('name',  'presets[' + i + '][width]');
            $(this).find('input[name*="[height]"]').attr('name', 'presets[' + i + '][height]');
        });
    }

    $('#marble-add-crop').on('click', function () {
        var name   = $('#new-crop-name').val().trim();
        var label  = $('#new-crop-label').val().trim();
        var width  = $('#new-crop-width').val().trim();
        var height = $('#new-crop-height').val().trim();

        if (!name)   { $('#new-crop-name').focus(); return; }
        if (!label)  { $('#new-crop-label').focus(); return; }
        if (!width)  { $('#new-crop-width').focus(); return; }
        if (!height) { $('#new-crop-height').focus(); return; }

        var i = $('#crop-presets-body tr:not(#crop-new-row)').length;
        var row = $('<tr></tr>');
        row.append($('<td></td>').append(
            $('<input type="text" class="form-control input-sm">').attr('name', 'presets[' + i + '][name]').val(name)
        ));
        row.append($('<td></td>').append(
            $('<input type="text" class="form-control input-sm">').attr('name', 'presets[' + i + '][label]').val(label)
        ));
        row.append($('<td></td>').append(
            $('<input type="number" class="form-control input-sm marble-input-num-sm" min="1" max="8000">').attr('name', 'presets[' + i + '][width]').val(width)
        ));
        row.append($('<td></td>').append(
            $('<input type="number" class="form-control input-sm marble-input-num-sm" min="1" max="8000">').attr('name', 'presets[' + i + '][height]').val(height)
        ));
        row.append('<td><button type="button" class="btn btn-danger btn-xs marble-remove-crop">&times;</button></td>');
        $('#crop-new-row').before(row);

        $('#new-crop-name').val('');
        $('#new-crop-label').val('');
        $('#new-crop-width').val('');
        $('#new-crop-height').val('');
    });

    $(document).on('click', '.marble-remove-crop', function () {
        $(this).closest('tr').remove();
        reindexCrops();
    });
})();
</script>
@endsection
