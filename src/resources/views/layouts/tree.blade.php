
@if( $isRoot )
    <ul class="nav nav-pills nav-stacked sidebar-tree">
@else 
    <ul class="submenu" style="display:block">
@endif

    @foreach($nodes as $node)
        <li class="open {{ $node->id == $selectedNode ? "active" : ""}}">
            @if( $isModal )
                <a href="javascript:;" class="{{ count($node->children) ? "dropdown-toggle" : "" }} object-browser-node" data-node-id="{{$node->id}}" data-node-name="{{$node->name}}">
            @else
                <a href="{{url("/admin/node/edit/". $node->id)}}" class="{{ count($node->children) ? "dropdown-toggle" : "" }}">
            @endif
                <i class="fa fa-{{$node->class->icon}}" ></i>
                <span>{{$node->name}}</span>
            </a>

            @include("admin::layouts.tree", array("nodes" => $node->children, "isRoot" => false, "isModal" => $isModal))
        </li>
    @endforeach

</ul>