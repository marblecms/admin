<div class="attribute-container" id="attribute-files-{{ $field->id }}-{{ $languageId }}">
    <input type="hidden"
           name="fields[{{ $field->id }}][{{ $languageId }}]"
           class="attribute-files-remove-input"
           value="noop" />

    <div class="attribute-files-list" style="margin-bottom:6px">
        @if(is_array($value) && count($value))
            @foreach($value as $key => $file)
            <div class="attribute-files-item" data-index="{{ $key }}" style="display:flex;align-items:center;gap:8px;padding:5px 10px;background:#f8f8f8;border:1px solid #ddd;border-radius:3px;margin-bottom:4px">
                @include('marble::components.famicon', ['name' => 'page_white'])
                <span>{{ $file['original_filename'] }}</span>
                <small class="text-muted">({{ number_format(($file['size'] ?? 0) / 1024, 1) }} KB)</small>
                <button type="button"
                        class="btn btn-default btn-xs"
                        style="margin-left:auto"
                        onclick="marbleFilesRemove(this, '{{ $field->id }}', '{{ $languageId }}', {{ $key }})">
                    @include('marble::components.famicon', ['name' => 'cancel'])
                    {{ trans('marble::admin.remove') }}
                </button>
            </div>
            @endforeach
        @endif
    </div>

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
<script>
function marbleFilesRemove(btn, fieldId, langId, index) {
    var container = document.getElementById('attribute-files-' + fieldId + '-' + langId);
    var input     = container.querySelector('.attribute-files-remove-input');
    var current   = input.value === 'noop' ? [] : input.value.split(',');
    current.push(index);
    input.value = current.join(',');
    btn.closest('.attribute-files-item').remove();
}
</script>
