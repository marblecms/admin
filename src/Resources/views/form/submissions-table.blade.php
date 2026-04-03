<div class="main-box">
    <header class="main-box-header clearfix">
        <h2>{{ trans('marble::admin.form_submissions') }} ({{ $submissions->total() }})</h2>
    </header>
    <div class="main-box-body clearfix">
        @if($submissions->isEmpty())
            <p class="text-muted marble-empty-state">{{ trans('marble::admin.no_submissions') }}</p>
        @else
            <table class="table table-striped table-hover marble-table-flush">
                <thead>
                    <tr>
                        <th class="marble-col-md">{{ trans('marble::admin.submitted_at') }}</th>
                        <th>{{ trans('marble::admin.name') }}</th>
                        <th class="marble-col-xs"></th>
                        <th class="marble-col-xs"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($submissions as $submission)
                        <tr class="{{ $submission->read ? '' : 'info' }}" onclick="window.location='{{ route('marble.form.show', [$item, $submission]) }}'" >
                            <td class="text-muted">{{ $submission->created_at->format('d.m.Y H:i') }}</td>
                            <td>
                                @if(!$submission->read)
                                    <span class="label label-primary marble-mr-xs">{{ trans('marble::admin.unread') }}</span>
                                @endif
                                @foreach(array_slice($submission->data ?? [], 0, 2) as $key => $val)
                                    <span class="text-muted">{{ $key }}:</span> {{ Str::limit(is_array($val) ? implode(', ', $val) : $val, 40) }}&nbsp;&nbsp;
                                @endforeach
                            </td>
                            <td onclick="event.stopPropagation()">
                                @if(!$submission->read)
                                    <form method="POST" action="{{ route('marble.form.mark-read', [$item, $submission]) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-xs btn-default">
                                            @include('marble::components.famicon', ['name' => 'tick']) {{ trans('marble::admin.mark_read') }}
                                        </button>
                                    </form>
                                @endif
                            </td>
                            <td onclick="event.stopPropagation()">
                                <form method="POST" action="{{ route('marble.form.destroy', [$item, $submission]) }}" onsubmit="return confirm('{{ trans('marble::admin.are_you_sure') }}')">
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
            @if( $submissions->hasPages() )
                <div class="marble-mt-xs marble-mb-xs">
                    {{ $submissions->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
