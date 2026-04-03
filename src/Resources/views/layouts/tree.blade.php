@php $prefix = config('marble.route_prefix', 'admin'); @endphp

@if($isRoot)
    <ul class="nav nav-pills nav-stacked sidebar-tree">
@else
    <ul class="submenu" style="display:block">
@endif

    @foreach($nodes as $node)
        @php $hasChildren = count($node->tree_children) > 0; @endphp
        <li class="open {{ ($selectedItem ?? null) == $node->id ? 'active' : '' }}" data-node-id="{{ $node->id }}">
            @if($isModal)
                <a href="javascript:;" class="{{ $hasChildren ? 'dropdown-toggle' : '' }} object-browser-node" data-node-id="{{ $node->id }}" data-node-name="{{ $node->name() }}">
            @else
                <a href="{{ url("{$prefix}/item/edit/{$node->id}") }}" class="{{ $hasChildren ? 'dropdown-toggle' : '' }}">
            @endif
                @if($hasChildren)
                    <img class="tree-expand-icon"
                         src="{{ asset('vendor/marble/assets/images/elbow-minus-nl.gif') }}"
                         data-minus="{{ asset('vendor/marble/assets/images/elbow-minus-nl.gif') }}"
                         data-plus="{{ asset('vendor/marble/assets/images/elbow-plus-nl.gif') }}"
                         width="20" height="20" alt="">
                @else
                    <img src="{{ asset('vendor/marble/assets/images/elbow-plus-nl.gif') }}" width="20" height="20" alt="" class="marble-invisible">
                @endif
                @if($node->blueprint)
                    <img src="{{ asset('vendor/marble/assets/images/famicons/' . $node->blueprint->effectiveIcon() . '.svg') }}" width="16" height="16" alt="" class="marble-vmid marble-mr-xs">
                @endif
                <span>{{ $node->name() }}</span>
                @if(!empty($node->_is_mount))
                    <span title="{{ trans('marble::admin.mount_point') }}" class="marble-mount-badge">🔗</span>
                @endif
            </a>

            @include('marble::layouts.tree', ['nodes' => $node->tree_children, 'isRoot' => false, 'isModal' => $isModal, 'selectedItem' => $selectedItem ?? null])
        </li>
    @endforeach

</ul>
