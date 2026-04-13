@extends('marble::layouts.app')

@section('javascript-head')
    <script type="text/javascript" src="{{ asset('vendor/marble/assets/js/attributes/attributes.js') }}"></script>
@endsection

@section('sidebar')
    @if(session('success'))
        <div class="alert alert-success marble-mb-sm">{{ session('success') }}</div>
    @endif

    <div class="main-box clearfix profile-box-menu">
        <div class="main-box-body clearfix">
            <div class="profile-box-header gray-bg clearfix">
                <h2>{{ trans('marble::admin.ab_variant_b') }}</h2>
                <div class="job-position marble-pl-lg">{{ $item->name() }}</div>
            </div>
            <div class="profile-box-content clearfix">
                <ul class="menu-items">
                    <li class="menu-item-action">
                        <span class="text-muted marble-text-sm">{{ trans('marble::admin.ab_traffic_split') }}</span>
                        <strong>{{ $variant->traffic_split }}%</strong>
                    </li>
                    <li class="menu-item-action">
                        <span class="text-muted marble-text-sm">{{ trans('marble::admin.ab_results') }}</span>
                        <span class="marble-text-sm">{{ $variant->winnerLabel() }}</span>
                    </li>
                    <li>
                        <a href="{{ route('marble.item.edit', $item) }}" class="clearfix">
                            @include('marble::components.famicon', ['name' => 'arrow_left']) {{ trans('marble::admin.back') }}
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
@endsection

@section('content')
<h1>
    {{ trans('marble::admin.ab_variant_b') }}
    <small class="marble-meta marble-fw-normal">{{ $item->name() }}</small>
</h1>

<div class="alert alert-info marble-text-sm">
    @include('marble::components.famicon', ['name' => 'information'])
    {{ trans('marble::admin.ab_variant_hint') }}
</div>

<form id="marble-edit-form" action="{{ route('marble.item.variant.save', [$item, $variant]) }}"
      enctype="multipart/form-data" method="POST">
    @csrf

    <div class="main-box">
        <header class="main-box-header clearfix">
            <h2>{{ trans('marble::admin.ab_variant_b') }} — {{ $variant->name }}</h2>
        </header>
        <div class="main-box-body clearfix">
            <div class="form-group">
                <label>{{ trans('marble::admin.name') }}</label>
                <input type="text" name="name" class="form-control marble-input-md-w" value="{{ $variant->name }}" />
            </div>
            <div class="form-group">
                <label>{{ trans('marble::admin.ab_traffic_split') }}</label>
                <div style="display:flex;align-items:center;gap:8px;">
                    <input type="range" name="traffic_split" min="1" max="99" value="{{ $variant->traffic_split }}"
                           id="ab-split-range" style="width:200px;" />
                    <span id="ab-split-val" class="marble-text-sm"><strong>{{ $variant->traffic_split }}%</strong> {{ trans('marble::admin.ab_variant_b') }}</span>
                </div>
            </div>
        </div>
    </div>

    @foreach($groupedFields as $group)
        <div class="main-box">
            @if($group['group'])
                <header class="main-box-header clearfix">
                    <h2><b>{{ $group['group']->name }}</b></h2>
                </header>
            @endif
            <div class="main-box-body clearfix">
                @foreach($group['fields'] as $field)
                    @continue($field->locked)
                    @php
                        $fieldType = $field->fieldTypeInstance();
                        $primaryLanguageId = \Marble\Admin\Facades\Marble::primaryLanguageId();
                        $variantVal = fn($langId) => \Marble\Admin\Models\ItemVariantValue::where('variant_id', $variant->id)
                            ->where('blueprint_field_id', $field->id)
                            ->where('language_id', $langId)
                            ->value('value');
                        $baseVal = fn($langId) => $item->rawValue($field->identifier, $langId);
                        $previewStr = function($v) {
                            if (is_array($v))   return '[' . count($v) . ' items]';
                            if (is_bool($v))    return $v ? 'true' : 'false';
                            if ($v === null)    return '';
                            return mb_strimwidth(strip_tags((string)$v), 0, 80, '…');
                        };
                    @endphp
                    <div class="form-group marble-ab-field-group" data-field-identifier="{{ $field->identifier }}">
                        <label>
                            {{ $field->name }}
                            @if($field->translatable)
                                <small class="text-muted marble-text-sm">({{ trans('marble::admin.translatable') ?? 'translatable' }})</small>
                            @endif
                        </label>

                        @if($field->translatable)
                            <div class="lang-container">
                                <div class="lang-switch-container">
                                    @foreach($languages as $language)
                                        <div class="lang-switch {{ $language->id == $primaryLanguageId ? 'active' : '' }}" data-lang="{{ $language->id }}">{{ $language->name }}</div>
                                    @endforeach
                                </div>
                                @foreach($languages as $language)
                                    @php $vv = $variantVal($language->id); $bv = $baseVal($language->id); @endphp
                                    <div class="lang-content {{ $language->id == $primaryLanguageId ? 'active' : '' }}" data-lang="{{ $language->id }}">
                                        <div class="marble-ab-base-preview text-muted marble-text-sm marble-mb-xs">
                                            <em>A: {{ $previewStr($bv) ?: '—' }}</em>
                                        </div>
                                        @include('marble::field-types.' . $fieldType->identifier(), [
                                            'field'      => $field,
                                            'item'       => $item,
                                            'languageId' => $language->id,
                                            'value'      => $vv ?? $bv,
                                            'fieldType'  => $fieldType,
                                        ])
                                    </div>
                                @endforeach
                            </div>
                        @else
                            @php $vv = $variantVal($primaryLanguageId); $bv = $baseVal($primaryLanguageId); @endphp
                            <div class="marble-ab-base-preview text-muted marble-text-sm marble-mb-xs">
                                <em>A: {{ $previewStr($bv) ?: '—' }}</em>
                            </div>
                            @include('marble::field-types.' . $fieldType->identifier(), [
                                'field'      => $field,
                                'item'       => $item,
                                'languageId' => $primaryLanguageId,
                                'value'      => $vv ?? $bv,
                                'fieldType'  => $fieldType,
                            ])
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    <div class="form-group pull-right">
        <a href="{{ route('marble.item.edit', $item) }}" class="btn btn-default">
            {{ trans('marble::admin.cancel') }}
        </a>
        <button type="submit" class="btn btn-success">
            @include('marble::components.famicon', ['name' => 'disk']) {{ trans('marble::admin.save') }}
        </button>
    </div>
    <div class="clearfix"></div>
</form>
@endsection

@section('javascript')
<script type="text/javascript" src="{{ asset('vendor/marble/assets/js/language-switch.js') }}"></script>
<script>
var splitRange = document.getElementById('ab-split-range');
var splitVal   = document.getElementById('ab-split-val');
if (splitRange) {
    splitRange.addEventListener('input', function() {
        splitVal.innerHTML = '<strong>' + this.value + '%</strong> {{ trans('marble::admin.ab_variant_b') }}';
    });
}
</script>
@endsection
