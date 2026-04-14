@extends('marble::layouts.app')

@php $prefix = config('marble.route_prefix', 'admin'); @endphp

@section('content')
    <h1>{{ trans('marble::admin.add_children') }}</h1>

    <div class="main-box">
        <header class="main-box-header clearfix">
            <h2>{{ trans('marble::admin.add_children') }}</h2>
        </header>
        <div class="main-box-body clearfix">
            <form action="{{ url("{$prefix}/item/create") }}" method="post">
                @csrf
                <input type="hidden" name="parent_id" value="{{ $parentItem->id }}" />

                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" value="" class="form-control"/>
                </div>
                <div class="form-group">
                    <label>Blueprint</label>
                    <select name="blueprint_id" class="form-control">
                        @foreach($allowedBlueprints as $blueprint)
                            <option value="{{ $blueprint->id }}" {{ $blueprint->id === $preselectedBlueprint ? 'selected' : '' }}>{{ $blueprint->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-success">@include('marble::components.famicon', ['name' => 'disk']) {{ trans('marble::admin.save') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection
