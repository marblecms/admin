@extends('marble::layouts.app')

@section('content')

<div class="clearfix marble-mb-sm">
    <a href="{{ route('marble.plugin.index') }}" class="btn btn-default btn-sm">
        ← {{ trans('marble::admin.plugins') }}
    </a>
</div>

@if($error)
    <div class="alert alert-danger">{{ $error }}</div>
@elseif($data)

@php
    $versions    = $data['versions'] ?? [];
    $latest      = collect($versions)->first();
    $latestKey   = array_key_first($versions);
    $reg         = $entry;
    $verified    = $reg['verified'] ?? false;
    $featured    = $reg['featured'] ?? false;
    $screenshots = $reg['screenshots'] ?? [];
    $tags        = $reg['tags'] ?? ($latest['keywords'] ?? []);
    $require     = $latest['require'] ?? [];
    $readme      = $reg['readme_url'] ?? null;
@endphp

<div class="main-box">
    <header class="main-box-header clearfix">
        <h2 style="display:flex;align-items:center;gap:10px">
            @include('marble::components.famicon', ['name' => 'plugin'])
            {{ $name }}
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
    </header>
    <div class="main-box-body clearfix">

        <p style="font-size:14px;color:#444;margin-bottom:16px">{{ $data['description'] ?? '' }}</p>

        @if(!empty($tags))
            <div class="marble-plugin-tags marble-mb-sm">
                @foreach((array) $tags as $tag)
                    <span class="marble-plugin-tag">{{ $tag }}</span>
                @endforeach
            </div>
        @endif

        {{-- Screenshots from registry --}}
        @if(!empty($screenshots))
            <div class="marble-plugin-screenshots marble-mb-sm">
                @foreach($screenshots as $shot)
                    <a href="{{ $shot }}" target="_blank" rel="noopener">
                        <img src="{{ $shot }}" alt="" class="marble-plugin-screenshot-thumb">
                    </a>
                @endforeach
            </div>
        @endif

        <table class="table marble-table-flush marble-plugin-detail-table">
            <tbody>
                <tr>
                    <td class="marble-config-label"><strong>{{ trans('marble::admin.plugin_latest_version') }}</strong></td>
                    <td><code>{{ $latestKey }}</code></td>
                </tr>
                <tr>
                    <td class="marble-config-label"><strong>{{ trans('marble::admin.plugin_downloads') }}</strong></td>
                    <td>{{ number_format($data['downloads']['total'] ?? 0) }}</td>
                </tr>
                @if(!empty($data['maintainers']))
                <tr>
                    <td class="marble-config-label"><strong>{{ trans('marble::admin.plugin_maintainers') }}</strong></td>
                    <td>{{ collect($data['maintainers'])->pluck('name')->implode(', ') }}</td>
                </tr>
                @endif
                @if(!empty($latest['license']))
                <tr>
                    <td class="marble-config-label"><strong>{{ trans('marble::admin.plugin_license') }}</strong></td>
                    <td>{{ implode(', ', (array) $latest['license']) }}</td>
                </tr>
                @endif
                @if(!empty($latest['homepage']))
                <tr>
                    <td class="marble-config-label"><strong>{{ trans('marble::admin.plugin_homepage') }}</strong></td>
                    <td><a href="{{ $latest['homepage'] }}" target="_blank" rel="noopener">{{ $latest['homepage'] }}</a></td>
                </tr>
                @endif
                <tr>
                    <td class="marble-config-label"><strong>{{ trans('marble::admin.plugin_packagist') }}</strong></td>
                    <td><a href="https://packagist.org/packages/{{ $name }}" target="_blank" rel="noopener">packagist.org/packages/{{ $name }}</a></td>
                </tr>
                @php
                    $marbleReq = $require['marble/admin'] ?? ($require['marble/marble'] ?? null);
                @endphp
                @if($marbleReq)
                <tr>
                    <td class="marble-config-label"><strong>{{ trans('marble::admin.plugin_requires_marble') }}</strong></td>
                    <td><code>{{ $marbleReq }}</code></td>
                </tr>
                @endif
            </tbody>
        </table>

        <div class="marble-plugin-install-box">
            <p class="marble-text-sm text-muted marble-mb-sm">{{ trans('marble::admin.plugin_install_hint') }}</p>
            <div class="marble-plugin-install-cmd" id="marble-install-cmd">composer require {{ $name }}</div>
            <button class="btn btn-default btn-sm marble-mt-xs" onclick="
                navigator.clipboard.writeText('composer require {{ $name }}');
                this.textContent = '✓ Copied';
                var b = this;
                setTimeout(function(){ b.textContent = '{{ trans('marble::admin.copy') }}'; }, 2000);
            ">{{ trans('marble::admin.copy') }}</button>
        </div>

    </div>
</div>

@if(count($versions) > 1)
<div class="main-box">
    <header class="main-box-header clearfix">
        <h2>@include('marble::components.famicon', ['name' => 'tag']) {{ trans('marble::admin.plugin_versions') }}</h2>
    </header>
    <div class="main-box-body clearfix">
        <table class="table marble-table-flush">
            <thead>
                <tr>
                    <th>{{ trans('marble::admin.plugin_version') }}</th>
                    <th>{{ trans('marble::admin.plugin_requires_marble') }}</th>
                    <th>PHP</th>
                </tr>
            </thead>
            <tbody>
                @foreach(array_slice($versions, 0, 10) as $vKey => $v)
                <tr>
                    <td><code>{{ $vKey }}</code></td>
                    <td><code>{{ $v['require']['marble/admin'] ?? $v['require']['marble/marble'] ?? '—' }}</code></td>
                    <td><code>{{ $v['require']['php'] ?? '—' }}</code></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endif

@endsection
