@extends('admin::layouts.app')

@section('content')

    <h1>Klassegruppe Editieren - {{$nodeClassGroup->name}}</h1>

    <div class="main-box">
        <header class="main-box-header clearfix">
            <h2>Klassengruppe Editieren</h2>
        </header>
        <div class="main-box-body clearfix">
            <form action="{{ url("admin/nodeclass/groups/save/" . $nodeClassGroup->id) }}" method="post">
                {!! csrf_field() !!}
            
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" value="{{$nodeClassGroup->name}}" class="form-control"/>
                </div>
            
            
                <div class="form-group">
                    <input type="submit" class="btn btn-success" value="Speichern" />
                </div>
            </form>
        </div>
    </div>
    

@endsection