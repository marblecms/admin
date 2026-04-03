@extends('marble::layouts.app')

@section('content')
    <h1>@include('marble::components.famicon', ['name' => 'bin']) {{ trans('marble::admin.trash') }}</h1>

    <div class="main-box">
        <header class="main-box-header clearfix">
            <h2>
                <div class="pull-left">{{ trans('marble::admin.trashed_items') }} ({{ $items->total() }})</div>
                @if($items->total() > 0)
                    <div class="pull-right">
                        <form method="POST" action="{{ route('marble.trash.empty') }}" onsubmit="return confirm('{{ trans('marble::admin.are_you_sure') }}')">
                            @csrf
                            <button type="submit" class="btn btn-xs btn-danger">
                                @include('marble::components.famicon', ['name' => 'bin_empty']) {{ trans('marble::admin.empty_trash') }}
                            </button>
                        </form>
                    </div>
                @endif
                <div class="clearfix"></div>
            </h2>
        </header>
        <div class="main-box-body clearfix">
            @if($items->isEmpty())
                <p class="text-muted marble-p-20">{{ trans('marble::admin.trash_empty') }}</p>
            @else
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>{{ trans('marble::admin.name') }}</th>
                            <th>Blueprint</th>
                            <th>{{ trans('marble::admin.deleted_at') }}</th>
                            <th class="text-right"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            <tr>
                                <td>
                                    @if($item->blueprint && $item->blueprint->icon)
                                        @include('marble::components.famicon', ['name' => $item->blueprint->icon])
                                    @endif
                                    {{ $item->name() }}
                                </td>
                                <td>{{ $item->blueprint->name ?? '—' }}</td>
                                <td>{{ $item->deleted_at->format('d.m.Y H:i') }}</td>
                                <td class="text-right">
                                    <div class="btn-group">
                                        <form method="POST" action="{{ route('marble.trash.restore', $item->id) }}" class="marble-inline-form">
                                            @csrf
                                            <button type="submit" class="btn btn-xs btn-info">
                                                @include('marble::components.famicon', ['name' => 'arrow_rotate_cw']) {{ trans('marble::admin.restore') }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('marble.trash.force-delete', $item->id) }}" class="marble-inline-form" onsubmit="return confirm('{{ trans('marble::admin.are_you_sure') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-danger">
                                                @include('marble::components.famicon', ['name' => 'bin']) {{ trans('marble::admin.delete_permanent') }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="marble-box-body">
                    {{ $items->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
