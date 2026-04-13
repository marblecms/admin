<div class="attribute-container" id="attribute-images-{{ $field->id }}-{{ $languageId }}">
    <div class="attribute-images-view"></div>
    <div class="clearfix"></div>

    <input type="hidden" name="fields[{{ $field->id }}][{{ $languageId }}]" class="attribute-images-input" value="noop" />

    <div class="marble-flex-center marble-mt-xs">
        <input type="file" name="file_{{ $field->id }}_{{ $languageId }}" class="form-control marble-flex-1" value="" />
        <button type="button" class="btn btn-default btn-sm marble-images-library-picker"
                data-field="{{ $field->id }}" data-lang="{{ $languageId }}">
            @include('marble::components.famicon', ['name' => 'pictures'])
            {{ trans('marble::admin.from_library') }}
        </button>
    </div>
    <div id="marble-images-library-preview-{{ $field->id }}-{{ $languageId }}" class="marble-images-library-previews"></div>
    <input type="hidden" name="library_add[{{ $field->id }}][{{ $languageId }}]"
           id="marble-images-library-input-{{ $field->id }}-{{ $languageId }}" value="" />
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

    // Media library picker for images gallery
    $('body').on('click', '.marble-images-library-picker[data-field="{{ $field->id }}"][data-lang="{{ $languageId }}"]', function () {
        MarbleMedia.open(function (media) {
            var fieldId  = '{{ $field->id }}';
            var langId   = '{{ $languageId }}';
            var $input   = $('#marble-images-library-input-' + fieldId + '-' + langId);
            var $preview = $('#marble-images-library-preview-' + fieldId + '-' + langId);

            // Append to comma-separated list
            var current = $input.val();
            $input.val(current ? current + ',' + media.id : String(media.id));

            // Show thumbnail preview
            var thumb = media.thumbnail
                ? '<img src="' + media.thumbnail + '" class="marble-thumb-preview marble-mr-xs" />'
                : '<span class="marble-mr-xs">📄 ' + media.original_filename + '</span>';
            $preview.append('<div class="marble-images-library-item marble-mt-xs">' + thumb + '<small>' + media.original_filename + '</small></div>');
        }, { type: 'image' });
    });
</script>
