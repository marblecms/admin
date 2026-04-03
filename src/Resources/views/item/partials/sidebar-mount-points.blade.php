@if($item->blueprint->allow_children || $mountPoints->isNotEmpty())
<div class="main-box clearfix profile-box-menu">
    <div class="main-box-body clearfix">
        <div class="profile-box-header gray-bg clearfix">
            <h2>{{ trans('marble::admin.mount_points') }}</h2>
        </div>
        <div class="profile-box-content clearfix marble-box-body">
            @if($mountPoints->isEmpty())
                <p class="text-muted marble-text-sm marble-mb-sm">{{ trans('marble::admin.mount_points_hint') }}</p>
            @else
                @foreach($mountPoints as $mount)
                    <div class="marble-route-row">
                        <span class="marble-flex-1">
                            🔗
                            @if($mount->mountParent)
                                <a href="{{ route('marble.item.edit', $mount->mountParent) }}">{{ $mount->mountParent->name() }}</a>
                            @else
                                <span class="text-muted">–</span>
                            @endif
                        </span>
                        <form method="POST" action="{{ route('marble.item.mount.destroy', [$item, $mount]) }}">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-xs btn-danger" title="{{ trans('marble::admin.mount_remove') }}">✕</button>
                        </form>
                    </div>
                @endforeach
            @endif

            <div class="marble-mt-sm">
                <input type="hidden" id="new-mount-parent-id" value="" />
                <input type="text" id="new-mount-parent-name" class="form-control input-sm marble-picker-input"
                       placeholder="{{ trans('marble::admin.mount_select_parent') }}"
                       readonly
                       onclick="ObjectBrowser.open(function(node){ document.getElementById('new-mount-parent-id').value=node.id; document.getElementById('new-mount-parent-name').value=node.name; })" />
                <form method="POST" action="{{ route('marble.item.mount.store', $item) }}" id="add-mount-form" class="marble-mt-xs">
                    @csrf
                    <input type="hidden" name="mount_parent_id" id="add-mount-parent-hidden" value="" />
                    <button type="button" class="btn btn-xs btn-default" onclick="
                        var id = document.getElementById('new-mount-parent-id').value;
                        if (!id) return;
                        document.getElementById('add-mount-parent-hidden').value = id;
                        document.getElementById('add-mount-form').submit();
                    ">
                        @include('marble::components.famicon', ['name' => 'add']) {{ trans('marble::admin.mount_add') }}
                    </button>
                </form>
                @error('mount') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
        </div>
    </div>
</div>
@endif
