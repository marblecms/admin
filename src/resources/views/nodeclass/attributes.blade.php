@extends('admin::layouts.app')


@section('sidebar')
    <div class="main-box clearfix profile-box-menu">
        <div class="main-box-body clearfix">
            <div class="profile-box-header gray-bg clearfix" style="padding:0 15px 15px">
                <h2>{{trans("admin::admin.attributegroups")}}</h2>
                <div class="job-position">
                    {{$nodeClass->name}}
                </div>
            </div>
            <div class="profile-box-content clearfix">
                <ul class="menu-items" id="class-attribute-groups">
                    @foreach($classAttributeGroups as $classAttributeGroup)
                        <li class="more" data-group-id="{{$classAttributeGroup->id}}">
                            <div class="pull-left">
                                <i class="fa fa-folder-open-o fa-lg"></i> {{$classAttributeGroup->name}}
                            </div>
                            <a style="display:inline-block" href="{{url("admin/nodeclass/attributegroups/delete/" . $nodeClass->id . "/" . $classAttributeGroup->id)}}" class="btn pull-right btn-danger btn-xs">
                                <i class="fa fa-trash-o"></i>
                            </a>
                            <a style="display:inline-block" data-modal-id="edit-attribute-group-modal" data-attribute-group-id="{{$classAttributeGroup->id}}" class="btn pull-right btn-info btn-xs edit-attribute-group-modal">
                                <i class="fa fa-pencil"></i>
                            </a>
                            <div class="clearfix"></div>
                        </li>
                    @endforeach
                </ul>
                <ul class="menu-items">
                    <li>
                        <a data-modal-id="add-attribute-group-modal" href="javascript:$('#add-attribute-group-modal').modal('show')" class="clearfix" class="add-attribute-group">
                            <i class="fa fa-plus fa-lg"></i> {{trans("admin::admin.add_group")}}
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <script>
        
        var attributeGroups = {};
            
        @foreach($classAttributeGroups as $classAttributeGroup)
            attributeGroups[{{$classAttributeGroup->id}}] = {
                id: {{$classAttributeGroup->id}},
                name: "{{$classAttributeGroup->name}}",
                template: "{{$classAttributeGroup->template}}"
            };
        @endforeach
        
        $( "#class-attribute-groups" ).sortable({
            revert: true,
            stop: function(){
                var classAttributeGroups = {},
                    i = 0;

                $("#class-attribute-groups > li").each(function(){
                    classAttributeGroups[$(this).data("group-id")] = i++;
                });

                $.post("/admin/nodeclass/attributegroups/sort/{{$nodeClass->id}}", {groups:classAttributeGroups});
            }
        });
        
        $(".edit-attribute-group-modal").click(function(){
            var $modal = $("#edit-attribute-group-modal"),
                id = $(this).data("attribute-group-id");
            
            $modal.modal("show");
            
            $modal.find("[data-field-id]").each(function(){
                var $this = $(this);
                
                $this.val(attributeGroups[id][$this.data("field-id")]);
            });
        });
    </script>
@endsection

