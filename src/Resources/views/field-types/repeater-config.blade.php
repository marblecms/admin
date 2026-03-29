@php $subFields = $field->configuration['sub_fields'] ?? []; @endphp

<div class="form-group">
    <label><b>{{ trans('marble::admin.repeater_fields') }}</b></label>
    <div id="repeater-config-{{ $field->id }}" style="background:#f4f4f4;padding:15px;border-radius:3px">
        <div style="display:flex;gap:8px;margin-bottom:4px;font-size:11px;color:#777;font-weight:bold">
            <div style="flex:1">Name</div>
            <div style="flex:1">Identifier</div>
            <div style="width:24px"></div>
        </div>
        <div class="rows">
            @foreach($subFields as $i => $sf)
                <div style="display:flex;gap:8px;margin-bottom:4px">
                    <input type="text" name="configuration[{{ $field->id }}][sub_fields][{{ $i }}][name]" value="{{ $sf['name'] ?? '' }}" class="form-control" style="flex:1" placeholder="Label" />
                    <input type="text" name="configuration[{{ $field->id }}][sub_fields][{{ $i }}][identifier]" value="{{ $sf['identifier'] ?? '' }}" class="form-control" style="flex:1" placeholder="identifier" />
                    <a href="javascript:;" class="remove-row" style="color:#c0392b;line-height:34px;font-size:18px">&times;</a>
                </div>
            @endforeach
        </div>
        <a href="javascript:;" class="add-row btn btn-info btn-xs" style="margin-top:8px">+ {{ trans('marble::admin.add_row') }}</a>
    </div>
</div>
<script>
;(function(){
    var container = $("#repeater-config-{{ $field->id }}"),
        i = container.find(".rows > div").length;

    container.find(".add-row").click(function(){
        container.find(".rows").append(
            '<div style="display:flex;gap:8px;margin-bottom:4px">' +
                '<input type="text" name="configuration[{{ $field->id }}][sub_fields][' + i + '][name]" value="" class="form-control" style="flex:1" placeholder="Label" />' +
                '<input type="text" name="configuration[{{ $field->id }}][sub_fields][' + i + '][identifier]" value="" class="form-control" style="flex:1" placeholder="identifier" />' +
                '<a href="javascript:;" class="remove-row" style="color:#c0392b;line-height:34px;font-size:18px">&times;</a>' +
            '</div>'
        );
        i++;
    });

    container.on("click", ".remove-row", function(){
        $(this).closest("div").remove();
    });
})();
</script>
