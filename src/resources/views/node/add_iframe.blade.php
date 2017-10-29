@extends('admin::layouts.iframe')

@section('javascript-head')
    <script type="text/javascript" src="{{ URL::asset('assets/admin/js/attributes/attributes.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('vendor/marblecore/admin/js/attributes/object-relation-edit.js') }}"></script>
@endsection

@section('javascript')
    <script type="text/javascript" src="{{ URL::asset('assets/admin/js/attributes/attributes-init.js') }}"></script>
@endsection

@section('content')
    <div class="main-box">
        <div class="main-box-body clearfix">
            
            <form action="{{url("admin/node/create")}}" method="post">
                <br />
                {!! csrf_field() !!}

                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" value="" class="form-control"/>
                </div>
                

                <div class="form-group">
                    <label>Parent node</label>
                    
                    <div class="attribute-container" id="object-relation-parent-node">
                        <div class="attribute-object-relation-view"></div>
                        <div class="clearfix"></div>
                        
                        <input type="hidden" name="parentId" class="attribute-object-relation-input" value="" />
                        <a href="javascript:;" class="btn btn-default btn-xs attribute-object-relation-add">{{trans("admin::admin.select_object")}}</a>

                    </div>
                    <script>
                        Attributes.ready(function(){
                            var objectRelation = new Attributes.ObjectRelation("object-relation-parent-node");
                            
                            objectRelation.selected(function(node){
                                $.get("/admin/node/" + node.id + "/allowedchildclasses.json", function(response){
                                    
                                    var $filter = $("[data-class-filter]");
                                    
                                    $filter.removeAttr("disabled");
                                    
                                    if( response.classes[0] === "all" ){
                                        return;
                                    }
                                    $filter.attr("disabled", "disabled");
                                    
                                    for( var key in response.classes ){
                                        $("[data-class-filter=" + response.classes[key] + "]").removeAttr("disabled");
                                    }
                                    
                                });
                            });
                            
                            $("#class-dropdown").val([]);
                        });
                    </script>
                </div>
                
                <div class="form-group">
                    <label>Klasse</label>
                    <select name="classId" class="form-control" id="class-dropdown" required>
                        @foreach($groupedNodeClasses as $nodeClasses)
                            <option disabled="disabled">{{$nodeClasses->group->name}}</option>
                            @foreach($nodeClasses->items as $nodeClass)
                                @if( \Marble\Admin\App\Helpers\PermissionHelper::allowedClass($nodeClass->id) )
                                    <option data-class-filter="{{$nodeClass->id}}" value="{{$nodeClass->id}}">&nbsp; &nbsp; &nbsp; {{$nodeClass->name}}</option>
                                @endif
                            @endforeach
                        @endforeach
                    </select>
                </div>
        

                <div class="form-group">
                    <input type="submit" class="btn btn-success" value="{{trans("admin::admin.save")}}" />
                </div>
            </form>
        </div>
    </div>
@endsection