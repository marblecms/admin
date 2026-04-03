@extends('marble::layouts.app')

@section('content_class', 'col-lg-12')

@section('content')
    <h1>Export Package</h1>

    <div class="main-box">
        <div class="main-box-body clearfix">
            <form method="POST" action="{{ route('marble.package.export.do') }}">
                @csrf

                <div class="form-group">
                    <label>Package Name</label>
                    <input type="text" name="package_name" class="form-control marble-input-md-w" value="marble-package" pattern="[a-zA-Z0-9_\-]+" required />
                    <small class="text-muted">Alphanumeric, dashes and underscores only.</small>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <h3>Blueprints</h3>
                        @if($blueprints->isEmpty())
                            <p class="text-muted">No blueprints available.</p>
                        @else
                            @foreach($blueprints as $blueprint)
                                <div class="checkbox">
                                    <label class="marble-fw-normal">
                                        <input type="checkbox" name="blueprint_ids[]" value="{{ $blueprint->id }}">
                                        {{ $blueprint->name }}
                                        <small class="text-muted">({{ $blueprint->identifier }})</small>
                                    </label>
                                </div>
                            @endforeach
                        @endif
                    </div>
                    <div class="col-md-6">
                        <h3>Custom Field Types</h3>
                        @if(empty($customFieldTypes))
                            <p class="text-muted">No custom field types registered.</p>
                        @else
                            @foreach($customFieldTypes as $ft)
                                <div class="checkbox">
                                    <label class="marble-fw-normal">
                                        <input type="checkbox" name="field_types[]" value="{{ $ft->identifier() }}">
                                        {{ $ft->name() }}
                                        <small class="text-muted">({{ $ft->identifier() }})</small>
                                    </label>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>

                <hr>

                <button type="submit" class="btn btn-success">
                    @include('marble::components.famicon', ['name' => 'box'])
                    Download Package
                </button>
                <a href="{{ route('marble.package.import') }}" class="btn btn-default marble-ml-sm">
                    Import Package
                </a>
            </form>
        </div>
    </div>
@endsection
