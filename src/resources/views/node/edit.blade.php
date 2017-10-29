@extends( $isIframe ? 'admin::layouts.iframe' : 'admin::layouts.app')

@section('javascript-head')
    <script type="text/javascript" src="{{ URL::asset('assets/admin/js/attributes/attributes.js') }}"></script>
@endsection



@section('javascript')
    <script type="text/javascript" src="{{ URL::asset('assets/admin/js/language-switch.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('assets/admin/js/attributes/attributes-init.js') }}"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDMKB3SFb6lbkWJ_XVE77XexLozs3IEI4E&callback=initMap&libraries=places" async defer></script>
    
    
    @if( $isIframe )
        <script>
            window.parent.ObjectBrowser.setNode({
                id: {{$node->id}},
                name: "{{$node->name}}"
            });
        </script>
    @endif
@endsection

@section('sidebar')
    <div class="main-box clearfix profile-box-menu">
        <div class="main-box-body clearfix">
            <div class="profile-box-header green-bg clearfix" style="padding:0 15px 15px">
                <h2>{{$node->name}}</h2>
                <div class="job-position">
                    {{$node->class->name}}
                </div>
            </div>
            <div class="profile-box-content clearfix">
                <ul class="menu-items">
                    @if($node->parentId != 0)
                        <li>
                            <a href="{{url("admin/node/delete/" . $node->id) }}" onclick="return confirm('{{trans("admin.are_you_sure")}}');" class="clearfix">
                                <i class="fa fa-trash-o fa-lg"></i> {{trans("admin.delete_node")}}
                            </a>
                        </li>
                    @endif
                    @if($node->class->allowChildren)
                        <li>
                            <a href="{{url("admin/node/add/" . $node->id)}}" class="clearfix">
                                <i class="fa fa-plus fa-lg"></i> {{trans("admin.add_children")}}
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>

    <div class="main-box clearfix profile-box-menu">
        <div class="main-box-body clearfix">
            <div class="profile-box-header gray-bg clearfix" style="padding:0 15px 15px">
                <h2>Meta Information</h2>
            </div>
            <div class="profile-box-content clearfix">
                <ul class="menu-items">
                    <li><a href="#"><b>ID:</b> {{$node->id}}</a></li>
                    <li><a href="#"><b>Class ID:</b> {{$node->class->id}}</a></li>
                    <li><a href="#"><b>Parent ID:</b> {{$node->parentId}}</a></li>
                </ul>
            </div>
        </div>
    </div>
@endsection

@section('content')

    <h1>{{$node->name}}</h1>

    @if($isIframe)
        <form action="{{url("admin/node/save/" . $node->id . "/iframe") }}" enctype="multipart/form-data" method="post">
    @else
        <form action="{{url("admin/node/save/" . $node->id) }}" enctype="multipart/form-data" method="post">
    @endif
    
        {!! csrf_field() !!}

        @if( ! $node->class->locked )
            
            @if( $node->class->tabs )
                <ul class="nav nav-tabs" role="tablist">
                
                    @foreach($groupedNodeAttributes as $i => $classAttributeGroup)
                        
                        <li role="presentation" class="{{$classAttributeGroup == reset($groupedNodeAttributes) ? "active" : "" }}"><a href="#tab-node-edit-{{$i}}" aria-controls="home" role="tab" data-toggle="tab">{{ $classAttributeGroup->group ? $classAttributeGroup->group->name : "Bearbeiten" }}</a></li>
                    @endforeach
                </ul>

                <div class="tab-content">
            @endif
                
            @foreach($groupedNodeAttributes as $i => $classAttributeGroup)
                
                @if( $node->class->tabs )
                    <div role="tabpanel" class="tab-pane {{$classAttributeGroup == reset($groupedNodeAttributes) ? "active" : "" }}" id="tab-node-edit-{{$i}}">
                @endif
                
                <div class="main-box">
                    
                    @if( $node->class->tabs )
                        <br />
                    @elseif( $classAttributeGroup->group )
                        <header class="main-box-header clearfix">
                            @if($classAttributeGroup->group)
                                <h2><b>{{$classAttributeGroup->group->name}}</b></h2>
                            @endif
                        </header>
                    @else
                        <br />
                    @endif
                    
                    <div class="main-box-body clearfix">
                        @if($classAttributeGroup->group && $classAttributeGroup->group->template)
                            @include("admin::attributegroups." . $classAttributeGroup->group->template, array("attributes" => $classAttributeGroup->items, "group" => $classAttributeGroup->group))
                        @else
                            @foreach($classAttributeGroup->items as $namedIdentifier => $attribute)
                                @continue($attribute->classAttribute->locked)
                                @include("admin::node.edit_attribute", array("attribute" => $attribute))
                            @endforeach
                        @endif
                    </div>
                </div>
                
                @if( $node->class->tabs )
                    </div>
                @endif

            @endforeach

            @if( $node->class->tabs )
                </div>
            @endif


            <div class="form-group pull-right">
                <a href="{{url("/admin/dashboard")}}" class="btn btn-primary">{{trans("admin.cancel")}}</a>
                <input type="submit" class="btn btn-success" value="{{trans("admin.save")}}" />
            </div>
            <div class="clearfix"></div>
            <br /><br /><br />
        @endif
    </form>

    @if($node->class->listChildren)
         @include("admin::node.children", array("node" => $node))
    @endif

@endsection