@extends('marble::layouts.app')

@section('content')
    <h1>{{ trans('marble::admin.move_item') }}: {{ $item->name() }}</h1>

    <div class="main-box">
        <header class="main-box-header clearfix">
            <h2><b>{{ trans('marble::admin.select_new_parent') }}</b></h2>
        </header>
        <div class="main-box-body clearfix">

            @if($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            @if($potentialParents->isEmpty())
                <p class="text-muted">No valid parent items found for this blueprint type.</p>
            @else
                <form action="{{ route('marble.item.move', $item) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>{{ trans('marble::admin.select_new_parent') }}</label>
                        <select name="parent_id" class="form-control" size="12">
                            @foreach($potentialParents as $parent)
                                <option value="{{ $parent->id }}" {{ $parent->id == $item->parent_id ? 'selected' : '' }}>
                                    {{ str_repeat('— ', $parent->depth()) }}{{ $parent->name() }}
                                    <small>({{ $parent->blueprint->name }})</small>
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <a href="{{ route('marble.item.edit', $item) }}" class="btn btn-default">
                            @include('marble::components.famicon', ['name' => 'cancel']) {{ trans('marble::admin.cancel') }}
                        </a>
                        <button type="submit" class="btn btn-success">
                            @include('marble::components.famicon', ['name' => 'application_side_expand']) {{ trans('marble::admin.move') }}
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
@endsection
