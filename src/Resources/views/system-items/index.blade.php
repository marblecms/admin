@extends('marble::layouts.app')

@section('content')
    <h1>
        @include('marble::components.famicon', ['name' => 'cog'])
        System Items
    </h1>

    <div class="main-box">
        <div class="main-box-body clearfix">
            @if($blueprints->isEmpty())
                <p class="text-muted marble-empty-state">
                    No system blueprints yet. Create a Blueprint with <strong>Hide system fields</strong> enabled.
                </p>
            @else
                <table class="table table-hover marble-text-sm">
                    <thead>
                        <tr>
                            <th>{{ trans('marble::admin.classes') }}</th>
                            <th class="marble-col-sm"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($blueprints as $blueprint)
                            @php $item = $items[$blueprint->id] ?? null; @endphp
                            <tr onclick="window.location='{{ $item ? route('marble.item.edit', $item) : '#' }}'" >
                                <td>
                                    @include('marble::components.famicon', ['name' => $blueprint->effectiveIcon()])
                                    {{ $blueprint->name }}
                                </td>
                                <td onclick="event.stopPropagation()">
                                    @if($item)
                                        <a href="{{ route('marble.item.edit', $item) }}" class="btn btn-xs btn-info">
                                            @include('marble::components.famicon', ['name' => 'pencil'])
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection
