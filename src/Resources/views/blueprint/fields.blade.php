@extends('marble::layouts.app')

@php $prefix = config('marble.route_prefix', 'admin'); @endphp

@section('sidebar')
    <div class="main-box clearfix profile-box-menu">
        <div class="main-box-body clearfix">
            <div class="profile-box-header gray-bg clearfix" style="padding:0 15px 15px">
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
                            <form method="POST" action="{{ url("{$prefix}/blueprint/{$blueprint->id}/field-group/delete/{$group->id}") }}" style="display:inline" onsubmit="return confirm('{{ trans('marble::admin.are_you_sure') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn pull-right btn-danger btn-xs">@include('marble::components.famicon', ['name' => 'bin'])</button>
                            </form>
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
            <form action="{{ url("{$prefix}/blueprint/{$blueprint->id}/field/add") }}" method="post" style="display:inline">
                @csrf
                <button type="submit" class="btn btn-xs btn-success pull-right" style="margin-right:15px">@include('marble::components.famicon', ['name' => 'add']) {{ trans('marble::admin.add_class') }}</button>
                <select name="type" class="form-control pull-right" style="width: auto; margin-right: 30px">
                    @foreach($fieldTypes as $ft)
                        <option value="{{ $ft->id }}">{{ $ft->name }}</option>
                    @endforeach
                </select>
                <div class="clearfix"></div>
            </form>
        </div>
        <div class="clearfix"></div>
    </h1>

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
                        <button type="button" class="btn btn-danger" data-dismiss="modal">@include('marble::components.famicon', ['name' => 'cancel']) {{ trans('marble::admin.cancel') }}</button>
                        <button type="submit" class="btn btn-success">@include('marble::components.famicon', ['name' => 'disk']) {{ trans('marble::admin.save') }}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <form action="{{ url("{$prefix}/blueprint/{$blueprint->id}/field/save") }}" method="post" id="class-attributes">
        @csrf

        @foreach($groupedFields as $group)
            <div class="class-attribute-sortable">
                @foreach($group['fields'] as $field)
                    <div class="main-box" data-attribute-id="{{ $field->id }}" style="position: relative">
                        <input type="hidden" name="sort_order[{{ $field->id }}]" value="{{ $field->sort_order }}" class="input-sort-order"/>

                        <header class="main-box-header clearfix">
                            <h2><b>{{ $field->name }}</b> &lt; {{ $field->fieldType->name }} &gt;</h2>
                        </header>
                        <div class="main-box-body clearfix">
                            @if(!in_array($field->identifier, ['name', 'slug']))
                                <div style="position: absolute; top: 10px; right: 10px">
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

                            <div class="row">
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
                            <div class="row" style="margin-top:8px">
                                <div class="col-md-6">
                                    <div class="form-group" style="margin-bottom:4px">
                                        <label style="color:#777">{{ trans('marble::admin.validation_rules') }}</label>
                                        <input type="text" name="validation_rules[{{ $field->id }}]" value="{{ $field->validation_rules ?? '' }}" class="form-control input-sm" placeholder="e.g. required|max:255" />
                                    </div>
                                </div>
                                <div class="col-md-3" style="padding-top:20px">
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

        <div class="main-box">
            <div class="main-box-body clearfix">
                <div class="form-group">
                    <a class="btn btn-danger" href="{{ url("{$prefix}/dashboard") }}">@include('marble::components.famicon', ['name' => 'cancel']) {{ trans('marble::admin.cancel') }}</a>
                    <button type="submit" class="btn btn-success">@include('marble::components.famicon', ['name' => 'disk']) {{ trans('marble::admin.save') }}</button>
                </div>
            </div>
        </div>
    </form>

    {{-- Standalone delete form (outside save form to avoid nesting) --}}
    <form id="field-delete-form" method="POST" style="display:none">
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
