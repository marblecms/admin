@php
    $subFields = $field->configuration['sub_fields'] ?? [];
    $rows = is_array($value) ? array_values($value) : [];
@endphp

<div id="repeater-{{ $field->id }}-{{ $languageId }}" class="marble-mb-sm">
    @if(empty($subFields))
        <p class="text-muted marble-text-sm">{{ trans('marble::admin.no_repeater_fields') }}</p>
    @else
        <table class="table table-condensed table-bordered marble-mb-sm">
            <thead>
                <tr>
                    @foreach($subFields as $sf)
                        <th class="marble-text-sm">{{ $sf['name'] }}</th>
                    @endforeach
                    <th class="marble-col-xs"></th>
                </tr>
            </thead>
            <tbody class="repeater-rows">
                @foreach($rows as $rowIdx => $row)
                    <tr>
                        @foreach($subFields as $sf)
                            <td>
                                <input type="text"
                                    name="fields[{{ $field->id }}][{{ $languageId }}][{{ $rowIdx }}][{{ $sf['identifier'] }}]"
                                    value="{{ $row[$sf['identifier']] ?? '' }}"
                                    class="form-control input-sm" />
                            </td>
                        @endforeach
                        <td>
                            <a href="javascript:;" class="repeater-remove-row btn btn-xs btn-danger">
                                &times;
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <a href="javascript:;" class="repeater-add-row btn btn-xs btn-info">
            + {{ trans('marble::admin.add_row') }}
        </a>
    @endif
</div>
<script>
;(function(){
    var container = $("#repeater-{{ $field->id }}-{{ $languageId }}");
    var subFields = @json($subFields);
    var fieldId = {{ $field->id }};
    var langId = {{ $languageId }};
    var rowCount = container.find(".repeater-rows tr").length;

    container.find(".repeater-add-row").click(function(){
        var cells = '';
        subFields.forEach(function(sf){
            cells += '<td><input type="text" name="fields[' + fieldId + '][' + langId + '][' + rowCount + '][' + sf.identifier + ']" value="" class="form-control input-sm" /></td>';
        });
        cells += '<td><a href="javascript:;" class="repeater-remove-row btn btn-xs btn-danger">&times;</a></td>';
        container.find(".repeater-rows").append('<tr>' + cells + '</tr>');
        rowCount++;
    });

    container.on("click", ".repeater-remove-row", function(){
        $(this).closest("tr").remove();
        // Renumber remaining rows so indices are sequential on submit
        container.find(".repeater-rows tr").each(function(idx){
            $(this).find("input").each(function(){
                var name = $(this).attr("name");
                $(this).attr("name", name.replace(/\[\d+\]\[/, '[' + idx + ']['));
            });
        });
        rowCount = container.find(".repeater-rows tr").length;
    });
})();
</script>
