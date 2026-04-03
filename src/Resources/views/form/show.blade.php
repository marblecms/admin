@extends('marble::layouts.app')

@section('sidebar')
    <div class="main-box clearfix profile-box-menu">
        <div class="main-box-body clearfix">
            <div class="profile-box-header gray-bg clearfix">
                <h2>Submission</h2>
            </div>
            <div class="profile-box-content clearfix">
                <table class="table"class="marble-table-flush">
                    <tr>
                        <td class="text-muted">{{ trans('marble::admin.submitted_at') }}</td>
                        <td>{{ $submission->created_at->format('d.m.Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">IP</td>
                        <td>{{ $submission->ip_address }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Browser</td>
                        <td class="marble-break-all marble-text-xs">{{ $submission->user_agent }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ trans('marble::admin.status') }}</td>
                        <td>
                            @if($submission->read)
                                <span class="label label-default">{{ trans('marble::admin.mark_read') }}</span>
                            @else
                                <span class="label label-primary">{{ trans('marble::admin.unread') }}</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <h1>{{ trans('marble::admin.form_submissions') }}: {{ $item->name() }}</h1>

    @php $breadcrumb = \Marble\Admin\Facades\Marble::breadcrumb($item); @endphp
    @if($breadcrumb->count() > 1)
        <div class="marble-breadcrumb">
            @foreach($breadcrumb as $crumb)
                <a href="{{ route('marble.item.edit', $crumb) }}" class="marble-link">{{ $crumb->name() ?: '—' }}</a>
                <span class="marble-breadcrumb-sep">›</span>
            @endforeach
            <span class="text-muted">{{ trans('marble::admin.form_submissions') }}</span>
        </div>
    @endif

    <div class="main-box">
        <header class="main-box-header clearfix marble-flex-center">
            <h2>{{ $submission->created_at->format('d.m.Y H:i') }}</h2>
            <form method="POST" action="{{ route('marble.form.destroy', [$item, $submission]) }}"
                  class="marble-ml-auto" onsubmit="return confirm('{{ trans('marble::admin.are_you_sure') }}')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-xs">
                    @include('marble::components.famicon', ['name' => 'bin']) {{ trans('marble::admin.delete') }}
                </button>
            </form>
        </header>
        <div class="main-box-body clearfix">
            <table class="table"class="marble-table-flush">
                <tbody>
                    @foreach($submission->data ?? [] as $key => $val)
                        <tr>
                            <th class="marble-col-label">{{ $key }}</th>
                            <td>{{ is_array($val) ? implode(', ', $val) : $val }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
