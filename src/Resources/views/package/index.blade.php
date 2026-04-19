@extends('marble::layouts.app')

@section('content_class', 'col-lg-10')

@section('content')
    <h1>Packages</h1>

    <ul class="nav nav-tabs marble-mb-md">
        <li role="presentation" class="{{ $activeTab === 'export' ? 'active' : '' }}">
            <a href="#tab-export" data-toggle="tab">
                @include('marble::components.famicon', ['name' => 'box'])
                Export
            </a>
        </li>
        <li role="presentation" class="{{ $activeTab === 'import' ? 'active' : '' }}">
            <a href="#tab-import" data-toggle="tab">
                @include('marble::components.famicon', ['name' => 'page_white_paste'])
                Import
            </a>
        </li>
    </ul>

    <div class="tab-content">

        {{-- Export tab --}}
        <div role="tabpanel" class="tab-pane {{ $activeTab === 'export' ? 'active' : '' }}" id="tab-export">
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
                    </form>
                </div>
            </div>
        </div>

        {{-- Import tab --}}
        <div role="tabpanel" class="tab-pane {{ $activeTab === 'import' ? 'active' : '' }}" id="tab-import">

            @if(session('import_log'))
                <div class="main-box">
                    <header class="main-box-header clearfix">
                        <h2>Import Results</h2>
                    </header>
                    <div class="main-box-body clearfix">
                        @if(session('import_success'))
                            <div class="alert alert-success">Import completed successfully.</div>
                        @else
                            <div class="alert alert-danger">Import failed.</div>
                        @endif
                        <ul class="list-unstyled">
                            @foreach(session('import_log') as $line)
                                <li class="marble-import-log-item">
                                    @if(str_starts_with($line, 'Warning'))
                                        <span class="text-warning">{{ $line }}</span>
                                    @elseif(str_starts_with($line, 'Note'))
                                        <span class="text-info">{{ $line }}</span>
                                    @else
                                        <span class="text-success">{{ $line }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <div class="main-box">
                <header class="main-box-header clearfix">
                    <h2>Upload Package</h2>
                </header>
                <div class="main-box-body clearfix">
                    <form method="POST" action="{{ route('marble.package.import.do') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group">
                            <label>Package File</label>
                            <input type="file" name="package" accept=".zip" required />
                            <small class="text-muted">Select a <code>.marble.zip</code> package file.</small>
                        </div>

                        <div class="alert alert-info">
                            <strong>Note:</strong> After importing custom field types, you must manually register them in your application's ServiceProvider:
                            <pre class="marble-code-hint">Marble::registerFieldType(new \App\MarbleFieldTypes\YourType\FieldType());</pre>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            @include('marble::components.famicon', ['name' => 'page_white_paste'])
                            Import Package
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
@endsection
