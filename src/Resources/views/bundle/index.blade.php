@extends('marble::layouts.app')

@section('content')
<div style="display:flex;align-items:center;gap:16px;margin-bottom:16px;">
    <h1 style="margin:0;">{{ trans('marble::admin.bundles') }}</h1>
    <a href="{{ route('marble.bundle.create') }}" class="btn btn-success btn-sm">
        @include('marble::components.famicon', ['name' => 'add']) {{ trans('marble::admin.bundle_new') }}
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="main-box">
    <div class="main-box-body clearfix">
        @if($bundles->isEmpty())
            <p class="text-muted marble-pad-md">{{ trans('marble::admin.no_items') }}</p>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ trans('marble::admin.name') }}</th>
                        <th>{{ trans('marble::admin.bundle_items') }}</th>
                        <th>Status</th>
                        <th>{{ trans('marble::admin.by') }}</th>
                        <th>{{ trans('marble::admin.last_edited') }}</th>
                        <th class="marble-col-xs"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bundles as $bundle)
                    <tr>
                        <td>
                            <a href="{{ route('marble.bundle.show', $bundle) }}" class="marble-link">
                                <strong>{{ $bundle->name }}</strong>
                            </a>
                            @if($bundle->description)
                                <p class="text-muted marble-text-sm marble-mb-0">{{ $bundle->description }}</p>
                            @endif
                        </td>
                        <td>{{ $bundle->bundle_items_count }}</td>
                        <td>
                            @php $statusColors = ['draft' => 'default', 'published' => 'success', 'rolled_back' => 'warning']; @endphp
                            <span class="label label-{{ $statusColors[$bundle->status] ?? 'default' }}">
                                {{ trans('marble::admin.bundle_status_' . $bundle->status) }}
                            </span>
                        </td>
                        <td class="text-muted marble-text-sm">{{ $bundle->creator?->name ?? '—' }}</td>
                        <td class="text-muted marble-text-sm">{{ $bundle->updated_at->diffForHumans() }}</td>
                        <td>
                            <form method="POST" action="{{ route('marble.bundle.destroy', $bundle) }}"
                                  onsubmit="return confirm('{{ trans('marble::admin.are_you_sure') }}')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-xs btn-danger">
                                    @include('marble::components.famicon', ['name' => 'bin'])
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection
