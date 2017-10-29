<div class="attribute-container" id="attribute-object-relation-list-{{$attribute->id}}-{{$locale}}">
    <div class="attribute-object-relation-list-view"></div>
    <div class="clearfix"></div>

    <div class="attribute-object-relation-list-inputs"></div>
    <a href="javascript:;" class="btn btn-info btn-xs attribute-object-relation-list-add">{{trans("admin::admin.select_object")}}</a>
    <a href="javascript:;" class="btn btn-primary btn-xs attribute-object-relation-list-create">{{trans("admin::admin.create_object")}}</a>

</div>
<script>
    Attributes.ready(function(){

        var container = new Attributes.ObjectRelationList(
            "attribute-object-relation-list-{{$attribute->id}}-{{$locale}}", 
            'attributes[{{$attribute->id}}][{{$locale}}][]',
            {{$attribute->id}},
            "{{$locale}}"
        );

        @foreach($attribute->processedValue[$locale] as $key => $subNode)
            @continue( ! $subNode )
            container.addNode({
                id: '{{$subNode->id}}',
                name: '{{$subNode->name}}'
            });
        @endforeach

    });
</script>

