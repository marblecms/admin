@extends('admin::layouts.app')

@section('content')

    <h1>
    	{{trans("admin.classes")}}
        @if(Marble\Admin\App\Helpers\PermissionHelper::allowed("createClass"))
            <div class="pull-right">
                <a href="javascript:$('#import-class-modal').modal('show')" data-modal-id="import-class-modal" class="btn btn-xs btn-info">{{trans("admin.import_class")}}</a>
                <a href="{{ url("admin/nodeclass/groups/add") }}" class="btn btn-xs btn-success">{{trans("admin.add_classgroup")}}</a>
                <a href="{{ url("admin/nodeclass/add") }}" class="btn btn-xs btn-success">{{trans("admin.add_class")}}</a>
            </div>
        @endif
    </h1>
    
    @if($error)
        <div class="alert alert-danger" role="alert">
            {{$error}}
        </div>
    @endif
    
    <div class="modal fade" id="import-class-modal">
        <form action="{{url("admin/nodeclass/import")}}" method="post" enctype="multipart/form-data">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">{{trans("admin.import_class")}}...</h4>
                    </div>
                    <div class="modal-body">
                        {!! csrf_field() !!}
                        <div class="form-group">
                            <label>{{trans("admin.file")}}</label>
                            <input type="file" class="form-control" name="file" />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">{{trans("admin.cancel")}}</button>
                        <button type="submit" class="btn btn-success">{{trans("admin.import")}}</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </form>
    </div><!-- /.modal -->

    @foreach($groupedNodeClasses as $groupedNodeClass)
        <div class="main-box">
            <header class="main-box-header clearfix">
                <h2 class="pull-left">
                    <b>{{$groupedNodeClass->group->name}}</b>
                </h2>
                <div class="pull-right">
                    @if(Marble\Admin\App\Helpers\PermissionHelper::allowed("editClass"))
                        <a href="{{ url("admin/nodeclass/groups/edit/" . $groupedNodeClass->group->id) }}" class="btn btn-xs btn-info">{{trans("admin.edit")}}</a>
                    @endif
                    @if(Marble\Admin\App\Helpers\PermissionHelper::allowed("deleteClass") && $groupedNodeClass->group->id !== 0)
                        <a href="{{ url("admin/nodeclass/groups/delete/" . $groupedNodeClass->group->id) }}" onclick="return confirm('{{trans("admin.are_you_sure")}}');" class="btn btn-xs btn-danger">{{trans("admin.delete")}}</a>
                    @endif
                </div>
                <div class="clearfix"></div>
            </header>
            <div class="main-box-body clearfix">        
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th><a href="#"><span>{{trans("admin.name")}}</span></a></th>
                                <th class="text-right"><span>&nbsp;</span></th>
                            </tr>
                        </thead>
                        <tbody> 
                            @foreach($groupedNodeClass->items as $nodeClass)
                                <tr class="reveal-button-group">
                                    <td>
                                        @if(Marble\Admin\App\Helpers\PermissionHelper::allowed("editClass"))
                                            <a href="{{ url("admin/nodeclass/edit/" . $nodeClass->id) }}">{{$nodeClass->name}}</a>
                                        @else
                                            {{$nodeClass->name}}
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <div class="btn-group">
                                            @if(Marble\Admin\App\Helpers\PermissionHelper::allowed("editClass"))
                                                <a href="{{ url("admin/nodeclass/attributes/edit/" . $nodeClass->id) }}" class="btn btn-primary btn-xs">{{trans("admin.edit_attributes")}}</a>
                                                <a href="{{ url("admin/nodeclass/edit/" . $nodeClass->id) }}" class="btn btn-info btn-xs">{{trans("admin.edit")}}</a>
                                            @endif
                                            <a href="{{ url("admin/nodeclass/export/" . $nodeClass->id) }}" class="btn btn-default btn-xs" download>{{trans("admin.export")}}</a>
                                            @if(Marble\Admin\App\Helpers\PermissionHelper::allowed("deleteClass"))
                                                <a href="{{ url("admin/nodeclass/delete/" . $nodeClass->id) }}" onclick="return confirm('{{trans("admin.are_you_sure")}}');" class="btn btn-xs btn-danger">{{trans("admin.delete")}}</a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endforeach
    

@endsection