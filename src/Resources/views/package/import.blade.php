@extends('marble::layouts.app')

@section('content_class', 'col-lg-8')

@section('content')
    <h1>Import Package</h1>

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
                    @include('marble::components.famicon', ['name' => 'box'])
                    Import
                </button>
                <a href="{{ route('marble.package.export') }}" class="btn btn-default marble-ml-sm">
                    Export Package
                </a>
            </form>
        </div>
    </div>
@endsection
