@extends('marble::layouts.app')

@section('content_class', 'col-lg-9')

@section('sidebar')
    <div class="main-box clearfix profile-box-menu">
        <div class="main-box-body clearfix">
            <div class="profile-box-header {{ $bundle->status === 'published' ? 'green-bg' : 'gray-bg' }} clearfix">
                <h2>{{ $bundle->name }}</h2>
                <div class="job-position marble-pl-lg">
                    @php $statusColors = ['draft' => 'default', 'published' => 'success', 'rolled_back' => 'warning']; @endphp
                    <span class="label label-{{ $statusColors[$bundle->status] ?? 'default' }}">
                        {{ trans('marble::admin.bundle_status_' . $bundle->status) }}
                    </span>
                </div>
            </div>
            <div class="profile-box-content clearfix">
                <ul class="menu-items">
                    @if($bundle->status === 'draft' && $bundle->bundleItems->isNotEmpty())
                        <li>
                            <form method="POST" action="{{ route('marble.bundle.publish', $bundle) }}"
                                  onsubmit="return confirm('{{ trans('marble::admin.bundle_confirm_publish') }}')">
                                @csrf
                                <button type="submit" class="btn btn-xs btn-success">
                                    @include('marble::components.famicon', ['name' => 'tick']) {{ trans('marble::admin.bundle_publish') }}
                                </button>
                            </form>
                        </li>
                    @endif

                    @if($bundle->status === 'published')
                        <li>
                            <form method="POST" action="{{ route('marble.bundle.rollback', $bundle) }}"
                                  onsubmit="return confirm('{{ trans('marble::admin.bundle_confirm_rollback') }}')">
                                @csrf
                                <button type="submit" class="btn btn-xs btn-default">
                                    @include('marble::components.famicon', ['name' => 'arrow_left']) {{ trans('marble::admin.bundle_rollback') }}
                                </button>
                            </form>
                        </li>
                    @endif

                    <li>
                        <form method="POST" action="{{ route('marble.bundle.destroy', $bundle) }}"
                              onsubmit="return confirm('{{ trans('marble::admin.are_you_sure') }}')">
                            @csrf @method('DELETE')
                            <button type="submit" class="danger btn btn-xs">
                                @include('marble::components.famicon', ['name' => 'bin']) {{ trans('marble::admin.delete') }}
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="main-box clearfix profile-box-menu">
        <div class="main-box-body clearfix">
            <div class="profile-box-header gray-bg clearfix">
                <h2>{{ trans('marble::admin.meta_information') }}</h2>
            </div>
            <div class="profile-box-content clearfix marble-box-body">
                <table class="table table-condensed marble-mb-0">
                    <tr><td class="text-muted marble-text-sm">{{ trans('marble::admin.by') }}</td><td>{{ $bundle->creator?->name ?? '—' }}</td></tr>
                    <tr><td class="text-muted marble-text-sm">{{ trans('marble::admin.last_edited') }}</td><td>{{ $bundle->updated_at->diffForHumans() }}</td></tr>
                    @if($bundle->published_at)
                        <tr><td class="text-muted marble-text-sm">Published</td><td>{{ $bundle->published_at->diffForHumans() }}</td></tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
@endsection

@section('content')
<h1>{{ $bundle->name }}</h1>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if($bundle->description)
    <p class="text-muted">{{ $bundle->description }}</p>
@endif

