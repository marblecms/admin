@extends('marble::layouts.app')

@php $prefix = config('marble.route_prefix', 'admin'); @endphp

@section('content')
    <h1>Edit Blueprint Group - {{ $group->name }}</h1>

    <div class="main-box">
        <header class="main-box-header clearfix">
            <h2>Edit Blueprint Group</h2>
        </header>
        <div class="main-box-body clearfix">
            <form action="{{ url("{$prefix}/blueprint/group/save/{$group->id}") }}" method="post">
                @csrf
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" value="{{ $group->name }}" class="form-control"/>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-success">@include('marble::components.famicon', ['name' => 'disk']) {{ trans('marble::admin.save') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection
