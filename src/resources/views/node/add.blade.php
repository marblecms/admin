@extends('admin::layouts.app')

@section('content')
    <h1>Element hinzufügen</h1>

    <div class="main-box">
        <header class="main-box-header clearfix">
            <h2>Element hinzufügen</h2>
        </header>
        <div class="main-box-body clearfix">
            <form action="{{url("admin/node/create/" . $parentNode->id)}}" method="post">

               {!! csrf_field() !!}

               <div class="form-group">
                   <label>Name</label>
                   <input type="text" name="name" value="" class="form-control"/>
               </div>
               <div class="form-group">
                   <label>Klasse</label>
                   <select name="classId" class="form-control">
                       @foreach($groupedNodeClasses as $nodeClasses)
                           <option disabled="disabled">{{$nodeClasses->group->name}}</option>
                           @foreach($nodeClasses->items as $nodeClass)
                               @if( \Marble\Admin\App\Helpers\PermissionHelper::allowedClass($nodeClass->id) && (in_array("all",$parentNode->class->allowedChildClasses) || in_array($nodeClass->id, $parentNode->class->allowedChildClasses) ) )
                                   <option value="{{$nodeClass->id}}">&nbsp; &nbsp; &nbsp; {{$nodeClass->name}}</option>
                               @endif
                           @endforeach
                       @endforeach
                   </select>
                </div>
        

                <div class="form-group">
                    <input type="submit" class="btn btn-success" value="Speichern" />
                </div>
            </form>
        </div>
    </div>
@endsection