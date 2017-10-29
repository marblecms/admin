@extends('admin::layouts.app')

@section('content')

    <h1>
    	{{trans("admin.usergroups")}}
        @if(Marble\Admin\App\Helpers\PermissionHelper::allowed("createGroup"))
            <div class="pull-right">
                <a href="{{ url("admin/usergroup/add") }}" class="btn btn-xs btn-success">{{trans("admin.add_usergroup")}}</a>
            </div>
        @endif
    </h1>


    <div class="main-box">
        <header class="main-box-header clearfix">
            <h2>
                {{trans("admin.usergroups")}}
            </h2>
        </header>
        <div class="main-box-body clearfix">        
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th><a href="#"><span>Name</span></a></th>
                            <th class="text-right"><span>&nbsp;</span></th>
                        </tr>
                    </thead>
                    <tbody> 
                        @foreach($groups as $group)
                            <tr>
                                <td>
                                    @if(Marble\Admin\App\Helpers\PermissionHelper::allowed("editGroup"))
                                        <a href="{{ url("admin/usergroup/edit/" . $group->id) }}">{{$group->name}}</a>
                                    @else
                                        {{$group->name}}
                                    @endif
                                </td>
                                <td class="text-right">
                                    <div class="btn-group">
                                        @if(Marble\Admin\App\Helpers\PermissionHelper::allowed("editGroup"))
                                            <a href="{{ url("admin/usergroup/edit/" . $group->id) }}" class="btn btn-info btn-xs">{{trans("admin.edit")}}</a>
                                        @endif
                                        @if($group->id != 0 and Marble\Admin\App\Helpers\PermissionHelper::allowed("deleteGroup"))
                                            <a href="{{ url("admin/usergroup/delete/" . $group->id) }}" onclick="return confirm('{{trans("admin.are_you_sure")}}');" class="btn btn-xs btn-danger">{{trans("admin.delete")}}</a>
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
    

@endsection