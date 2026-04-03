@extends('marble::layouts.app')

@section('content')
    <h1>{{ $workflow->name }}</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('marble.workflow.save', $workflow) }}" id="workflow-form">
        @csrf

        {{-- General --}}
        <div class="main-box">
            <header class="main-box-header clearfix">
                <h2>{{ trans('marble::admin.workflow_general') }}</h2>
            </header>
            <div class="main-box-body clearfix">
                <div class="form-group">
                    <label>{{ trans('marble::admin.name') }}</label>
                    <input type="text" name="name" value="{{ $workflow->name }}" class="form-control" required />
                </div>
            </div>
        </div>

        {{-- Steps --}}
        <div class="main-box">
            <header class="main-box-header clearfix">
                <h2>{{ trans('marble::admin.workflow_steps') }}</h2>
            </header>
            <div class="main-box-body clearfix">
                <p class="text-muted marble-mb-md">{{ trans('marble::admin.workflow_steps_hint') }}</p>

                <div id="steps-list">
                    @foreach($workflow->steps as $step)
                        @include('marble::workflow.partials.step-row', [
                            'step'      => $step,
                            'idx'       => $loop->index,
                            'allSteps'  => $workflow->steps,
                            'allUsers'  => $allUsers,
                            'allGroups' => $allGroups,
                        ])
                    @endforeach
                </div>

                {{-- Terminal published step (always shown, greyed out) --}}
                <div class="marble-step-terminal">
                    <span class="marble-step-handle-ghost">&#9776;</span>
                    <input type="text" class="form-control marble-flex-1" value="{{ trans('marble::admin.published') }}" disabled />
                    <div class="marble-step-spacer"></div>
                </div>
                <small class="text-muted">{{ trans('marble::admin.workflow_published_hint') }}</small>

                <div class="marble-mt-md">
                    <button type="button" class="btn btn-default btn-sm" id="add-step">
                        @include('marble::components.famicon', ['name' => 'add']) {{ trans('marble::admin.workflow_add_step') }}
                    </button>
                </div>
            </div>
        </div>

        <div class="form-group pull-right">
            <button type="submit" class="btn btn-success">
                @include('marble::components.famicon', ['name' => 'disk']) {{ trans('marble::admin.save') }}
            </button>
        </div>
    </form>

    <form method="POST" action="{{ route('marble.workflow.delete', $workflow) }}" class="marble-mt-xxl"
          onsubmit="return confirm('{{ trans('marble::admin.confirm_delete') }}')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-xs btn-danger">
            @include('marble::components.famicon', ['name' => 'bin']) {{ trans('marble::admin.delete') }} {{ trans('marble::admin.workflow') }}
        </button>
    </form>

    {{-- Template for new step rows (rendered server-side as JSON) --}}
    <script id="step-row-template" type="text/x-template">
        @include('marble::workflow.partials.step-row', [
            'step'      => null,
            'idx'       => '__IDX__',
            'allSteps'  => $workflow->steps,
            'allUsers'  => $allUsers,
            'allGroups' => $allGroups,
        ])
    </script>

    <script>
    (function () {
        var $list = $('#steps-list');

        function reindex() {
            $list.find('.step-row').each(function (i) {
                $(this).find('[data-step-field]').each(function () {
                    var field = $(this).attr('data-step-field');
                    $(this).attr('name', 'steps[' + i + '][' + field + ']');
                });
                // notifiables
                $(this).find('.notifiable-row').each(function (j) {
                    $(this).find('[data-notifiable-field]').each(function () {
                        var field = $(this).attr('data-notifiable-field');
                        $(this).attr('name', 'steps[' + i + '][notifiables][' + j + '][' + field + ']');
                    });
                });
            });
        }

        $list.sortable({ handle: '.step-handle', update: reindex });

        $('#add-step').on('click', function () {
            var idx = $list.find('.step-row').length;
            var html = $('#step-row-template').html().replace(/__IDX__/g, idx);
            $list.append(html);
            reindex();
        });

        $(document).on('click', '.remove-step', function () {
            $(this).closest('.step-row').remove();
            reindex();
        });

        $(document).on('click', '.toggle-step-config', function () {
            var $panel = $(this).closest('.step-row').find('.step-config-panel');
            var open   = $panel.is(':visible');
            $panel.slideToggle(150);
            $(this).find('span').text(open ? '▸' : '▾');
        });

        $(document).on('click', '.add-notifiable', function () {
            var $row     = $(this).closest('.step-row');
            var stepIdx  = $row.index();
            var $nList   = $row.find('.notifiables-list');
            var nIdx     = $nList.find('.notifiable-row').length;
            var $n = $('<div class="notifiable-row marble-notifiable-row">' +
                '<select class="form-control input-sm marble-input-num-xs" data-notifiable-field="type">' +
                '<option value="user">{{ trans('marble::admin.user') }}</option>' +
                '<option value="group">{{ trans('marble::admin.usergroup_singular') }}</option>' +
                '</select>' +
                '<select class="form-control input-sm notifiable-id-select marble-flex-1" data-notifiable-field="id"></select>' +
                '<select class="form-control input-sm marble-input-num-sm" data-notifiable-field="channel">' +
                '<option value="cms">CMS</option>' +
                '<option value="email">Email</option>' +
                '<option value="both">Both</option>' +
                '</select>' +
                '<button type="button" class="btn btn-xs btn-danger remove-notifiable">✕</button>' +
                '</div>');
            $nList.append($n);
            refreshNotifiableSelect($n.find('.notifiable-id-select'), 'user');
            reindex();
        });

        $(document).on('change', '[data-notifiable-field="type"]', function () {
            var type = $(this).val();
            var $sel = $(this).closest('.notifiable-row').find('.notifiable-id-select');
            refreshNotifiableSelect($sel, type);
        });

        $(document).on('change', '.reject-toggle', function () {
            $(this).closest('.step-config-panel').find('.reject-options').toggle(this.checked);
        });

        $(document).on('click', '.remove-notifiable', function () {
            $(this).closest('.notifiable-row').remove();
            reindex();
        });

        var usersData  = @json($allUsers->map(fn($u) => ['id' => $u->id, 'name' => $u->name]));
        var groupsData = @json($allGroups->map(fn($g) => ['id' => $g->id, 'name' => $g->name]));

        function refreshNotifiableSelect($sel, type) {
            var items = type === 'group' ? groupsData : usersData;
            $sel.empty();
            items.forEach(function (item) {
                $sel.append($('<option>').val(item.id).text(item.name));
            });
        }
    })();
    </script>
@endsection
