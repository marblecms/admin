<div class="main-box" id="inline-children-box">
    <header class="main-box-header clearfix">
        <h2>
            <div class="pull-left">{{ trans('marble::admin.children') }}</div>
            @if($item->blueprint->allow_children)
                <div class="pull-right">
                    <button type="button" class="btn btn-info btn-xs" id="inline-add-child-btn">
                        @include('marble::components.famicon', ['name' => 'add']) {{ trans('marble::admin.add_children') }}
                    </button>
                </div>
            @endif
            <div class="clearfix"></div>
        </h2>
    </header>

    {{-- Quick-create form (hidden by default) --}}
    @if($item->blueprint->allow_children)
    <div id="inline-add-child-form" class="marble-inline-add-form marble-hidden">
        <form method="POST" action="{{ route('marble.item.create') }}">
            @csrf
            <input type="hidden" name="parent_id" value="{{ $item->id }}">
            <input type="hidden" name="_inline_parent_id" value="{{ $item->id }}">
            <div class="marble-inline-add-row">
                @php
                    $allowedBps = $item->blueprint->allowsAllChildren()
                        ? \Marble\Admin\Models\Blueprint::orderBy('name')->get()
                        : $item->blueprint->allowedChildBlueprints->sortBy('name');
                @endphp
                @if($allowedBps->count() === 1)
                    <input type="hidden" name="blueprint_id" value="{{ $allowedBps->first()->id }}">
                    <input type="text" name="name" class="form-control marble-inline-name-input"
                           placeholder="{{ trans('marble::admin.name') }}…" required>
                @else
                    <select name="blueprint_id" class="form-control marble-inline-bp-select">
                        @foreach($allowedBps as $bp)
                            <option value="{{ $bp->id }}">{{ $bp->name }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="name" class="form-control marble-inline-name-input"
                           placeholder="{{ trans('marble::admin.name') }}…" required>
                @endif
                <button type="submit" class="btn btn-success btn-sm">
                    @include('marble::components.famicon', ['name' => 'add']) {{ trans('marble::admin.create') }}
                </button>
                <button type="button" class="btn btn-default btn-sm" id="inline-add-child-cancel">
                    {{ trans('marble::admin.cancel') }}
                </button>
            </div>
        </form>
    </div>
    @endif

    <div class="main-box-body">
        @forelse($childItems as $child)
            @php
                $childGroupedFields = $child->blueprint->groupedFields();
            @endphp
            <div class="marble-inline-panel"
                 id="child-{{ $child->id }}">
                <div class="marble-inline-panel-header" data-toggle-panel="{{ $child->id }}">
                    <span class="marble-inline-toggle-icon">▸</span>
                    @if($child->blueprint->icon)
                        @include('marble::components.famicon', ['name' => $child->blueprint->icon])
                    @endif
                    <strong class="marble-inline-panel-name">{{ $child->name() ?: '(' . $child->blueprint->name . ')' }}</strong>
                    @if($child->status === 'draft')
                        <span class="label label-default marble-text-xs marble-ml-xs">{{ trans('marble::admin.draft') }}</span>
                    @endif
                    <span class="marble-inline-panel-actions" onclick="event.stopPropagation()">
                        <a href="{{ route('marble.item.edit', $child) }}" class="btn btn-xs btn-default" title="{{ trans('marble::admin.open_full') }}">
                            @include('marble::components.famicon', ['name' => 'application_go'])
                        </a>
                        <form method="POST" action="{{ route('marble.item.delete', $child) }}"
                              class="marble-inline-delete-form"
                              onsubmit="return confirm('{{ trans('marble::admin.are_you_sure') }}')">
                            @csrf @method('DELETE')
                            <input type="hidden" name="_inline_parent_id" value="{{ $item->id }}">
                            <button type="submit" class="btn btn-xs btn-danger" title="{{ trans('marble::admin.delete') }}">
                                @include('marble::components.famicon', ['name' => 'bin'])
                            </button>
                        </form>
                    </span>
                </div>

                <div class="marble-inline-panel-body marble-hidden">
                    <form method="POST" action="{{ route('marble.item.save', $child) }}"
                          enctype="multipart/form-data"
                          class="marble-inline-form">
                        @csrf
                        <input type="hidden" name="_inline_parent_id" value="{{ $item->id }}">

                        @if($child->blueprint->tab_groups && count($childGroupedFields) > 1)
                            <div class="marble-group-tab-container">
                                <div class="marble-group-tab-switcher">
                                    @foreach($childGroupedFields as $group)
                                        <div class="marble-group-tab {{ $loop->first ? 'active' : '' }}"
                                             data-group="{{ $child->id }}-{{ $loop->index }}">
                                            {{ $group['group'] ? $group['group']->name : trans('marble::admin.general') }}
                                        </div>
                                    @endforeach
                                </div>
                                @foreach($childGroupedFields as $group)
                                    <div class="marble-group-panel {{ $loop->first ? 'active' : '' }}"
                                         data-group="{{ $child->id }}-{{ $loop->index }}">
                                        @foreach($group['fields'] as $field)
                                            @continue($field->locked)
                                            @include('marble::item.edit_field', [
                                                'field'     => $field,
                                                'item'      => $child,
                                                'languages' => $languages,
                                            ])
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        @else
                            @foreach($childGroupedFields as $group)
                                @if($group['group'])
                                    <div class="marble-inline-group-label">{{ $group['group']->name }}</div>
                                @endif
                                @foreach($group['fields'] as $field)
                                    @continue($field->locked)
                                    @include('marble::item.edit_field', [
                                        'field'     => $field,
                                        'item'      => $child,
                                        'languages' => $languages,
                                    ])
                                @endforeach
                            @endforeach
                        @endif

                        <div class="marble-inline-save-row">
                            <button type="submit" class="btn btn-success btn-sm marble-save-btn">
                                @include('marble::components.famicon', ['name' => 'disk']) {{ trans('marble::admin.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @empty
            <p class="text-muted text-center marble-no-children-hint">{{ trans('marble::admin.no_children') }}</p>
        @endforelse
    </div>
</div>

<script>
(function () {
    // Toggle panels
    document.querySelectorAll('[data-toggle-panel]').forEach(function (header) {
        header.addEventListener('click', function () {
            var panel = this.closest('.marble-inline-panel');
            var body  = panel.querySelector('.marble-inline-panel-body');
            var icon  = panel.querySelector('.marble-inline-toggle-icon');
            var open  = panel.classList.toggle('marble-inline-panel-open');
            body.classList.toggle('marble-hidden', !open);
            icon.textContent = open ? '▾' : '▸';
        });
    });

    // Add child button
    var addBtn    = document.getElementById('inline-add-child-btn');
    var addForm   = document.getElementById('inline-add-child-form');
    var cancelBtn = document.getElementById('inline-add-child-cancel');
    if (addBtn && addForm) {
        addBtn.addEventListener('click', function () {
            addForm.classList.remove('marble-hidden');
            addForm.querySelector('input[name="name"]').focus();
        });
    }
    if (cancelBtn && addForm) {
        cancelBtn.addEventListener('click', function () {
            addForm.classList.add('marble-hidden');
        });
    }

    // Open panel from URL fragment (#child-123)
    var fragment = window.location.hash;
    if (fragment && fragment.startsWith('#child-')) {
        var target = document.getElementById(fragment.slice(1));
        if (target) {
            var body = target.querySelector('.marble-inline-panel-body');
            var icon = target.querySelector('.marble-inline-toggle-icon');
            target.classList.add('marble-inline-panel-open');
            if (body) body.classList.remove('marble-hidden');
            if (icon) icon.textContent = '▾';
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }
})();
</script>
