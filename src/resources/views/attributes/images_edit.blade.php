
<div class="attribute-container" id="attribute-images-{{$attribute->id}}-{{$locale}}">
    <div class="attribute-images-view"></div>
    <div class="clearfix"></div>

    <input type="hidden" name="attributes[{{$attribute->id}}][{{$locale}}]" class="attribute-images-input" value="noop" />
    <input type="file" name="file_{{$attribute->id}}_{{$locale}}" class="form-control" value="" />
</div>
<script>
    Attributes.ready(function(){

        var images = new Attributes.Images(
            "attribute-images-{{$attribute->id}}-{{$locale}}",
            {{$attribute->id}},
            "{{$locale}}"
        );

        @foreach($attribute->value[$locale] as $key => $imagesAttributeItem)
            images.addImage({
                id: '{{$key}}',
                filename: '{{url("/image/" . $imagesAttributeItem->filename)}}',
                thumbnailFilename: '{{url("/image/200/150/" . $imagesAttributeItem->filename)}}',
                originalFilename: '{{$imagesAttributeItem->originalFilename}}',
                transformations: {!! json_encode($imagesAttributeItem->transformations) !!}
            });
        @endforeach
        
    });
</script>