@php $fileCount = is_array($value) ? count($value) : 0; @endphp
<div class="attribute-container" id="attribute-files-{{ $field->id }}-{{ $languageId }}">
    <input type="hidden"
           name="fields[{{ $field->id }}][{{ $languageId }}]"
           class="attribute-files-remove-input"
           value="noop" />
    <input type="hidden"
           name="fields_order[{{ $field->id }}][{{ $languageId }}]"
           class="attribute-files-order-input"
           value="{{ is_array($value) ? implode(',', array_keys($value)) : '' }}" />

    <div class="attribute-files-list marble-mb-xs">
        @if(is_array($value) && $fileCount > 0)
            @foreach($value as $key => $file)
            <div class="attribute-files-item marble-file-item" data-index="{{ $key }}">
                @include('marble::components.famicon', ['name' => 'page_white'])
                <span>{{ $file['original_filename'] }}</span>
                <small class="text-muted">({{ number_format(($file['size'] ?? 0) / 1024, 1) }} KB)</small>
                <div class="marble-ml-auto marble-flex-center marble-gap-xs">
                    @if($fileCount > 1)
                    <button type="button" class="btn btn-default btn-xs marble-files-up"
                            @if($loop->first) disabled @endif
                            onclick="marbleFilesMove(this, -1, '{{ $field->id }}', '{{ $languageId }}')">
                        @include('marble::components.famicon', ['name' => 'arrow_up'])
                    </button>
                    <button type="button" class="btn btn-default btn-xs marble-files-down"
                            @if($loop->last) disabled @endif
                            onclick="marbleFilesMove(this, 1, '{{ $field->id }}', '{{ $languageId }}')">
                        @include('marble::components.famicon', ['name' => 'arrow_down'])
                    </button>
                    @endif
                    <button type="button"
                            class="btn btn-default btn-xs"
                            onclick="marbleFilesRemove(this, '{{ $field->id }}', '{{ $languageId }}', {{ $key }})">
                        @include('marble::components.famicon', ['name' => 'cancel'])
                        {{ trans('marble::admin.remove') }}
                    </button>
                </div>
            </div>
            @endforeach
        @endif
    </div>

    <div class="marble-flex-center marble-mt-xs">
        <input type="file"
               name="file_{{ $field->id }}_{{ $languageId }}"
               class="form-control marble-flex-1" />
        <button type="button" class="btn btn-default btn-sm marble-files-library-picker"
                data-field="{{ $field->id }}" data-lang="{{ $languageId }}">
            @include('marble::components.famicon', ['name' => 'folder'])
            {{ trans('marble::admin.from_library') }}
        </button>
    </div>
    <input type="hidden" name="library_add[{{ $field->id }}][{{ $languageId }}]"
           id="marble-files-library-input-{{ $field->id }}-{{ $languageId }}" value="" />

    @php
        $allowedFiletypes = trim($field->configuration['allowed_filetypes'] ?? '');
    @endphp
    @if($allowedFiletypes)
        <small class="text-muted">{{ trans('marble::admin.allowed_filetypes') }}: {{ $allowedFiletypes }}</small>
    @endif
</div>
<script>
    $('body').on('click', '.marble-files-library-picker[data-field="{{ $field->id }}"][data-lang="{{ $languageId }}"]', function () {
        MarbleMedia.open(function (media) {
            var fieldId = '{{ $field->id }}';
            var langId  = '{{ $languageId }}';
            var $input  = $('#marble-files-library-input-' + fieldId + '-' + langId);
            var $list   = $('#attribute-files-' + fieldId + '-' + langId + ' .attribute-files-list');

            // Append to comma-separated list
            var current = $input.val();
            $input.val(current ? current + ',' + media.id : String(media.id));

            // Add visual row to list (pending, no remove button needed as it won't be saved until submit)
            $list.append(
                '<div class="attribute-files-item marble-file-item marble-files-library-pending">' +
                    '<span>📄 ' + media.original_filename + '</span>' +
                    '<small class="text-muted marble-ml-xs">(from library)</small>' +
                '</div>'
            );
        });
    });
</script>
<script>
function marbleFilesRemove(btn, fieldId, langId, index) {
    var container = document.getElementById('attribute-files-' + fieldId + '-' + langId);
    var removeInput = container.querySelector('.attribute-files-remove-input');
    var orderInput  = container.querySelector('.attribute-files-order-input');

    // Add to remove list
    var current = removeInput.value === 'noop' ? [] : removeInput.value.split(',');
    current.push(index);
    removeInput.value = current.join(',');

    // Remove from order list
    var order = orderInput.value === '' ? [] : orderInput.value.split(',');
    orderInput.value = order.filter(function(i) { return i != index; }).join(',');

    // Remove DOM item
    btn.closest('.attribute-files-item').remove();

    // Update move button states
    marbleFilesRefreshButtons(container);
}

function marbleFilesMove(btn, direction, fieldId, langId) {
    var container = document.getElementById('attribute-files-' + fieldId + '-' + langId);
    var list      = container.querySelector('.attribute-files-list');
    var item      = btn.closest('.attribute-files-item');
    var items     = Array.from(list.querySelectorAll('.attribute-files-item'));
    var idx       = items.indexOf(item);
    var swapIdx   = idx + direction;

    if (swapIdx < 0 || swapIdx >= items.length) return;

    // Swap DOM
    if (direction === -1) {
        list.insertBefore(item, items[swapIdx]);
    } else {
        list.insertBefore(items[swapIdx], item);
    }

    // Rebuild order input from new DOM order
    var orderInput = container.querySelector('.attribute-files-order-input');
    var newItems   = Array.from(list.querySelectorAll('.attribute-files-item'));
    orderInput.value = newItems.map(function(el) { return el.dataset.index; }).join(',');

    marbleFilesRefreshButtons(container);
}

function marbleFilesRefreshButtons(container) {
    var items = Array.from(container.querySelectorAll('.attribute-files-item'));
    items.forEach(function(item, i) {
        var upBtn   = item.querySelector('.marble-files-up');
        var downBtn = item.querySelector('.marble-files-down');
        if (upBtn)   upBtn.disabled   = (i === 0);
        if (downBtn) downBtn.disabled = (i === items.length - 1);
    });
}
</script>
