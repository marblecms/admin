<div class="attribute-container" id="attribute-image-{{ $field->id }}-{{ $languageId }}">
    <div class="attribute-image-view"></div>
    <div class="clearfix"></div>

    <input type="hidden" name="fields[{{ $field->id }}][{{ $languageId }}]" class="attribute-image-input" value="noop" />

    <div style="display:flex; gap:8px; align-items:center; margin-top:6px;">
        <input type="file" name="file_{{ $field->id }}_{{ $languageId }}" class="form-control" style="flex:1" />
        <button type="button" class="btn btn-default btn-sm media-library-picker"
                data-field="{{ $field->id }}" data-lang="{{ $languageId }}">
            @include('marble::components.famicon', ['name' => 'pictures'])
            {{ trans('marble::admin.from_library') }}
        </button>
    </div>
</div>
<script>
    Attributes.ready(function(){
        var image = new Attributes.Image(
            "attribute-image-{{ $field->id }}-{{ $languageId }}",
            {{ $field->id }},
            "{{ $languageId }}"
        );

        @php
            $filename = null;
            $originalFilename = '';
            $transformations = [];

            if ($value && is_array($value)) {
                if (isset($value['media_id'])) {
                    $media = \Marble\Admin\Models\Media::find($value['media_id']);
                    if ($media) {
                        $filename = $media->filename;
                        $originalFilename = $media->original_filename;
                    }
                } elseif (isset($value['filename'])) {
                    $filename = $value['filename'];
                    $originalFilename = $value['original_filename'] ?? '';
                }
                $transformations = $value['transformations'] ?? [];
            }
        @endphp

        @if($filename)
            image.setImage({
                filename: '{{ url("/image/" . $filename) }}',
                thumbnailFilename: '{{ url("/image/200/150/" . $filename) }}',
                originalFilename: '{{ addslashes($originalFilename) }}',
                transformations: {!! json_encode($transformations) !!}
            });
        @endif
    });

    // Media library picker for this field
    $('body').on('click', '.media-library-picker[data-field="{{ $field->id }}"][data-lang="{{ $languageId }}"]', function() {
        MarbleMedia.open(function(media) {
            var $container = $('#attribute-image-{{ $field->id }}-{{ $languageId }}');
            $container.find('.attribute-image-input').val('library:' + media.id);
            $container.find('.attribute-image-view').html(
                '<div style="margin:6px 0">' +
                '<img src="' + media.thumbnail + '" style="max-width:200px;max-height:150px;border:1px solid #ddd;" />' +
                '<br><small>' + media.original_filename + '</small>' +
                '</div>'
            );
        });
    });
</script>
