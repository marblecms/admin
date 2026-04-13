<div class="attribute-container" id="attribute-file-{{ $field->id }}-{{ $languageId }}">
    <input type="hidden"
           name="fields[{{ $field->id }}][{{ $languageId }}]"
           class="attribute-file-input"
           value="noop" />

    @php
        $currentFile = null;
        if ($value && is_array($value) && !empty($value['filename'])) {
            $currentFile = $value;
        }
    @endphp

    @if($currentFile)
    <div class="attribute-file-current marble-file-current">
        @include('marble::components.famicon', ['name' => 'page_white'])
        <span class="attribute-file-name">{{ $currentFile['original_filename'] }}</span>
        <small class="text-muted">({{ number_format(($currentFile['size'] ?? 0) / 1024, 1) }} KB)</small>
        <button type="button"
                class="btn btn-default btn-xs attribute-file-remove marble-ml-auto"
                onclick="
                    document.querySelector('#attribute-file-{{ $field->id }}-{{ $languageId }} .attribute-file-input').value = 'remove';
                    this.closest('.attribute-file-current').remove();
                ">
            @include('marble::components.famicon', ['name' => 'cancel'])
            {{ trans('marble::admin.remove') }}
        </button>
    </div>
    @endif

    <div class="marble-flex-center marble-mt-xs">
        <input type="file"
               name="file_{{ $field->id }}_{{ $languageId }}"
               class="form-control marble-flex-1" />
        <button type="button" class="btn btn-default btn-sm marble-file-library-picker"
                data-field="{{ $field->id }}" data-lang="{{ $languageId }}">
            @include('marble::components.famicon', ['name' => 'folder'])
            {{ trans('marble::admin.from_library') }}
        </button>
    </div>

    @php
        $allowedFiletypes = trim($field->configuration['allowed_filetypes'] ?? '');
    @endphp
    @if($allowedFiletypes)
        <small class="text-muted">{{ trans('marble::admin.allowed_filetypes') }}: {{ $allowedFiletypes }}</small>
    @endif
</div>

<script>
    $('body').on('click', '.marble-file-library-picker[data-field="{{ $field->id }}"][data-lang="{{ $languageId }}"]', function () {
        MarbleMedia.open(function (media) {
            var $container = $('#attribute-file-{{ $field->id }}-{{ $languageId }}');
            $container.find('.attribute-file-input').val('library:' + media.id);

            // Show selected file
            $container.find('.attribute-file-current').remove();
            var icon = media.thumbnail
                ? '<img src="' + media.thumbnail + '" style="height:20px;vertical-align:middle;margin-right:4px" />'
                : '';
            $container.prepend(
                '<div class="attribute-file-current marble-file-current">' +
                icon +
                '<span class="attribute-file-name">' + media.original_filename + '</span>' +
                '<button type="button" class="btn btn-default btn-xs attribute-file-remove marble-ml-auto" onclick="' +
                    'document.querySelector(\'#attribute-file-{{ $field->id }}-{{ $languageId }} .attribute-file-input\').value = \'remove\';' +
                    'this.closest(\'.attribute-file-current\').remove();">' +
                    '✕ {{ trans('marble::admin.remove') }}' +
                '</button>' +
                '</div>'
            );
        });
    });
</script>
