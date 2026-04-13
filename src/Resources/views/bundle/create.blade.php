@extends('marble::layouts.app')

@section('content')
<h1>{{ trans('marble::admin.bundle_new') }}</h1>

<div class="main-box">
    <div class="main-box-body clearfix">
        <form method="POST" action="{{ route('marble.bundle.store') }}">
            @csrf
            <div class="form-group">
                <label>{{ trans('marble::admin.bundle_name') }}</label>
                <input type="text" name="name" class="form-control marble-input-md-w" value="{{ old('name') }}" autofocus required />
            </div>
            <div class="form-group">
                <label>{{ trans('marble::admin.bundle_description') }}</label>
                <textarea name="description" class="form-control marble-input-md-w" rows="2">{{ old('description') }}</textarea>
            </div>
            <div class="form-group pull-right">
                <a href="{{ route('marble.bundle.index') }}" class="btn btn-default">{{ trans('marble::admin.cancel') }}</a>
                <button type="submit" class="btn btn-success">
                    @include('marble::components.famicon', ['name' => 'disk']) {{ trans('marble::admin.save') }}
                </button>
            </div>
            <div class="clearfix"></div>
        </form>
    </div>
</div>
@endsection
