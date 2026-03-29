@php
    $items = [];
    if (is_array($value)) {
        foreach ($value as $id) {
            $relItem = \Marble\Admin\Models\Item::find((int)$id);
            if ($relItem) $items[] = $relItem;
        }
    }
@endphp

<div class="attribute-container" id="attribute-object-relation-list-{{ $field->id }}-{{ $languageId }}">
    <div class="attribute-object-relation-list-view"></div>
    <div class="clearfix"></div>

    <div class="attribute-object-relation-list-inputs"></div>
    <a href="javascript:;" class="btn btn-info btn-xs attribute-object-relation-list-add">{{ trans('marble::admin.select_object') }}</a>
</div>
<script>
    Attributes.ready(function(){
        var container = new Attributes.ObjectRelationList(
            "attribute-object-relation-list-{{ $field->id }}-{{ $languageId }}",
            'fields[{{ $field->id }}][{{ $languageId }}][]',
            {{ $field->id }},
            "{{ $languageId }}"
        );

        @foreach($items as $relItem)
            container.addNode({
                id: '{{ $relItem->id }}',
                name: '{{ $relItem->name() }}'
            });
        @endforeach
    });
</script>
