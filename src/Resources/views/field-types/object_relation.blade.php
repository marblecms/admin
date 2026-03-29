@php $processedValue = $value ? \Marble\Admin\Models\Item::find((int)$value) : null; @endphp

<div class="attribute-container" id="attribute-object-relation-{{ $field->id }}-{{ $languageId }}">
    <div class="attribute-object-relation-view"></div>
    <div class="clearfix"></div>

    <input type="hidden" name="fields[{{ $field->id }}][{{ $languageId }}]" class="attribute-object-relation-input" value="{{ $value }}" />
    <a href="javascript:;" class="btn btn-info btn-xs attribute-object-relation-add">{{ trans('marble::admin.select_object') }}</a>
</div>
<script>
    Attributes.ready(function(){
        var container = new Attributes.ObjectRelation("attribute-object-relation-{{ $field->id }}-{{ $languageId }}");

        @if($processedValue)
            container.setNode({
                id: '{{ $processedValue->id }}',
                name: '{{ $processedValue->name() }}'
            });
        @endif
    });
</script>