<div class="main-box">
    <header class="main-box-header clearfix">
        <h2>@include('marble::components.famicon', ['name' => 'folder_page']) {{ trans('marble::admin.bundle_items') }} ({{ $bundle->items->count() }})</h2>
    </header>
    <div class="main-box-body clearfix">
        @if($bundle->items->isEmpty())
            <p class="text-muted marble-pad-md">{{ trans('marble::admin.bundle_no_items') }}</p>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ trans('marble::admin.name') }}</th>
                        <th>Blueprint</th>
                        <th>Status</th>
                        @if($bundle->status === 'published')
                            <th>{{ trans('marble::admin.bundle_rollback') }} Info</th>
                        @endif
                        <th class="marble-col-xs"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bundle->items as $item)
                    <tr>
                        <td>
                            <a href="{{ route('marble.item.edit', $item) }}" class="marble-link">{{ $item->name() ?: "#{$item->id}" }}</a>
                        </td>
                        <td class="text-muted marble-text-sm">{{ $item->blueprint?->name }}</td>
                        <td>
                            <span class="label label-{{ $item->status === 'published' ? 'success' : 'default' }}">
                                {{ $item->status }}
                            </span>
                        </td>
                        @if($bundle->status === 'published')
                            <td class="text-muted marble-text-sm">
                                Was: {{ $item->pivot->pre_publish_status ?? '—' }}
                                @if($item->pivot->pre_publish_revision_id)
                                    · snapshot #{{ $item->pivot->pre_publish_revision_id }}
                                @endif
                            </td>
                        @endif
                        <td>
                            @if($bundle->status !== 'published')
                                <form method="POST" action="{{ route('marble.bundle.remove-item', [$bundle, $item]) }}"
                                      onsubmit="return confirm('{{ trans('marble::admin.are_you_sure') }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger">
                                        @include('marble::components.famicon', ['name' => 'bin'])
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @if($bundle->status === 'draft')
            <div class="marble-box-body marble-border-top">
                <form method="POST" action="{{ route('marble.bundle.add-item', $bundle) }}" class="form-inline" id="bundle-add-item-form">
                    @csrf
                    <input type="hidden" name="item_id" id="bundle-item-id-input" />
                    <div style="display:flex;gap:8px;align-items:center;">
                        <input type="text" id="bundle-item-search" class="form-control input-sm"
                               placeholder="{{ trans('marble::admin.search_placeholder') }}"
                               autocomplete="off" style="min-width:240px;" />
                        <button type="submit" class="btn btn-success btn-sm" id="bundle-add-btn" disabled>
                            @include('marble::components.famicon', ['name' => 'add']) {{ trans('marble::admin.bundle_add_item') }}
                        </button>
                    </div>
                </form>
                <div id="bundle-item-results" style="display:none;background:#fff;border:1px solid #ddd;border-radius:4px;max-height:220px;overflow-y:auto;margin-top:4px;min-width:300px;"></div>
            </div>
        @endif
    </div>
</div>
@endsection

@section('javascript')
<script>
(function(){
    var searchUrl = '{{ route('marble.item.search') }}';
    var $search   = $('#bundle-item-search');
    var $idInput  = $('#bundle-item-id-input');
    var $addBtn   = $('#bundle-add-btn');
    var $results  = $('#bundle-item-results');

    if (!$search.length) return;

    var debounce;
    $search.on('input', function () {
        clearTimeout(debounce);
        var q = this.value.trim();
        if (q.length < 2) { $results.hide(); return; }
        debounce = setTimeout(function () {
            $.get(searchUrl, { q: q }, function (data) {
                $results.empty().show();
                if (!data.length) {
                    $results.append('<div style="padding:8px 12px;color:#888;font-size:13px;">No results</div>');
                    return;
                }
                data.forEach(function (item) {
                    $('<div>')
                        .css({ padding: '8px 12px', cursor: 'pointer', fontSize: '13px', borderBottom: '1px solid #f0f0f0' })
                        .html('<strong>' + $('<div>').text(item.name).html() + '</strong> <span style="color:#aaa;font-size:11px;">' + (item.blueprint || '') + '</span>')
                        .on('click', function () {
                            $search.val(item.name);
                            $idInput.val(item.id);
                            $addBtn.prop('disabled', false);
                            $results.hide();
                        })
                        .appendTo($results);
                });
            });
        }, 250);
    });

    $(document).on('click', function (e) {
        if (!$(e.target).closest('#bundle-item-search, #bundle-item-results').length) $results.hide();
    });
})();
</script>
@endsection
