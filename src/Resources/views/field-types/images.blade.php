<div class="attribute-container" id="attribute-images-{{ $field->id }}-{{ $languageId }}">
    <div class="attribute-images-view"></div>
    <div class="clearfix"></div>

    <input type="hidden" name="fields[{{ $field->id }}][{{ $languageId }}]" class="attribute-images-input" value="noop" />
    <input type="file" name="file_{{ $field->id }}_{{ $languageId }}" class="form-control" value="" />
</div>
<script>
    Attributes.ready(function(){
        var images = new Attributes.Images(
            "attribute-images-{{ $field->id }}-{{ $languageId }}",
            {{ $field->id }},
            "{{ $languageId }}"
        );

        @if(is_array($value))
            @foreach($value as $key => $img)
                images.addImage({
                    id: '{{ $key }}',
                    filename: '{{ url("/image/" . ($img["filename"] ?? "")) }}',
                    thumbnailFilename: '{{ url("/image/200/150/" . ($img["filename"] ?? "")) }}',
                    originalFilename: '{{ $img["original_filename"] ?? "" }}',
                    transformations: {!! json_encode($img['transformations'] ?? []) !!}
                });
            @endforeach
        @endif
    });
</script>
