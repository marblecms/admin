@extends('marble::layouts.app')

@php $prefix = config('marble.route_prefix', 'admin'); @endphp

@section('sidebar')
    <div class="main-box clearfix profile-box-menu">
        <div class="main-box-body clearfix">
            <div class="profile-box-header gray-bg clearfix">
                <h2>{{ trans('marble::admin.attributegroups') }}</h2>
                <div class="job-position">{{ $blueprint->name }}</div>
            </div>
            <div class="profile-box-content clearfix">
                <ul class="menu-items" id="class-attribute-groups">
                    @foreach($fieldGroups as $group)
                        <li class="more" data-group-id="{{ $group->id }}">
                            <div class="pull-left">
                                @include('marble::components.famicon', ['name' => 'folder']) {{ $group->name }}
                            </div>
                            <form method="POST" action="{{ url("{$prefix}/blueprint/{$blueprint->id}/field-group/delete/{$group->id}") }}" class="marble-inline-form" onsubmit="return confirm('{{ trans('marble::admin.are_you_sure') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn pull-right btn-danger btn-xs">@include('marble::components.famicon', ['name' => 'bin'])</button>
                            </form>
                            <button type="button" class="btn pull-right btn-default btn-xs marble-mr-xs"
                                onclick="$('#rename-group-id').val('{{ $group->id }}');$('#rename-group-name').val('{{ addslashes($group->name) }}');$('#rename-field-group-modal').modal('show')">
                                @include('marble::components.famicon', ['name' => 'pencil'])
                            </button>
                            <div class="clearfix"></div>
                        </li>
                    @endforeach
                </ul>
                <ul class="menu-items">
                    <li>
                        <a href="javascript:$('#add-field-group-modal').modal('show')" class="clearfix">
                            @include('marble::components.famicon', ['name' => 'add']) {{ trans('marble::admin.add_group') }}
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        $("#class-attribute-groups").sortable({
            revert: true,
            stop: function() {
                var groups = {}, i = 0;
                $("#class-attribute-groups > li").each(function() {
                    groups[$(this).data("group-id")] = i++;
                });
                $.post("/{{ $prefix }}/blueprint/{{ $blueprint->id }}/field-group/sort", { groups: groups });
            }
        });
    </script>
@endsection

