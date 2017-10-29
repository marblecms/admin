@extends('admin::layouts.app')

@section('javascript-head')
    <script type="text/javascript" src="{{ URL::asset('assets/admin/js/attributes/attributes.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('vendor/marblecore/admin/js/attributes/object-relation-edit.js') }}"></script>
@endsection

@section('javascript')
    <script type="text/javascript" src="{{ URL::asset('assets/admin/js/attributes/attributes-init.js') }}"></script>
@endsection

@section('content')

    <h1>{{$group->name}}</h1>

    <form action="{{url("admin/usergroup/save/" . $group->id) }}" enctype="multipart/form-data" method="post">

        {!! csrf_field() !!}

            
        <div class="main-box">
            <header class="main-box-header clearfix">
                    <h2><b>{{$group->name}}</b></h2>
            </header>
            <div class="main-box-body clearfix">


                <div class="form-group">
                    <label>{{trans("admin::admin.name")}}</label>
                    
                    <input type="text" class="form-control" name="name" value="{{$group->name}}" />
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>{{trans("admin::admin.classes")}}</label>
                            <br />
                            <div class="pull-left">
                                <div class="onoffswitch onoffswitch-success">
                                
                                    <input type="checkbox" name="createClass" class="onoffswitch-checkbox" value="1" id="onoff-create_class" {{ $group->createClass ? 'checked="checked"' : '' }}>
                                    <label class="onoffswitch-label" for="onoff-create_class">
                                        <div class="onoffswitch-inner"></div>
                                        <div class="onoffswitch-switch"></div>
                                    </label>
                                </div>
                            </div>
                            <div class="pull-left" style="padding-top:5px;margin-right: 40px;">
                                <label>{{trans("admin::admin.create")}}</label>
                            </div>
                            <div class="clearfix"></div>
                            <div class="pull-left">
                                <div class="onoffswitch onoffswitch-success">
                                    
                                    <input type="checkbox" name="editClass" class="onoffswitch-checkbox" value="1" id="onoff-edit_class" {{ $group->editClass ? 'checked="checked"' : '' }}>
                                    <label class="onoffswitch-label" for="onoff-edit_class">
                                        <div class="onoffswitch-inner"></div>
                                        <div class="onoffswitch-switch"></div>
                                    </label>
                                </div>
                            </div>
                            <div class="pull-left" style="padding-top:5px;margin-right: 40px;">
                                <label>{{trans("admin::admin.edit")}}</label>
                            </div>
                            <div class="clearfix"></div>
                            <div class="pull-left">
                                <div class="onoffswitch onoffswitch-success">
                                    
                                    <input type="checkbox" name="deleteClass" class="onoffswitch-checkbox" value="1" id="onoff-delete_class" {{ $group->deleteClass ? 'checked="checked"' : '' }}>
                                    <label class="onoffswitch-label" for="onoff-delete_class">
                                        <div class="onoffswitch-inner"></div>
                                        <div class="onoffswitch-switch"></div>
                                    </label>
                                </div>
                            </div>
                            <div class="pull-left" style="padding-top:5px;margin-right: 40px;">
                                <label>{{trans("admin::admin.delete")}}</label>
                            </div>
                            <div class="clearfix"></div>
                            <div class="pull-left">
                                <div class="onoffswitch onoffswitch-success">
                                    
                                    <input type="checkbox" name="listClass" class="onoffswitch-checkbox" value="1" id="onoff-list_class" {{ $group->listClass ? 'checked="checked"' : '' }}>
                                    <label class="onoffswitch-label" for="onoff-list_class">
                                        <div class="onoffswitch-inner"></div>
                                        <div class="onoffswitch-switch"></div>
                                    </label>
                                </div>
                            </div>
                            <div class="pull-left" style="padding-top:5px;margin-right: 40px;">
                                <label>{{trans("admin::admin.list")}}</label>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>{{trans("admin::admin.users")}}</label>
                            <br />
                            <div class="pull-left">
                                <div class="onoffswitch onoffswitch-success">
                                
                                    <input type="checkbox" name="createUser" class="onoffswitch-checkbox" value="1" id="onoff-create_user" {{ $group->createUser ? 'checked="checked"' : '' }}>
                                    <label class="onoffswitch-label" for="onoff-create_user">
                                        <div class="onoffswitch-inner"></div>
                                        <div class="onoffswitch-switch"></div>
                                    </label>
                                </div>
                            </div>
                            <div class="pull-left" style="padding-top:5px;margin-right: 40px;">
                                <label>{{trans("admin::admin.create")}}</label>
                            </div>
                            <div class="clearfix"></div>
                            <div class="pull-left">
                                <div class="onoffswitch onoffswitch-success">
                                    
                                    <input type="checkbox" name="editUser" class="onoffswitch-checkbox" value="1" id="onoff-edit_user" {{ $group->editUser ? 'checked="checked"' : '' }}>
                                    <label class="onoffswitch-label" for="onoff-edit_user">
                                        <div class="onoffswitch-inner"></div>
                                        <div class="onoffswitch-switch"></div>
                                    </label>
                                </div>
                            </div>
                            <div class="pull-left" style="padding-top:5px;margin-right: 40px;">
                                <label>{{trans("admin::admin.edit")}}</label>
                            </div>
                            <div class="clearfix"></div>
                            <div class="pull-left">
                                <div class="onoffswitch onoffswitch-success">
                                    
                                    <input type="checkbox" name="deleteUser" class="onoffswitch-checkbox" value="1" id="onoff-delete_user" {{ $group->deleteUser ? 'checked="checked"' : '' }}>
                                    <label class="onoffswitch-label" for="onoff-delete_user">
                                        <div class="onoffswitch-inner"></div>
                                        <div class="onoffswitch-switch"></div>
                                    </label>
                                </div>
                            </div>
                            <div class="pull-left" style="padding-top:5px;margin-right: 40px;">
                                <label>{{trans("admin::admin.delete")}}</label>
                            </div>
                            <div class="clearfix"></div>
                            <div class="pull-left">
                                <div class="onoffswitch onoffswitch-success">
                                    
                                    <input type="checkbox" name="listUser" class="onoffswitch-checkbox" value="1" id="onoff-list_user" {{ $group->listUser ? 'checked="checked"' : '' }}>
                                    <label class="onoffswitch-label" for="onoff-list_user">
                                        <div class="onoffswitch-inner"></div>
                                        <div class="onoffswitch-switch"></div>
                                    </label>
                                </div>
                            </div>
                            <div class="pull-left" style="padding-top:5px;margin-right: 40px;">
                                <label>{{trans("admin::admin.list")}}</label>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>{{trans("admin::admin.usergroups")}}</label>
                            <br />
                            <div class="pull-left">
                                <div class="onoffswitch onoffswitch-success">
                                
                                    <input type="checkbox" name="createGroup" class="onoffswitch-checkbox" value="1" id="onoff-create_group" {{ $group->createGroup ? 'checked="checked"' : '' }}>
                                    <label class="onoffswitch-label" for="onoff-create_group">
                                        <div class="onoffswitch-inner"></div>
                                        <div class="onoffswitch-switch"></div>
                                    </label>
                                </div>
                            </div>
                            <div class="pull-left" style="padding-top:5px;margin-right: 40px;">
                                <label>{{trans("admin::admin.create")}}</label>
                            </div>
                            <div class="clearfix"></div>
                            <div class="pull-left">
                                <div class="onoffswitch onoffswitch-success">
                                    
                                    <input type="checkbox" name="editGroup" class="onoffswitch-checkbox" value="1" id="onoff-edit_group" {{ $group->editGroup ? 'checked="checked"' : '' }}>
                                    <label class="onoffswitch-label" for="onoff-edit_group">
                                        <div class="onoffswitch-inner"></div>
                                        <div class="onoffswitch-switch"></div>
                                    </label>
                                </div>
                            </div>
                            <div class="pull-left" style="padding-top:5px;margin-right: 40px;">
                                <label>{{trans("admin::admin.edit")}}</label>
                            </div>
                            <div class="clearfix"></div>
                            <div class="pull-left">
                                <div class="onoffswitch onoffswitch-success">
                                    
                                    <input type="checkbox" name="deleteGroup" class="onoffswitch-checkbox" value="1" id="onoff-delete_group" {{ $group->deleteGroup ? 'checked="checked"' : '' }}>
                                    <label class="onoffswitch-label" for="onoff-delete_group">
                                        <div class="onoffswitch-inner"></div>
                                        <div class="onoffswitch-switch"></div>
                                    </label>
                                </div>
                            </div>
                            <div class="pull-left" style="padding-top:5px;margin-right: 40px;">
                                <label>{{trans("admin::admin.delete")}}</label>
                            </div>
                            <div class="clearfix"></div>
                            <div class="pull-left">
                                <div class="onoffswitch onoffswitch-success">
                                    
                                    <input type="checkbox" name="listGroup" class="onoffswitch-checkbox" value="1" id="onoff-list_group" {{ $group->listGroup ? 'checked="checked"' : '' }}>
                                    <label class="onoffswitch-label" for="onoff-list_group">
                                        <div class="onoffswitch-inner"></div>
                                        <div class="onoffswitch-switch"></div>
                                    </label>
                                </div>
                            </div>
                            <div class="pull-left" style="padding-top:5px;margin-right: 40px;">
                                <label>{{trans("admin::admin.list")}}</label>
                            </div>
                            <div class="clearfix"></div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>{{trans("admin::admin.entrypoint")}}</label>
                        
                        <div class="attribute-container" id="object-relation-entry-node-id">
                            <div class="attribute-object-relation-view"></div>
                            <div class="clearfix"></div>
                            
                            <input type="hidden" name="entryNodeId" class="attribute-object-relation-input" value="{{$group->entryNodeId}}" />
                            <a href="javascript:;" class="btn btn-default btn-xs attribute-object-relation-add">{{trans("admin::admin.select_object")}}</a>

                        </div>
                        <script>
                            Attributes.ready(function(){

                                var objectRelation = new Attributes.ObjectRelation("object-relation-entry-node-id");

                                @if($group->entryNodeId !== -1)
                                    objectRelation.setNode({
                                        id: '{{$group->entryNodeId}}',
                                        name: '{{Marble\Admin\App\Models\Node::find($group->entryNodeId)->name}}'
                                    });
                                @endif

                            });
                        </script>
                    </div>


            
                    <div class="form-group">
                        <label>{{trans("admin::admin.allowed_classes")}}</label>
                        <select multiple name="allowed_classes[]" class="form-control" size="10">
                            <option value="all" {{ (in_array("all",$group->allowedClasses) || !count($group->allowedClasses) ? 'selected="selected"' : '')}} >- Alle -</option>
                            @foreach($groupedNodeClasses as $nodeClasses)
                                <option disabled="disabled">{{$nodeClasses->group->name}}</option>
                                @foreach($nodeClasses->items as $nodeClass)
                                    <option value="{{$nodeClass->id}}" {{ (in_array($nodeClass->id,$group->allowedClasses) ? 'selected="selected"' : '') }}>&nbsp; &nbsp; &nbsp; {{$nodeClass->name}}</option>
                                @endforeach
                            @endforeach
                        </select>
                    </div>

                </div>




            </div>

        <div class="form-group pull-right">
            <a href="{{url("/admin/usergroup/list")}}" class="btn btn-primary">{{trans("admin::admin.cancel")}}</a>
            <input type="submit" class="btn btn-success" value="{{trans("admin::admin.save")}}" />
        </div>
        <div class="clearfix"></div>

    </form>

@endsection