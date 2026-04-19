@extends('marble::layouts.app')

@section('content_class', 'col-lg-12')

@section('content')

<h1>
    @include('marble::components.famicon', ['name' => 'plugin']) {{ trans('marble::admin.plugins') }}
    <small class="text-muted marble-text-sm" style="font-weight:normal;margin-left:8px">{{ trans('marble::admin.plugins_hint') }}</small>
</h1>

{{-- Search --}}
<div class="main-box">
    <div class="main-box-body clearfix">
        <form method="GET" action="{{ route('marble.plugin.index') }}" class="marble-flex-wrap">
            <div class="form-group" style="flex:1;min-width:200px">
                <input type="text" name="q" class="form-control"
                       placeholder="{{ trans('marble::admin.plugins_search_placeholder') }}"
                       value="{{ $query }}" autofocus>
            </div>
            <button type="submit" class="btn btn-default marble-ml-sm" style="margin-top:1px">
                @include('marble::components.famicon', ['name' => 'magnifier']) {{ trans('marble::admin.search') }}
            </button>
        </form>
    </div>
</div>

@if($error)
    <div class="alert alert-danger">{{ $error }}</div>
@endif

@if(!empty($packages))

<p class="text-muted marble-text-sm marble-mb-sm">
    {{ number_format($total) }} {{ trans('marble::admin.plugins_found') }}
    @if($query) {{ trans('marble::admin.plugins_for') }} "<strong>{{ $query }}</strong>" @endif
</p>

@foreach($packages as $pkg)
@php
    $reg      = $pkg['registry'] ?? null;
    $verified = $reg['verified'] ?? false;
    $featured = $reg['featured'] ?? false;
    $pkgName  = $pkg['name'];
    $parts    = explode('/', $pkgName);
    $tags     = $reg['tags'] ?? ($pkg['keywords'] ?? []);
    $version  = array_key_first($pkg['versions'] ?? []);
@endphp
<div class="main-box {{ $featured ? 'marble-plugin-featured-box' : '' }}">
    <header class="main-box-header clearfix">
        <h2 class="pull-left" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
            @include('marble::components.famicon', ['name' => 'plugin'])
            <a href="{{ route('marble.plugin.show', ['vendor' => $parts[0], 'package' => $parts[1]]) }}">{{ $pkgName }}</a>
            @if($verified)
                <span class="marble-plugin-badge marble-plugin-badge-verified">
                    @include('marble::components.famicon', ['name' => 'tick']) {{ trans('marble::admin.plugin_verified') }}
                </span>
            @endif
            @if($featured)
                <span class="marble-plugin-badge marble-plugin-badge-featured">
                    @include('marble::components.famicon', ['name' => 'star']) {{ trans('marble::admin.plugin_featured') }}
                </span>
            @endif
        </h2>
        <div class="pull-right" style="display:flex;align-items:center;gap:8px;padding-top:4px">
            @if($version)
                <span class="label label-default">{{ ltrim($version, 'v') }}</span>
            @endif
            <span class="text-muted marble-text-sm">
                @include('marble::components.famicon', ['name' => 'arrow_down']) {{ number_format($pkg['downloads'] ?? 0) }}
            </span>
            <a href="{{ route('marble.plugin.show', ['vendor' => $parts[0], 'package' => $parts[1]]) }}"
               class="btn btn-info btn-xs">
                {{ trans('marble::admin.details') }} →
            </a>
        </div>
        <div class="clearfix"></div>
    </header>
    <div class="main-box-body clearfix">
        <p class="marble-mb-0">{{ $pkg['description'] ?? '' }}</p>
        @if(!empty($tags))
            <div class="marble-plugin-tags marble-mt-xs">
                @foreach((array) $tags as $tag)
                    <span class="marble-plugin-tag">{{ $tag }}</span>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endforeach

{{-- Pagination --}}
@if($total > 15)
<div class="text-center marble-mt-sm marble-mb-sm">
    @if($page > 1)
        <a href="{{ route('marble.plugin.index', ['q' => $query, 'page' => $page - 1]) }}"
           class="btn btn-default btn-sm">← {{ trans('marble::admin.previous') }}</a>
    @endif
    <span class="text-muted marble-text-sm marble-ml-sm marble-mr-sm">{{ trans('marble::admin.page') }} {{ $page }}</span>
    @if($total > $page * 15)
        <a href="{{ route('marble.plugin.index', ['q' => $query, 'page' => $page + 1]) }}"
           class="btn btn-default btn-sm">{{ trans('marble::admin.next') }} →</a>
    @endif
</div>
@endif

@elseif(!$error)
<div class="main-box">
    <div class="main-box-body clearfix" style="padding:40px;text-align:center">
        @include('marble::components.famicon', ['name' => 'plugin'])
        <p class="text-muted" style="margin-top:12px">
            @if($query)
                {{ trans('marble::admin.plugins_no_results') }} "<strong>{{ $query }}</strong>"
            @else
                {{ trans('marble::admin.plugins_empty') }}
            @endif
        </p>
    </div>
</div>
@endif

@endsection
