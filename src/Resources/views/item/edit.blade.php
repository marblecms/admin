@extends('marble::layouts.app')

@section('javascript-head')
    <script type="text/javascript" src="{{ asset('vendor/marble/assets/js/attributes/attributes.js') }}"></script>
@endsection

@section('javascript')
    @include('marble::item.partials.edit-scripts')
@endsection

@section('sidebar')

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="alert alert-success marble-mb-sm">{{ session('success') }}</div>
    @endif
    @if($errors->has('delete'))
        <div class="alert alert-danger marble-mb-sm">{{ $errors->first('delete') }}</div>
    @endif
    @if($errors->has('aliases'))
        <div class="alert alert-danger marble-mb-sm">{{ $errors->first('aliases') }}</div>
    @endif

    {{-- Lock warning --}}
    @if($lockedByOther)
        <div class="alert alert-warning marble-mb-sm">
            @include('marble::components.famicon', ['name' => 'lock'])
            <strong>{{ $lockUser->name }}</strong> {{ trans('marble::admin.lock_editing') }}
        </div>
    @endif

    @include('marble::item.partials.sidebar-status')
    @include('marble::item.partials.workflow-timeline')
    @include('marble::item.partials.sidebar-aliases')
    @include('marble::item.partials.sidebar-draft-preview')
    @include('marble::item.partials.sidebar-copy-language')
    @include('marble::item.partials.sidebar-scheduling')
    @include('marble::item.partials.sidebar-meta')
    @include('marble::item.partials.sidebar-mount-points')
    @include('marble::item.partials.sidebar-ab-test')
    @include('marble::item.partials.sidebar-bundles')
    @include('marble::item.partials.sidebar-used-by')
    @include('marble::item.partials.sidebar-revisions')

@endsection

@section('content')
    <h1>
        {{ $item->name() }}
        @if(config('marble.autosave', false))
            <small id="autosave-indicator" class="marble-hidden marble-meta marble-fw-normal marble-ml-md"></small>
        @endif
    </h1>

    @if($breadcrumb->count() > 1)
        <div class="marble-breadcrumb">
            @foreach($breadcrumb as $crumb)
                @if(!$loop->last)
                    <a href="{{ route('marble.item.edit', $crumb) }}" class="marble-link">{{ $crumb->name() ?: '—' }}</a>
                    <span class="marble-breadcrumb-sep">›</span>
                @else
                    <span class="text-muted">{{ $crumb->name() ?: '—' }}</span>
                @endif
            @endforeach
        </div>
    @endif

    @if($item->blueprint->is_form)
        <form id="marble-edit-form" action="{{ route('marble.item.save', $item) }}" enctype="multipart/form-data" method="post">
            @csrf
            <div class="main-box">
                <div class="main-box-body clearfix">
                    @foreach($groupedFields as $group)
                        @foreach($group['fields'] as $field)
                            @if(in_array($field->identifier, ['name', 'slug']))
                                @include('marble::item.edit_field', ['field' => $field, 'item' => $item, 'languages' => $languages])
                            @endif
                        @endforeach
                    @endforeach
                </div>
            </div>
            <div class="form-group pull-right marble-mb-md">
                <button type="submit" class="btn btn-success marble-save-btn">@include('marble::components.famicon', ['name' => 'disk']) {{ trans('marble::admin.save') }}</button>
            </div>
            <div class="clearfix"></div>
        </form>

        @include('marble::form.submissions-table', ['item' => $item, 'submissions' => $submissions])

    @elseif(!$item->blueprint->locked)

        <form id="marble-edit-form" action="{{ route('marble.item.save', $item) }}" enctype="multipart/form-data" method="post">
            @csrf

            @if($item->blueprint->tab_groups && count($groupedFields) > 1)
                {{-- Tab layout --}}
                @php $firstGroup = true; @endphp
                <div class="main-box">
                    <div class="marble-group-tab-container">
                        <div class="marble-group-tab-switcher">
                            @foreach($groupedFields as $group)
                                <div class="marble-group-tab {{ $loop->first ? 'active' : '' }}"
                                     data-group="{{ $loop->index }}">
                                    {{ $group['group'] ? $group['group']->name : trans('marble::admin.general') }}
                                </div>
                            @endforeach
                        </div>

                        @foreach($groupedFields as $group)
                            <div class="marble-group-panel {{ $loop->first ? 'active' : '' }}"
                                 data-group="{{ $loop->index }}">
                                <div class="main-box-body clearfix">
                                    @foreach($group['fields'] as $field)
                                        @continue($field->locked)
                                        @include('marble::item.edit_field', ['field' => $field, 'item' => $item, 'languages' => $languages])
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                {{-- Stacked boxes layout (default) --}}
                @foreach($groupedFields as $group)
                    <div class="main-box">
                        @if($group['group'])
                            <header class="main-box-header clearfix">
                                <h2><b>{{ $group['group']->name }}</b></h2>
                            </header>
                        @else
                            <br />
                        @endif

                        <div class="main-box-body clearfix">
                            @foreach($group['fields'] as $field)
                                @continue($field->locked)
                                @include('marble::item.edit_field', ['field' => $field, 'item' => $item, 'languages' => $languages])
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endif

            <div class="form-group pull-right">
                <button type="submit" class="btn btn-success marble-save-btn">@include('marble::components.famicon', ['name' => 'disk']) {{ trans('marble::admin.save') }}</button>
            </div>
            <div class="clearfix"></div>
            <br /><br /><br />
        </form>

    @endif

    @if($item->blueprint->inline_children && $childItems !== null)
        @include('marble::item.children-inline', ['item' => $item, 'childItems' => $childItems, 'languages' => $languages])
    @elseif($item->blueprint->list_children && $childItems)
        @include('marble::item.children', ['item' => $item, 'childItems' => $childItems])
    @endif

    @include('marble::item.partials.collaboration')

    @if(config('marble.autosave', false))
        <div id="marble-autosave-toast" class="toast-success"></div>
    @endif

@endsection
