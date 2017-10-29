@extends('admin::layouts.app')

@section('content')
    <h1>Dashboard</h1>

    <div class="row">
    	@if( \Marble\Admin\App\Helpers\PermissionHelper::allowed("listClass") )
            <div class="col-md-6">
                <div class="main-box">
                    <header class="main-box-header clearfix">
                        <h2>{{trans("admin.classes")}}</h2>
                    </header>
                    <div class="main-box-body clearfix">        
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th><a href="#"><span>{{trans("admin.name")}}</span></a></th>
                                        <th class="text-right"><span>&nbsp;</span></th>
                                    </tr>
                                </thead>
                                <tbody> 
                                    @foreach($nodeClasses as $nodeClass)
                                        <tr>
                                            <td>
                                                @if(Marble\Admin\App\Helpers\PermissionHelper::allowed("editClass"))
                                                    <a href="{{ url("admin/nodeclass/attributes/edit/" . $nodeClass->id) }}">{{$nodeClass->name}}</a>
                                                @else
                                                    {{$nodeClass->name}}
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                <div class="btn-group">
                                                    @if(Marble\Admin\App\Helpers\PermissionHelper::allowed("editClass"))
                                                        <a href="{{ url("admin/nodeclass/edit/" . $nodeClass->id) }}" class="btn btn-info btn-xs">{{trans("admin.edit")}}</a>
                                                    @endif
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
            </div>
        @endif
    	@if( \Marble\Admin\App\Helpers\PermissionHelper::allowed("listUser") )
            <div class="col-md-6">
                <div class="main-box">
                    <header class="main-box-header clearfix">
                        <h2>{{trans("admin.users")}}</h2>
                    </header>
                    <div class="main-box-body clearfix">        
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th><a href="#"><span>{{trans("admin.name")}}</span></a></th>
                                        <th class="text-right"><span>&nbsp;</span></th>
                                    </tr>
                                </thead>
                                <tbody> 
                                    @foreach($users as $user)
                                        <tr>
                                            <td>

                                                @if(Marble\Admin\App\Helpers\PermissionHelper::allowed("editUser"))
                                                    <a href="{{ url("admin/user/edit/" . $user->id) }}">{{$user->name}}</a>
                                                @else
                                                    {{$user->name}}
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                <div class="btn-group">
                                                    @if(Marble\Admin\App\Helpers\PermissionHelper::allowed("editUser"))
                                                        <a href="{{ url("admin/user/edit/" . $user->id) }}" class="btn btn-info btn-xs">{{trans("admin.edit")}}</a>
                                                    @endif

                                                    @if(Marble\Admin\App\Helpers\PermissionHelper::allowed("deleteUser"))
                                                        <a href="{{ url("admin/user/delete/" . $user->id) }}" onclick="return confirm('{{trans("admin.are_you_sure")}}');" class="btn btn-xs btn-danger">{{trans("admin.delete")}}</a>
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
            </div>
        @endif
    </div>
@endsection