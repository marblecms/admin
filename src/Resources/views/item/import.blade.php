@extends('marble::layouts.app')

@section('content')
    <h1>@include('marble::components.famicon', ['name' => 'page_white_paste']) {{ trans('marble::admin.import') }}</h1>

    <div class="main-box">
        <header class="main-box-header clearfix">
            <h2><b>{{ trans('marble::admin.import_json') }}</b></h2>
        </header>
        <div class="main-box-body clearfix">
            @if($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('marble.item.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label>{{ trans('marble::admin.parent_item') }}</label>
                    <select name="parent_id" class="form-control">
                        @foreach($items as $candidate)
                            <option value="{{ $candidate->id }}">
                                {{ str_repeat('— ', $candidate->depth()) }}{{ $candidate->name() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>{{ trans('marble::admin.file') }} (JSON)</label>
                    <input type="file" name="file" accept=".json" class="form-control" />
                </div>
                <div class="form-group">
                    <a href="{{ route('marble.dashboard') }}" class="btn btn-default">
                        @include('marble::components.famicon', ['name' => 'cancel']) {{ trans('marble::admin.cancel') }}
                    </a>
                    <button type="submit" class="btn btn-success">
                        @include('marble::components.famicon', ['name' => 'page_white_paste']) {{ trans('marble::admin.import') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
