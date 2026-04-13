<div class="main-box">
    <header class="main-box-header clearfix">
        <h2>
            <div class="pull-left">{{ trans('marble::admin.children') }}</div>
            @if($item->blueprint->allow_children)
                <div class="pull-right">
                    <a href="{{ route('marble.item.add', $item) }}" class="btn btn-info btn-xs">
                        @include('marble::components.famicon', ['name' => 'add']) {{ trans('marble::admin.add_children') }}
                    </a>
                </div>
            @endif
            <div class="clearfix"></div>
        </h2>
    </header>
    <div class="main-box-body clearfix">
        @if(count($childItems))
        <div class="table-responsive">
            {{-- Hidden forms for btn-group actions --}}
            @foreach($childItems as $child)
                <form id="child-duplicate-{{ $child->id }}" method="POST" action="{{ route('marble.item.duplicate', $child) }}" class="marble-hidden">@csrf</form>
                <form id="child-status-{{ $child->id }}" method="POST" action="{{ route('marble.item.toggle-status', $child) }}" class="marble-hidden">@csrf</form>
                <form id="child-delete-{{ $child->id }}" method="POST" action="{{ route('marble.item.delete', $child) }}" class="marble-hidden" onsubmit="return confirm('{{ trans('marble::admin.are_you_sure') }}')">@csrf @method('DELETE')</form>
            @endforeach

            <table class="table table-striped" id="sortable-children">
                <tbody>
                    @foreach($childItems as $child)
                        <tr data-node-id="{{ $child->id }}" data-href="{{ route('marble.item.edit', $child) }}" class="marble-row-link">
                            <td>
                                @if($child->blueprint->icon)
                                    @include('marble::components.famicon', ['name' => $child->blueprint->icon])
                                @endif
                                {{ $child->name() }}
                                @if($child->status === 'draft')
                                    <span class="label label-default marble-text-xs marble-mr-xs">{{ trans('marble::admin.draft') }}</span>
                                @endif
                            </td>
                            <td class="text-right marble-cell-right" onclick="event.stopPropagation()">
                                <div class="btn-group btn-group-xs marble-btn-group-inline">
                                    <a href="{{ route('marble.item.edit', $child) }}" class="btn btn-xs btn-info">
                                        @include('marble::components.famicon', ['name' => 'pencil']) {{ trans('marble::admin.edit') }}
                                    </a>
                                    <button type="submit" form="child-duplicate-{{ $child->id }}" class="btn btn-xs btn-default">
                                        @include('marble::components.famicon', ['name' => 'page_white_copy']) {{ trans('marble::admin.duplicate') }}
                                    </button>
                                    <button type="submit" form="child-status-{{ $child->id }}" class="btn btn-xs btn-default">
                                        @include('marble::components.famicon', ['name' => $child->status === 'published' ? 'tick' : 'pencil'])
                                        {{ $child->status === 'published' ? trans('marble::admin.set_draft') : trans('marble::admin.set_published') }}
                                    </button>
                                    <button type="submit" form="child-delete-{{ $child->id }}" class="btn btn-xs btn-danger">
                                        @include('marble::components.famicon', ['name' => 'bin']) {{ trans('marble::admin.delete') }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <script>
                (function() {
                    var dragging = false;

                    $("#sortable-children tbody").sortable({
                        revert: true,
                        start: function() { dragging = true; },
                        stop: function() {
                            var items = {};
                            $("#sortable-children tbody > tr").each(function(i) {
                                items[$(this).data("node-id")] = i;
                            });
                            $.post("{{ route('marble.item.sort') }}", { items: items, _token: $('meta[name="csrf-token"]').attr('content') });
                            setTimeout(function() { dragging = false; }, 0);
                        }
                    });

                    $("#sortable-children").on("click", "tr.marble-row-link", function(e) {
                        if (dragging) return;
                        window.location = $(this).data("href");
                    });
                })();
            </script>
        </div>
        @else
            <p class="text-muted text-center marble-no-children-hint">{{ trans('marble::admin.no_children') }}</p>
        @endif
    </div>
</div>
