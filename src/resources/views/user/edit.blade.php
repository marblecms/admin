@extends('admin::layouts.app')

@section('content')

    <h1>{{$user->name}}</h1>



    <form action="{{url("admin/user/save/" . $user->id) }}" enctype="multipart/form-data" method="post">

        {!! csrf_field() !!}

            
        <div class="main-box">
            <header class="main-box-header clearfix">
                    <h2><b>{{$user->name}}</b></h2>
            </header>
            <div class="main-box-body clearfix">


                <div class="form-group">
                    <label>{{trans("admin::admin.name")}}</label>
                    
                    <input type="text" class="form-control" name="name" value="{{$user->name}}" />
                </div>
                <div class="form-group">
                    <label>{{trans("admin::admin.email")}}</label>
                    
                    <input type="text" class="form-control" name="email" value="{{$user->email}}" />
                </div>
                <div class="form-group">
                    <label>{{trans("admin::admin.new_password")}}</label>
                    
                    <input type="password" class="form-control" name="password" value="" />
                </div>

                <div class="form-group">
                    <label>{{trans("admin::admin.group")}}</label>
                    <select name="group_id" class="form-control">
                        @foreach($userGroups as $userGroup)
                            <option value="{{$userGroup->id}}" {{ $userGroup->id == $user->groupId ? 'selected="selected"' : '' }}>{{$userGroup->name}}</option>
                        @endforeach
                    </select>
                </div>

            </div>
        </div>



        <div class="form-group pull-right">
            <a href="{{url("/admin/user/list")}}" class="btn btn-primary">{{trans("admin::admin.cancel")}}</a>
            <input type="submit" class="btn btn-success" value="{{trans("admin::admin.save")}}" />
        </div>
        <div class="clearfix"></div>
        <br /><br /><br />
    </form>


@endsection