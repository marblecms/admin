@extends('marble::layouts.app')

@section('content_class', 'col-lg-12')

@section('content')
    <h1>{{ trans('marble::admin.redirect_manager') }}</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Add new redirect --}}
    <div class="main-box">
        <header class="main-box-header clearfix">
            <h2>@include('marble::components.famicon', ['name' => 'add']) {{ trans('marble::admin.add_redirect') }}</h2>
        </header>
        <div class="main-box-body clearfix marble-pad-md">
            <form method="POST" action="{{ route('marble.redirect.store') }}" class="marble-flex-wrap">
                @csrf
                <div class="form-group">
                    <label class="marble-label-sm">{{ trans('marble::admin.source_path') }}</label>
                    <input type="text" name="source_path" class="form-control input-sm" placeholder="/old-page" class="marble-redirect-input" required value="{{ old('source_path') }}" />
                </div>
                <div class="marble-redirect-arrow">→</div>
                <div class="form-group marble-relative">
                    <label class="marble-label-sm">{{ trans('marble::admin.target_path') }}</label>
                    <input type="text" name="target_path" id="target-path-input" class="form-control input-sm" placeholder="/new-page" class="marble-redirect-input" value="{{ old('target_path') }}" autocomplete="off" />
                    <ul id="target-path-suggestions" class="list-group marble-suggest-list marble-hidden"></ul>
                </div>
                <div class="form-group">
                    <label class="marble-label-sm">{{ trans('marble::admin.status_code') }}</label>
                    <select name="status_code" class="form-control input-sm">
                        <option value="301">301 Permanent</option>
                        <option value="302">302 Temporary</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success btn-sm marble-mt-md">
                    @include('marble::components.famicon', ['name' => 'add']) {{ trans('marble::admin.save') }}
                </button>
            </form>
            @if($errors->any())
                <div class="alert alert-danger marble-mt-sm marble-mb-0">{{ $errors->first() }}</div>
            @endif
        </div>
    </div>

    {{-- Redirect list --}}
    <div class="main-box">
        <header class="main-box-header clearfix">
            <h2>
                @include('marble::components.famicon', ['name' => 'arrow_right']) {{ trans('marble::admin.redirects') }}
                <span class="badge marble-mr-xs">{{ $redirects->total() }}</span>
            </h2>
        </header>
        <div class="main-box-body clearfix">
            @if($redirects->isEmpty())
                <p class="text-muted text-center marble-mt-sm marble-mb-sm">{{ trans('marble::admin.no_redirects') }}</p>
            @else
                <table class="table table-hover marble-table-flush">
                    <thead>
                        <tr>
                            <th>{{ trans('marble::admin.source_path') }}</th>
                            <th>{{ trans('marble::admin.target_path') }}</th>
                            <th class="marble-col-sm">{{ trans('marble::admin.status_code') }}</th>
                            <th class="marble-col-xs">{{ trans('marble::admin.hits') }}</th>
                            <th class="marble-col-sm">{{ trans('marble::admin.active') }}</th>
                            <th class="marble-col-sm"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($redirects as $redirect)
                            <tr>
                                <td class="marble-mono">{{ $redirect->source_path }}</td>
                                <td class="marble-mono marble-link">
                                    @if($redirect->target_item_id && $redirect->targetItem)
                                        <a href="{{ route('marble.item.edit', $redirect->targetItem) }}" class="marble-link">
                                            @include('marble::components.famicon', ['name' => 'page_white'])
                                            {{ $redirect->targetItem->name() ?: '—' }}
                                        </a>
                                    @else
                                        {{ $redirect->target_path }}
                                    @endif
                                </td>
                                <td>
                                    <span class="label {{ $redirect->status_code === 301 ? 'label-warning' : 'label-info' }}">
                                        {{ $redirect->status_code }}
                                    </span>
                                </td>
                                <td class="text-muted">{{ number_format($redirect->hits) }}</td>
                                <td>
                                    <form method="POST" action="{{ route('marble.redirect.toggle', $redirect) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-xs {{ $redirect->active ? 'btn-success' : 'btn-default' }}" title="{{ $redirect->active ? trans('marble::admin.active') : trans('marble::admin.inactive') }}">
                                            @include('marble::components.famicon', ['name' => $redirect->active ? 'tick' : 'cancel'])
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('marble.redirect.destroy', $redirect) }}"
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
                <div class="text-center marble-mt-sm marble-mb-sm">
                    {{ $redirects->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

@section('javascript')
<script>
$(function() {
    var $input = $('#target-path-input');
    var $list  = $('#target-path-suggestions');

    $input.on('keyup', function() {
        var q = $(this).val();
        if (q.length < 2) { $list.hide().html(''); return; }

        $.get('{{ route('marble.item.search') }}', { q: q }, function(items) {
            $list.html('');
            if (!items.length) { $list.hide(); return; }
            $.each(items, function(i, item) {
                var slug = item.slug || '/';
                var $li = $('<li class="list-group-item marble-suggest-item"></li>')
                    .text(item.name + ' — ' + slug)
                    .on('click', function() {
                        $input.val(slug);
                        $list.hide().html('');
                    });
                $list.append($li);
            });
            $list.show();
        });
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('#target-path-input, #target-path-suggestions').length) {
            $list.hide().html('');
        }
    });
});
</script>
@endsection
