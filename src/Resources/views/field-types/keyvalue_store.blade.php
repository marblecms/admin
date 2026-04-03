@php $rows = is_array($value) ? $value : []; @endphp

<div id="kv-store-{{ $field->id }}-{{ $languageId }}" class="marble-kv-panel">
    <div class="marble-kv-header">
        <div class="marble-kv-input-key">Key</div>
        <div class="marble-kv-input-value">Value</div>
    </div>
    <div class="rows">
        @foreach($rows as $key => $row)
            <div class="marble-kv-row-pb">
                <input type="text" name="fields[{{ $field->id }}][{{ $languageId }}][{{ $key }}][key]" value="{{ $row['key'] ?? '' }}" class="form-control marble-kv-input-key" />
                <input type="text" name="fields[{{ $field->id }}][{{ $languageId }}][{{ $key }}][value]" value="{{ $row['value'] ?? '' }}" class="form-control marble-kv-input-value" />
                <a href="javascript:;" class="cancel remove-row">&times;</a>
            </div>
        @endforeach
    </div>
    <br />
    <a href="javascript:;" class="add-row btn btn-info btn-xs">+ Add Row</a>
</div>
<script>
    ;(function(){
        var container = $("#kv-store-{{ $field->id }}-{{ $languageId }}"),
            i = container.find(".rows > div").length - 1;

        container.find(".add-row").click(function(){
            i++;
            container.find(".rows").append(
                '<div class="marble-kv-row-pb">' +
                    '<input type="text" name="fields[{{ $field->id }}][{{ $languageId }}][' + i + '][key]" value="" class="form-control marble-kv-input-key" /> ' +
                    '<input type="text" name="fields[{{ $field->id }}][{{ $languageId }}][' + i + '][value]" value="" class="form-control marble-kv-input-value" /> ' +
                    '<a href="javascript:;" class="cancel remove-row">&times;</a>' +
                '</div>'
            );
        });

        container.on("click", ".remove-row", function(){
            $(this).parent().remove();
        });
    })();
</script>
