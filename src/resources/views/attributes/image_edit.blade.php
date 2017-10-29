
<div class="attribute-container" id="attribute-image-{{$attribute->id}}-{{$locale}}">
    <div class="attribute-image-view"></div>
    <div class="clearfix"></div>
    
    <input type="hidden" name="attributes[{{$attribute->id}}][{{$locale}}]" class="attribute-image-input" value="noop" />
    <input type="file" name="file_{{$attribute->id}}_{{$locale}}" class="form-control" value="" />
</div>
<script>
    Attributes.ready(function(){
    
        var image = new Attributes.Image(
            "attribute-image-{{$attribute->id}}-{{$locale}}",
            {{$attribute->id}},
            "{{$locale}}"
        );

        @if($attribute->value[$locale])
            image.setImage({
                filename: '{{url("/image/" . $attribute->value[$locale]->filename)}}',
                thumbnailFilename: '{{url("/image/200/150/" . $attribute->value[$locale]->filename)}}',
                originalFilename: '{{$attribute->value[$locale]->originalFilename}}',
                transformations: {!! json_encode($attribute->value[$locale]->transformations) !!}
            });
        @endif
    
    });
</script>
