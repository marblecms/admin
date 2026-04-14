@php $slug = $item->rawValue('slug'); @endphp

<div class="main-box clearfix profile-box-menu">
    <div class="main-box-body clearfix">
        <div class="profile-box-header {{ $item->status === 'published' ? 'green-bg' : 'gray-bg' }} clearfix marble-flex-center">
            <div>
                <h2>{{ $item->name() }}</h2>
                <div class="job-position marble-pl-lg">{{ $item->blueprint->name }}</div>
            </div>
            @if($slug)
                @php $frontendUrl = config('marble.frontend_url', ''); @endphp
                @if($item->status === 'published')
                    <a href="{{ $frontendUrl . $item->slug() }}" target="_blank" class="btn btn-xs btn-default marble-ml-auto">
                        @include('marble::components.famicon', ['name' => 'monitor']) {{ trans('marble::admin.preview') }}
                    </a>
                @else
                    <span class="btn btn-xs btn-default marble-ml-auto disabled"
                          title="{{ trans('marble::admin.preview_not_published_hint') }}">
                        @include('marble::components.famicon', ['name' => 'monitor']) {{ trans('marble::admin.preview') }}
                    </span>
                @endif
            @endif
        </div>
        <div class="profile-box-content clearfix">
            <ul class="menu-items">

                {{-- Toggle status --}}
                <li class="menu-item-action">
                    <span>
                        @if($item->status === 'published')
                            @include('marble::components.famicon', ['name' => 'tick'])
                            {{ trans('marble::admin.published') }}
                        @else
                            @include('marble::components.famicon', ['name' => 'pencil'])
                            <span class="marble-meta marble-fw-bold">{{ trans('marble::admin.draft') }}</span>
                        @endif
                    </span>
                    @if(!$item->blueprint?->workflow_id)
                    <form method="POST" action="{{ route('marble.item.toggle-status', $item) }}" class="marble-inline-form">
                        @csrf
                        <button type="submit" class="btn btn-xs btn-info">
                            {{ $item->status === 'published' ? trans('marble::admin.set_draft') : trans('marble::admin.set_published') }}
                        </button>
                    </form>
                    @endif
                </li>

                {{-- Show in Nav toggle --}}
                @if($item->blueprint->show_in_tree)
                    <li class="menu-item-action">
                        <span>
                            @include('marble::components.famicon', ['name' => 'application_side_tree'])
                            @if($item->show_in_nav)
                                {{ trans('marble::admin.show_in_navigation') }}
                            @else
                                <span class="marble-meta marble-fw-bold">{{ trans('marble::admin.hidden_in_navigation') }}</span>
                            @endif
                        </span>
                        <form method="POST" action="{{ route('marble.item.toggle-nav', $item) }}" class="marble-inline-form">
                            @csrf
                            <button type="submit" class="btn btn-xs btn-info">
                                {{ $item->show_in_nav ? trans('marble::admin.hide_in_nav') : trans('marble::admin.show_in_nav') }}
                            </button>
                        </form>
                    </li>
                @endif

                {{-- Add child --}}
                @if($item->blueprint->allow_children)
                    <li>
                        <a href="{{ route('marble.item.add', $item) }}" class="clearfix">
                            @include('marble::components.famicon', ['name' => 'add']) {{ trans('marble::admin.add_children') }}
                        </a>
                    </li>
                @endif

                {{-- Duplicate --}}
                <li>
                    <form method="POST" action="{{ route('marble.item.duplicate', $item) }}">
                        @csrf
                        <button type="submit">
                            @include('marble::components.famicon', ['name' => 'page_white_copy']) {{ trans('marble::admin.duplicate') }}
                        </button>
                    </form>
                </li>

                {{-- Move --}}
                @if($item->parent_id)
                    <li>
                        <a href="{{ route('marble.item.move-form', $item) }}" class="clearfix">
                            @include('marble::components.famicon', ['name' => 'application_side_expand']) {{ trans('marble::admin.move') }}
                        </a>
                    </li>
                @endif

                {{-- Export --}}
                <li>
                    <a href="{{ route('marble.item.export', $item) }}" class="clearfix">
                        @include('marble::components.famicon', ['name' => 'page_white_paste']) {{ trans('marble::admin.export') }}
                    </a>
                </li>

                {{-- Watch / Unwatch --}}
                <li>
                    <form method="POST" action="{{ route('marble.item.subscribe', $item) }}" class="marble-inline-form">
                        @csrf
                        <button type="submit" class="{{ $isWatching ? 'marble-watching' : '' }}">
                            @include('marble::components.famicon', ['name' => $isWatching ? 'bell' : 'bell'])
                            {{ $isWatching ? trans('marble::admin.subscription_unwatch') : trans('marble::admin.subscription_watch') }}
                        </button>
                    </form>
                </li>

                {{-- Relations graph --}}
                <li>
                    <a href="{{ route('marble.item.graph', $item) }}" class="clearfix">
                        @include('marble::components.famicon', ['name' => 'chart_bar']) {{ trans('marble::admin.relations_graph') }}
                    </a>
                </li>

                {{-- Traffic --}}
                @if(config('marble.traffic_tracking', false))
                <li>
                    <a href="{{ route('marble.item.traffic', $item) }}" class="clearfix">
                        @include('marble::components.famicon', ['name' => 'chart_bar']) {{ trans('marble::admin.traffic') }}
                    </a>
                </li>
                @endif

                {{-- Delete --}}
                @if($item->parent_id)
                    <li>
                        <form method="POST" action="{{ route('marble.item.delete', $item) }}" onsubmit="return confirm('{{ trans('marble::admin.are_you_sure') }}'); formDirty=false;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="danger">
                                @include('marble::components.famicon', ['name' => 'bin']) {{ trans('marble::admin.delete_node') }}
                            </button>
                        </form>
                    </li>
                @endif

            </ul>
        </div>
    </div>
</div>
