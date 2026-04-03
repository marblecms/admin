@extends('marble::layouts.app')

@section('content')
    <div class="marble-page-header">
        <h1>{{ trans('marble::admin.portal_users') }}</h1>
        <a href="{{ route('marble.portal-user.create') }}" class="btn btn-success btn-sm">
            @include('marble::components.famicon', ['name' => 'plus']) {{ trans('marble::admin.add') }}
        </a>
    </div>

    <div class="main-box">
        <div class="main-box-body">
            @if($portalUsers->isEmpty())
                <p class="text-muted">{{ trans('marble::admin.no_portal_users') }}</p>
            @else
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>{{ trans('marble::admin.email') }}</th>
                            <th>{{ trans('marble::admin.item') }}</th>
                            <th>{{ trans('marble::admin.status') }}</th>
                            <th>{{ trans('marble::admin.created_at') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($portalUsers as $pu)
                            <tr>
                                <td>{{ $pu->email }}</td>
                                <td>{{ $pu->item?->name() ?? '-' }}</td>
                                <td>
                                    <form method="POST" action="{{ route('marble.portal-user.toggle', $pu) }}" class="marble-inline-form">
                                        @csrf
                                        <button type="submit" class="btn btn-xs {{ $pu->enabled ? 'btn-success' : 'btn-default' }}">
                                            {{ $pu->enabled ? trans('marble::admin.active') : trans('marble::admin.inactive') }}
                                        </button>
                                    </form>
                                </td>
                                <td>{{ $pu->created_at->format('Y-m-d') }}</td>
                                <td class="text-right">
                                    <a href="{{ route('marble.portal-user.edit', $pu) }}" class="btn btn-xs btn-default">
                                        @include('marble::components.famicon', ['name' => 'pencil'])
                                    </a>
                                    <form method="POST" action="{{ route('marble.portal-user.delete', $pu) }}" class="marble-inline-form"
                                          onsubmit="return confirm('{{ trans('marble::admin.confirm_delete') }}')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-danger">
                                            @include('marble::components.famicon', ['name' => 'trash'])
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $portalUsers->links() }}
            @endif
        </div>
    </div>
@endsection
