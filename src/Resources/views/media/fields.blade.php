@extends('marble::layouts.app')

@section('content')
    <h1>
        @include('marble::components.famicon', ['name' => 'pictures'])
        {{ $media->original_filename }}
        <small class="text-muted">— {{ $media->blueprint->name }}</small>
    </h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        <div class="col-md-3">
            <div class="main-box">
                <div class="main-box-body clearfix">
                    @if($media->isImage())
                        <img src="{{ url('/image/300/200/' . $media->filename) }}" class="img-responsive marble-mb-sm" style="border-radius:3px" />
                    @endif
                    <table class="table table-condensed marble-mb-0">
                        <tr><td class="text-muted">{{ trans('marble::admin.filename') }}</td><td>{{ $media->original_filename }}</td></tr>
                        <tr><td class="text-muted">MIME</td><td>{{ $media->mime_type }}</td></tr>
                        <tr><td class="text-muted">{{ trans('marble::admin.size') }}</td><td>{{ number_format($media->size / 1024, 0) }} KB</td></tr>
                        @if($media->width)
                            <tr><td class="text-muted">{{ trans('marble::admin.dimensions') }}</td><td>{{ $media->width }} × {{ $media->height }}</td></tr>
                        @endif
                    </table>
                    <a href="{{ route('marble.media.index') }}" class="btn btn-default btn-sm btn-block marble-mt-sm">
                        @include('marble::components.famicon', ['name' => 'arrow_left']) {{ trans('marble::admin.back') }}
                    </a>

                    @if($media->isImage())
                        @php $cropPresets = \Marble\Admin\Models\CropPreset::orderBy('name')->get(); @endphp
                        @if($cropPresets->isNotEmpty())
                            <hr class="marble-mt-sm marble-mb-sm">
                            <p class="marble-text-sm text-muted marble-mb-xs"><strong>{{ trans('marble::admin.crop_preview') }}</strong></p>
                            @foreach($cropPresets as $preset)
                                @php $cropUrl = $media->crop($preset->name); @endphp
                                <div class="marble-crop-preview-item">
                                    <div class="marble-crop-preview-thumb">
                                        <img src="{{ $cropUrl }}" alt="{{ $preset->label }}" loading="lazy" />
                                    </div>
                                    <div class="marble-crop-preview-label">
                                        {{ $preset->label }}
                                        <small class="marble-meta">{{ $preset->width }}×{{ $preset->height }}</small>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <form id="marble-edit-form" action="{{ route('marble.media.fields.save', $media) }}" enctype="multipart/form-data" method="post">
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
                                @php
                                    $raw = \Marble\Admin\Models\MediaValue::where('media_id', $media->id)
                                        ->where('blueprint_field_id', $field->id)
                                        ->get()
                                        ->pluck('value', 'language_id');
                                    $ft = $field->fieldTypeInstance();
                                @endphp
                                <div class="form-group" data-field-identifier="{{ $field->identifier }}">
                                    <label>{{ $field->name }}</label>

                                    @if($field->translatable)
                                        <div class="lang-container">
                                            <div class="lang-switch-container">
                                                @foreach($languages as $language)
                                                    <div class="lang-switch {{ $loop->first ? 'active' : '' }}" data-lang="{{ $language->id }}">{{ $language->name }}</div>
                                                @endforeach
                                            </div>
                                            @foreach($languages as $language)
                                                @php
                                                    $rawVal = $raw->get($language->id);
                                                    $value  = $ft->isStructured() ? json_decode($rawVal, true) : $rawVal;
                                                @endphp
                                                <div class="lang-content {{ $loop->first ? 'active' : '' }}" data-lang="{{ $language->id }}">
                                                    @include('marble::field-types.' . $ft->identifier(), [
                                                        'field'      => $field,
                                                        'item'       => null,
                                                        'languageId' => $language->id,
                                                        'value'      => $value,
                                                        'fieldType'  => $ft,
                                                    ])
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        @php
                                            $primaryLangId = \Marble\Admin\Facades\Marble::primaryLanguageId();
                                            $rawVal = $raw->get($primaryLangId);
                                            $value  = $ft->isStructured() ? json_decode($rawVal, true) : $rawVal;
                                        @endphp
                                        @include('marble::field-types.' . $ft->identifier(), [
                                            'field'      => $field,
                                            'item'       => null,
                                            'languageId' => $primaryLangId,
                                            'value'      => $value,
                                            'fieldType'  => $ft,
                                        ])
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <div class="form-group pull-right">
                    <button type="submit" class="btn btn-success marble-save-btn">
                        @include('marble::components.famicon', ['name' => 'disk']) {{ trans('marble::admin.save') }}
                    </button>
                </div>
                <div class="clearfix"></div>
                <br /><br />
            </form>
        </div>
    </div>
@endsection