@section('content')
    <h1>
        <span class="pull-left">{{ $blueprint->name }}</span>
        <div class="pull-right">
            <form action="{{ url("{$prefix}/blueprint/{$blueprint->id}/field/add") }}" method="post" class="marble-inline-form">
                @csrf
                <button type="submit" class="btn btn-lg btn-success pull-right">@include('marble::components.famicon', ['name' => 'add']) {{ trans('marble::admin.add_class') }}</button>
                <select name="type" class="form-control pull-right marble-type-select">
                    @foreach($fieldTypes as $ft)
                        <option value="{{ $ft->id }}">{{ $ft->name }}</option>
                    @endforeach
                </select>
                <div class="clearfix"></div>
            </form>
        </div>
        <div class="clearfix"></div>
    </h1>

    <form action="{{ url("{$prefix}/blueprint/{$blueprint->id}/field/save") }}" method="post" id="class-attributes">
        @csrf

        @foreach($groupedFields as $group)
            <div class="class-attribute-sortable">
                @foreach($group['fields'] as $field)
                    <div class="main-box marble-relative" data-attribute-id="{{ $field->id }}">
                        <input type="hidden" name="sort_order[{{ $field->id }}]" value="{{ $field->sort_order }}" class="input-sort-order"/>

                        <header class="main-box-header clearfix">
                            <h2><b>{{ $field->name }}</b> &lt; {{ $field->fieldType->name }} &gt;</h2>
                        </header>
                        <div class="main-box-body clearfix">
                            @if($field->identifier !== 'name')
                                <div class="marble-field-delete-btn">
                                    <button type="button" class="btn btn-xs btn-danger"
                                        onclick="marbleDeleteField('{{ url("{$prefix}/blueprint/{$blueprint->id}/field/delete/{$field->id}") }}')">
                                        @include('marble::components.famicon', ['name' => 'bin']) {{ trans('marble::admin.delete') }}
                                    </button>
                                </div>
                            @endif

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ trans('marble::admin.name') }}</label>
                                        <input type="text" name="name[{{ $field->id }}]" value="{{ $field->name }}" class="form-control"/>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ trans('marble::admin.identifier') }}</label>
                                        <input type="text" name="identifier[{{ $field->id }}]" value="{{ $field->identifier }}" class="form-control"/>
                                    </div>
                                </div>
                            </div>

                            <div class="row form-group">
                                <div class="col-md-3">
                                    <label>
                                        <input type="checkbox" name="translatable[{{ $field->id }}]" value="1" {{ $field->translatable ? 'checked' : '' }} /> Translatable
                                    </label>
                                </div>
                                <div class="col-md-3">
                                    <label>
                                        <input type="checkbox" name="locked[{{ $field->id }}]" value="1" {{ $field->locked ? 'checked' : '' }} /> Locked
                                    </label>
                                </div>
                            </div>
                            <div class="row form-group marble-mt-sm">
                                <div class="col-md-6">
                                    <div class="form-group marble-mb-xs">
                                        <label class="text-muted">{{ trans('marble::admin.validation_rules') }}</label>
                                        <div class="input-group">
                                            <input type="text" name="validation_rules[{{ $field->id }}]" id="vr-input-{{ $field->id }}" value="{{ $field->validation_rules ?? '' }}" class="form-control" placeholder="e.g. required|max:255" />
                                            <div class="input-group-btn">
                                                <button type="button" class="btn btn-default dropdown-toggle marble-btn-rules" data-toggle="dropdown">+ <span class="caret"></span></button>
                                                <ul class="dropdown-menu dropdown-menu-right marble-rules-menu">
                                                    @foreach(['required','nullable','string','integer','numeric','email','url','min:1','max:255','unique:items'] as $rule)
                                                        <li><a href="#" onclick="event.preventDefault();(function(r,id){var inp=document.getElementById('vr-input-'+id);inp.value=inp.value?(inp.value+'|'+r):r;}('{{ $rule }}','{{ $field->id }}'))">{{ $rule }}</a></li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 marble-pt-lg">
                                    <select name="group_id[{{ $field->id }}]" class="form-control">
                                        <option value="0">{{ trans('marble::admin.no_group') }}</option>
                                        @foreach($fieldGroups as $fg)
                                            <option value="{{ $fg->id }}" {{ $field->blueprint_field_group_id == $fg->id ? 'selected' : '' }}>{{ $fg->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            @php $ftInstance = $field->fieldTypeInstance(); @endphp
                            @if($ftInstance->configComponent())
                                @include($ftInstance->configComponent(), ['field' => $field])
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach

        <script>
            $(".class-attribute-sortable").sortable({
                revert: true,
                stop: function() {
                    $(".class-attribute-sortable").each(function() {
                        var i = 0;
                        $(this).find(".input-sort-order").each(function() {
                            $(this).val(i++);
                        });
                    });
                }
            });
        </script>

        <div class="clearfix">
            <div class="form-group pull-right">
                <button type="submit" class="btn btn-success">@include('marble::components.famicon', ['name' => 'disk']) {{ trans('marble::admin.save') }}</button>
            </div>
        </div>
    </form>

    {{-- Standalone delete form (outside save form to avoid nesting) --}}
    <form id="field-delete-form" method="POST" class="marble-hidden">
        @csrf
        @method('DELETE')
    </form>
    <script>
        function marbleDeleteField(url) {
            if (!confirm('{{ trans('marble::admin.are_you_sure') }}')) return;
            var f = document.getElementById('field-delete-form');
            f.action = url;
            f.submit();
        }
    </script>
@endsection

@push('modals')
    {{-- Rename Field Group Modal --}}
    <div class="modal fade" id="rename-field-group-modal">
        <form action="{{ url("{$prefix}/blueprint/{$blueprint->id}/field-group/save") }}" method="post">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                        <h4 class="modal-title">{{ trans('marble::admin.rename_group') }}</h4>
                    </div>
                    <div class="modal-body">
                        @csrf
                        <input type="hidden" name="id" id="rename-group-id" value="" />
                        <div class="form-group">
                            <label>{{ trans('marble::admin.name') }}</label>
                            <input type="text" class="form-control" name="name" id="rename-group-name" />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('marble::admin.cancel') }}</button>
                        <button type="submit" class="btn btn-success">@include('marble::components.famicon', ['name' => 'disk']) {{ trans('marble::admin.save') }}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Add Field Group Modal --}}
    <div class="modal fade" id="add-field-group-modal">
        <form action="{{ url("{$prefix}/blueprint/{$blueprint->id}/field-group/add") }}" method="post">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                        <h4 class="modal-title">{{ trans('marble::admin.add_group') }}</h4>
                    </div>
                    <div class="modal-body">
                        @csrf
                        <div class="form-group">
                            <label>{{ trans('marble::admin.name') }}</label>
                            <input type="text" class="form-control" name="name" />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('marble::admin.cancel') }}</button>
                        <button type="submit" class="btn btn-success">@include('marble::components.famicon', ['name' => 'disk']) {{ trans('marble::admin.save') }}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endpush
