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
        <div class="main-box-body clearfix" style="padding:16px">
            <form method="POST" action="{{ route('marble.redirect.store') }}" class="form-inline" style="display:flex;gap:8px;flex-wrap:wrap;align-items:flex-end">
                @csrf
                <div class="form-group">
                    <label style="display:block;font-size:11px;color:#777;margin-bottom:3px">{{ trans('marble::admin.source_path') }}</label>
                    <input type="text" name="source_path" class="form-control input-sm" placeholder="/old-page" style="width:200px" required value="{{ old('source_path') }}" />
                </div>
                <div style="line-height:30px;color:#aaa;margin-top:16px">→</div>
                <div class="form-group" style="position:relative">
                    <label style="display:block;font-size:11px;color:#777;margin-bottom:3px">{{ trans('marble::admin.target_path') }}</label>
                    <input type="text" name="target_path" id="target-path-input" class="form-control input-sm" placeholder="/new-page" style="width:200px" value="{{ old('target_path') }}" autocomplete="off" />
                    <ul id="target-path-suggestions" class="list-group" style="display:none;position:absolute;top:100%;left:0;width:300px;z-index:9999;margin:0;max-height:200px;overflow-y:auto;box-shadow:0 4px 12px rgba(0,0,0,.15)"></ul>
                </div>
                <div class="form-group">
                    <label style="display:block;font-size:11px;color:#777;margin-bottom:3px">{{ trans('marble::admin.status_code') }}</label>
                    <select name="status_code" class="form-control input-sm">
                        <option value="301">301 Permanent</option>
                        <option value="302">302 Temporary</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success btn-sm" style="margin-top:16px">
                    @include('marble::components.famicon', ['name' => 'add']) {{ trans('marble::admin.save') }}
                </button>
            </form>
            @if($errors->any())
                <div class="alert alert-danger" style="margin-top:10px;margin-bottom:0">{{ $errors->first() }}</div>
            @endif
        </div>
    </div>

    {{-- Redirect list --}}
    <div class="main-box">
        <header class="main-box-header clearfix">
            <h2>
                @include('marble::components.famicon', ['name' => 'arrow_right']) {{ trans('marble::admin.redirects') }}
                <span class="badge" style="margin-left:6px">{{ $redirects->total() }}</span>
            </h2>
        </header>
        <div class="main-box-body clearfix">
            @if($redirects->isEmpty())
                <p class="text-muted" style="padding:16px 0;text-align:center">{{ trans('marble::admin.no_redirects') }}</p>
            @else
                <table class="table table-hover" style="margin-bottom:0">
                    <thead>
                        <tr>
                            <th>{{ trans('marble::admin.source_path') }}</th>
                            <th>{{ trans('marble::admin.target_path') }}</th>
                            <th style="width:80px">{{ trans('marble::admin.status_code') }}</th>
                            <th style="width:60px">{{ trans('marble::admin.hits') }}</th>
                            <th style="width:70px">{{ trans('marble::admin.active') }}</th>
                            <th style="width:80px"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($redirects as $redirect)
                            <tr>
                                <td style="font-family:monospace;font-size:12px">{{ $redirect->source_path }}</td>
                                <td style="font-family:monospace;font-size:12px;color:#5580B0">
                                    @if($redirect->target_item_id && $redirect->targetItem)
                                        <a href="{{ route('marble.item.edit', $redirect->targetItem) }}" style="color:#5580B0">
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
                <div class="text-center" style="margin:12px 0">
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
                var $li = $('<li class="list-group-item" style="cursor:pointer;font-size:12px;padding:6px 10px"></li>')
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
