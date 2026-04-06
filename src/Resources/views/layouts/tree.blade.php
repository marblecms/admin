@php
$prefix = config('marble.route_prefix', 'admin');
if (!isset($iconMinus)) {
    $iconMinus = $adminTheme === '98'
        ? asset('vendor/marble/assets/images/win98icons/minus.png')
        : asset('vendor/marble/assets/images/elbow-minus-nl.gif');
    $iconPlus = $adminTheme === '98'
        ? asset('vendor/marble/assets/images/win98icons/plus.png')
        : asset('vendor/marble/assets/images/elbow-plus-nl.gif');
    $iconSize = $adminTheme === '98' ? 10 : 20;
}
@endphp

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
                         src="{{ $iconMinus }}"
                         data-minus="{{ $iconMinus }}"
                         data-plus="{{ $iconPlus }}"
                         width="{{ $iconSize }}" height="{{ $iconSize }}" alt="">
                @else
                    <img src="{{ $iconPlus }}" width="{{ $iconSize }}" height="{{ $iconSize }}" alt="" class="marble-invisible">
                @endif
                @if($node->blueprint)
                    @include('marble::components.famicon', ['name' => $node->blueprint->effectiveIcon()])
                @endif
                <span>{{ $node->name() }}</span>
                @if(!empty($node->_is_mount))
                    <span title="{{ trans('marble::admin.mount_point') }}" class="marble-mount-badge">🔗</span>
                @endif
            </a>

            @include('marble::layouts.tree', [
                'nodes'        => $node->tree_children,
                'isRoot'       => false,
                'isModal'      => $isModal,
                'selectedItem' => $selectedItem ?? null,
                'iconMinus'    => $iconMinus,
                'iconPlus'     => $iconPlus,
                'iconSize'     => $iconSize,
            ])
        </li>
    @endforeach

</ul>
