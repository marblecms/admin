@php $subFields = $field->configuration['sub_fields'] ?? []; @endphp

<div class="form-group">
    <label><b>{{ trans('marble::admin.repeater_fields') }}</b></label>
    <div id="repeater-config-{{ $field->id }}" class="marble-kv-panel">
        <div class="marble-kv-header">
            <div class="marble-flex-1">Name</div>
            <div class="marble-flex-1">Identifier</div>
            <div class="marble-col-xxs"></div>
        </div>
        <div class="rows">
            @foreach($subFields as $i => $sf)
                <div class="marble-kv-row">
                    <input type="text" name="configuration[{{ $field->id }}][sub_fields][{{ $i }}][name]" value="{{ $sf['name'] ?? '' }}" class="form-control marble-flex-1" placeholder="Label" />
                    <input type="text" name="configuration[{{ $field->id }}][sub_fields][{{ $i }}][identifier]" value="{{ $sf['identifier'] ?? '' }}" class="form-control marble-flex-1" placeholder="identifier" />
                    <a href="javascript:;" class="remove-row marble-remove-row">&times;</a>
                </div>
            @endforeach
        </div>
        <a href="javascript:;" class="add-row btn btn-info btn-xs marble-mt-sm">+ {{ trans('marble::admin.add_row') }}</a>
    </div>
</div>
<script>
;(function(){
    var container = $("#repeater-config-{{ $field->id }}"),
        i = container.find(".rows > div").length;

    container.find(".add-row").click(function(){
        container.find(".rows").append(
            '<div class="marble-kv-row">' +
                '<input type="text" name="configuration[{{ $field->id }}][sub_fields][' + i + '][name]" value="" class="form-control marble-flex-1" placeholder="Label" />' +
                '<input type="text" name="configuration[{{ $field->id }}][sub_fields][' + i + '][identifier]" value="" class="form-control marble-flex-1" placeholder="identifier" />' +
                '<a href="javascript:;" class="remove-row marble-remove-row">&times;</a>' +
            '</div>'
        );
        i++;
    });

    container.on("click", ".remove-row", function(){
        $(this).closest("div").remove();
    });
})();
</script>
