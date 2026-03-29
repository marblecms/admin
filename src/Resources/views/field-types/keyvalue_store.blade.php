@php $rows = is_array($value) ? $value : []; @endphp

<div id="kv-store-{{ $field->id }}-{{ $languageId }}" style="background: #f4f4f4;padding: 15px;border-radius: 3px">
    <div style="width: 120px; display:inline-block;">Key</div>
    <div style="width: 300px; display:inline-block;">Value</div>
    <br />
    <div class="rows">
        @foreach($rows as $key => $row)
            <div style="padding-bottom: 3px">
                <input type="text" name="fields[{{ $field->id }}][{{ $languageId }}][{{ $key }}][key]" value="{{ $row['key'] ?? '' }}" class="form-control" style="display: inline-block; width:100px; margin-right:10px" />
                <input type="text" name="fields[{{ $field->id }}][{{ $languageId }}][{{ $key }}][value]" value="{{ $row['value'] ?? '' }}" class="form-control" style="display: inline-block; width:300px" />
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
                '<div style="padding-bottom: 3px">' +
                    '<input type="text" name="fields[{{ $field->id }}][{{ $languageId }}][' + i + '][key]" value="" class="form-control" style="display: inline-block; width:100px; margin-right:10px" /> ' +
                    '<input type="text" name="fields[{{ $field->id }}][{{ $languageId }}][' + i + '][value]" value="" class="form-control" style="display: inline-block; width:300px" /> ' +
                    '<a href="javascript:;" class="cancel remove-row">&times;</a>' +
                '</div>'
            );
        });

        container.on("click", ".remove-row", function(){
            $(this).parent().remove();
        });
    })();
</script>
