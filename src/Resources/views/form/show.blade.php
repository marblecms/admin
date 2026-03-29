@extends('marble::layouts.app')

@section('sidebar')
    <div class="main-box clearfix profile-box-menu">
        <div class="main-box-body clearfix">
            <div class="profile-box-header gray-bg clearfix">
                <h2>Submission</h2>
            </div>
            <div class="profile-box-content clearfix">
                <table class="table" style="margin-bottom:0">
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
                        <td style="word-break:break-all;font-size:11px">{{ $submission->user_agent }}</td>
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
        <div style="margin:-6px 0 12px;font-size:12px;color:#888">
            @foreach($breadcrumb as $crumb)
                <a href="{{ route('marble.item.edit', $crumb) }}" style="color:#5580B0">{{ $crumb->name() ?: '—' }}</a>
                <span style="margin:0 4px;color:#bbb">›</span>
            @endforeach
            <span style="color:#555">{{ trans('marble::admin.form_submissions') }}</span>
        </div>
    @endif

    <div class="main-box">
        <header class="main-box-header clearfix" style="display:flex;align-items:center">
            <h2>{{ $submission->created_at->format('d.m.Y H:i') }}</h2>
            <form method="POST" action="{{ route('marble.form.destroy', [$item, $submission]) }}"
                  style="margin-left:auto" onsubmit="return confirm('{{ trans('marble::admin.are_you_sure') }}')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-xs">
                    @include('marble::components.famicon', ['name' => 'bin']) {{ trans('marble::admin.delete') }}
                </button>
            </form>
        </header>
        <div class="main-box-body clearfix">
            <table class="table" style="margin-bottom:0">
                <tbody>
                    @foreach($submission->data ?? [] as $key => $val)
                        <tr>
                            <th style="width:200px;font-weight:bold">{{ $key }}</th>
                            <td>{{ is_array($val) ? implode(', ', $val) : $val }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
