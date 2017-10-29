<div class="attribute-container" id="attribute-object-relation-{{$attribute->id}}-{{$locale}}">
    <div class="attribute-object-relation-view"></div>
    <div class="clearfix"></div>
    
    <input type="hidden" name="attributes[{{$attribute->id}}][{{$locale}}]" class="attribute-object-relation-input" value="{{$attribute->value[$locale]}}" />
    <a href="javascript:;" class="btn btn-info btn-xs attribute-object-relation-add">{{trans("admin.select_object")}}</a>
    <a href="javascript:;" class="btn btn-primary btn-xs attribute-object-relation-create">{{trans("admin.create_object")}}</a>

</div>
<script>
    Attributes.ready(function(){

        var container = new Attributes.ObjectRelation("attribute-object-relation-{{$attribute->id}}-{{$locale}}");

        @if($attribute->processedValue[$locale])
            container.setNode({
                id: '{{$attribute->processedValue[$locale]->id}}',
                name: '{{$attribute->processedValue[$locale]->name}}'
            });
        @endif

    });
</script>
