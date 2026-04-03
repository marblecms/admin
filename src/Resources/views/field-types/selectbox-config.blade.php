@php $options = $field->configuration['options'] ?? []; @endphp

<div class="form-group">
    <label><b>Configuration</b></label>
    <div id="selectbox-config-{{ $field->id }}" class="marble-kv-panel">
        <div class="marble-kv-header">
            <div class="marble-kv-input-key">Key</div>
            <div class="marble-kv-input-value">Value</div>
        </div>
        <div class="rows">
            @foreach($options as $key => $row)
                <div class="marble-kv-row-pb">
                    <input type="text" name="configuration[{{ $field->id }}][{{ $key }}][key]" value="{{ $row['key'] ?? '' }}" class="form-control marble-kv-input-key" />
                    <input type="text" name="configuration[{{ $field->id }}][{{ $key }}][value]" value="{{ $row['value'] ?? '' }}" class="form-control marble-kv-input-value" />
                    <a href="javascript:;" class="cancel remove-row">&times;</a>
                </div>
            @endforeach
        </div>
        <br />
        <a href="javascript:;" class="add-row btn btn-primary">+ Add Row</a>
    </div>
</div>
<script>
    ;(function(){
        var container = $("#selectbox-config-{{ $field->id }}"),
            i = container.find(".rows > div").length - 1;

        container.find(".add-row").click(function(){
            i++;
            container.find(".rows").append(
                '<div class="marble-kv-row-pb">' +
                    '<input type="text" name="configuration[{{ $field->id }}][' + i + '][key]" value="" class="form-control marble-kv-input-key" /> ' +
                    '<input type="text" name="configuration[{{ $field->id }}][' + i + '][value]" value="" class="form-control marble-kv-input-value" /> ' +
                    '<a href="javascript:;" class="cancel remove-row">&times;</a>' +
                '</div>'
            );
        });

        container.on("click", ".remove-row", function(){
            $(this).parent().remove();
        });
    })();
</script>
