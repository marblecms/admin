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

    <input type="file"
           name="file_{{ $field->id }}_{{ $languageId }}"
           class="form-control" />

    @php
        $allowedFiletypes = trim($field->configuration['allowed_filetypes'] ?? '');
    @endphp
    @if($allowedFiletypes)
        <small class="text-muted">{{ trans('marble::admin.allowed_filetypes') }}: {{ $allowedFiletypes }}</small>
    @endif
</div>