@section('content')

    <h1>
        <span class="pull-left">{{$nodeClass->name}}</span>

        <div class="pull-right">
            <form action="{{url("admin/nodeclass/attributes/add/" . $nodeClass->id) }}" method="post">
                {!! csrf_field() !!}
                <input style="margin-right:15px" type="submit" class="btn btn-xs btn-success pull-right" value="Attribut hinzufügen" />
                <select name="type" class="form-control pull-right" style="width: auto; margin-right: 30px">
                    @foreach($attributes as $attribute)
                        <option value="{{$attribute->id}}">{{$attribute->name}}</option>
                    @endforeach
                </select>
                <div class="clearfix"></div>
            </form>
        </div>
        <div class="clearfix"></div>
    </h1>

    <div class="modal fade" id="add-attribute-group-modal">
        <form action="{{url("admin/nodeclass/attributegroups/add/" . $nodeClass->id)}}" method="post">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">{{trans("admin::admin.add_group")}}...</h4>
                    </div>
                    <div class="modal-body">
                        {!! csrf_field() !!}
                        <div class="form-group">
                            <label>{{trans("admin::admin.name")}}</label>
                            <input type="text" class="form-control" name="name" />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">{{trans("admin::admin.cancel")}}</button>
                        <button type="submit" class="btn btn-success">{{trans("admin::admin.save")}}</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </form>
    </div><!-- /.modal -->

    <div class="modal fade" id="edit-attribute-group-modal">
        <form action="{{url("admin/nodeclass/attributegroups/save/" . $nodeClass->id)}}" method="post">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">{{trans("admin::admin.edit_attributegroup")}}</h4>
                    </div>
                    <div class="modal-body">
                        {!! csrf_field() !!}
                        <div class="form-group">
                            <label>{{trans("admin::admin.name")}}</label>
                            <input type="text" class="form-control" name="name" data-field-id="name" />
                        </div>
                        <div class="form-group">
                            <label>{{trans("admin::admin.template")}}</label>
                            <select class="form-control" name="template" data-field-id="template">
                                <option value="">default</option>
                                @foreach($attributeGroupTemplates as $template)
                                    <option value="{{$template}}">{{$template}}</option>
                                @endforeach
                            </select>
                        </div>
                        <input type="hidden" name="id" data-field-id="id" />
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">{{trans("admin::admin.cancel")}}</button>
                        <button type="submit" class="btn btn-success">{{trans("admin::admin.save")}}</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </form>
    </div><!-- /.modal -->
    

    <form action="{{url("admin/nodeclass/attributes/save/" . $nodeClass->id) }}" method="post" id="class-attributes">
        {!! csrf_field() !!}

        @foreach($groupedClassAttributes as $classAttributeGroup)

            <div class="class-attribute-sortable">

                @foreach($classAttributeGroup->items as $attribute)
                    <div class="main-box" data-attribute-id="{{$attribute->id}}" style="position: relative">

                        <input type="hidden" name="sortOrder[{{$attribute->id}}]" value="{{$attribute->sortOrder}}" class="input-sort-order"/>
                        
                        <header class="main-box-header clearfix">
                            @if($attribute->namedIdentifier == "name")
                                <h2><b>{{$attribute->name}}</b></h2>
                            @else
                            <h2>
                                <b>{{$attribute->name}}</b> &lt; {{$attribute->type->name}} &gt; 
                                
                            </h2>
                            @endif
                        </header>
                        <div class="main-box-body clearfix">
                            @if($attribute->namedIdentifier != "name")
                                <div style="position: absolute; top: 10px; right: 10px">
                                    <a href="{{url("admin/nodeclass/attributes/delete/" . $nodeClass->id . "/" . $attribute->id)}}" class="btn btn-xs btn-danger">{{trans("admin::admin.delete")}}</a>
                                </div>
                                

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{trans("admin::admin.name")}}</label>
                                            <input type="text" name="name[{{$attribute->id}}]" value="{{$attribute->name}}" class="form-control"/>
                                            
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{trans("admin::admin.identifier")}}</label>
                                            <input type="text" name="namedIdentifier[{{$attribute->id}}]" value="{{$attribute->namedIdentifier}}" class="form-control"/>
                                        </div>
                                    </div>
                                </div>
                            @else 
                                <input type="hidden" name="name[{{$attribute->id}}]" value="{{$attribute->name}}" />
                                <input type="hidden" name="namedIdentifier[{{$attribute->id}}]" value="{{$attribute->namedIdentifier}}" />
                            @endif

                            
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>
                                            <input type="checkbox" name="translate[{{$attribute->id}}]" value="1" {{ $attribute->translate ? 'checked="checked"' : '' }} /> &nbsp; {{trans("admin::admin.translateable")}}?
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>
                                            <input type="checkbox" name="locked[{{$attribute->id}}]" value="1" {{ $attribute->locked ? 'checked="checked"' : '' }} /> &nbsp; {{trans("admin::admin.locked")}}?
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>
                                            <input type="checkbox" name="showName[{{$attribute->id}}]" value="1" {{ $attribute->showName ? 'checked="checked"' : '' }} /> &nbsp; {{trans("admin::admin.show_name")}}?
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <select name="groupId[{{$attribute->id}}]" class="form-control">
                                        <option {{ $attribute->group_id == 0 ? "selected" : "" }}value="0">{{trans("admin::admin.no_group")}}</option>
                                        @foreach($classAttributeGroups as $classAttributeGroup)
                                            <option {{ $attribute->groupId == $classAttributeGroup->id ? "selected" : "" }} value="{{$classAttributeGroup->id}}">{{$classAttributeGroup->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            @if(method_exists($attribute->class, "renderConfiguration"))
                                {!! $attribute->class->renderConfiguration() !!}
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach

        <script>
            $( ".class-attribute-sortable" ).sortable({
                revert: true,
                stop: function(){
                    var $classAttributeGroups = $(".class-attribute-sortable");

                    $classAttributeGroups.each(function(){
                        var i = 0;

                        $(this).find(".input-sort-order").each(function(){
                            $(this).val(i++);
                        });

                    });
                }
            });
        </script>
        <div class="main-box">
            <header class="main-box-header clearfix" style="min-height:30px">
            </header>
            <div class="main-box-body clearfix">
                <div class="form-group">
                    <a class="btn btn-danger" href="{{url("admin/dashboard")}}">{{trans("admin::admin.cancel")}}</a>
                    <input type="submit" class="btn btn-success" value="{{trans("admin::admin.save")}}" />
                </div>
            </div>
        </div>
    </form>


@endsection