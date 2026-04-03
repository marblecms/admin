@php
    $slug = $item->rawValue('slug');
    $frontendUrl = rtrim(config('marble.frontend_url', ''), '/');
    $reachableRoutes = [];

    foreach ($languages as $lang) {
        $langSlug = $item->slug($lang->id);
        if ($langSlug) $reachableRoutes[] = ['type' => 'slug', 'lang' => strtoupper($lang->code), 'path' => $langSlug];
    }

    foreach ($mountPoints as $mount) {
        foreach ($languages as $lang) {
            $mountSlug = $item->slug($lang->id, $mount->mount_parent_id);
            if ($mountSlug) {
                $reachableRoutes[] = ['type' => 'mount', 'lang' => strtoupper($lang->code), 'path' => $mountSlug];
            }
        }
    }

    foreach ($aliases as $alias) {
        $reachableRoutes[] = ['type' => 'alias', 'lang' => strtoupper($alias->language->code ?? ''), 'path' => '/' . ltrim($alias->alias, '/')];
    }

    foreach ($inboundRedirects as $redirect) {
        $reachableRoutes[] = ['type' => 'redirect', 'lang' => null, 'path' => '/' . ltrim($redirect->source_path, '/'), 'code' => $redirect->status_code];
    }
@endphp

{{-- Meta --}}
<div class="main-box clearfix profile-box-menu">
    <div class="main-box-body clearfix">
        <div class="profile-box-header gray-bg clearfix">
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

{{-- Reachable via --}}
@if(count($reachableRoutes))
<div class="main-box clearfix profile-box-menu">
    <div class="main-box-body clearfix">
        <div class="profile-box-header gray-bg clearfix">
            <h2>{{ trans('marble::admin.reachable_via') }}</h2>
        </div>
        <div class="profile-box-content clearfix marble-box-body">
            @foreach($reachableRoutes as $route)
            <div class="marble-route-row">
                @if($route['type'] === 'slug')
                    <span class="marble-route-label marble-meta">{{ trans('marble::admin.slug') }} {{ $route['lang'] }}</span>
                @elseif($route['type'] === 'mount')
                    <span class="marble-route-label text-muted">{{ trans('marble::admin.mount') }} {{ $route['lang'] }}</span>
                @elseif($route['type'] === 'alias')
                    <span class="marble-route-label marble-meta">{{ trans('marble::admin.alias') }} {{ $route['lang'] }}</span>
                @else
                    <span class="marble-route-label marble-meta">{{ trans('marble::admin.redirect') }} {{ $route['code'] }}</span>
                @endif
                <a href="{{ $frontendUrl . $route['path'] }}" target="_blank" class="marble-break-all">{{ $route['path'] }}</a>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif
