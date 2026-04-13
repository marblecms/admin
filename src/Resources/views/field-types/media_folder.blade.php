@php
    $folderId  = $value['folder_id'] ?? null;
    $folder    = $folderId ? \Marble\Admin\Models\MediaFolder::find($folderId) : null;
    $ancestors = $folder ? $folder->ancestors() : [];
@endphp

<div class="attribute-container" id="media-folder-field-{{ $field->id }}-{{ $languageId }}">
    <input type="hidden"
           name="fields[{{ $field->id }}][{{ $languageId }}]"
           id="media-folder-input-{{ $field->id }}-{{ $languageId }}"
           value="{{ $folderId ? 'folder:' . $folderId : 'noop' }}" />

    <div class="marble-flex-center marble-mt-xs">
        <div id="media-folder-label-{{ $field->id }}-{{ $languageId }}" class="marble-media-folder-label marble-flex-1">
            @if($folder)
                @foreach($ancestors as $ancestor)
                    <span class="marble-folder-bc-seg text-muted">{{ $ancestor->name }}</span>
                    <span class="marble-folder-bc-sep">›</span>
                @endforeach
                @include('marble::components.famicon', ['name' => 'folder'])
                <strong>{{ $folder->name }}</strong>
            @else
                <span class="text-muted">{{ trans('marble::admin.no_folder_selected') }}</span>
            @endif
        </div>
        <button type="button" class="btn btn-default btn-sm marble-media-folder-pick"
                data-field="{{ $field->id }}" data-lang="{{ $languageId }}">
            @include('marble::components.famicon', ['name' => 'folder'])
            {{ trans('marble::admin.select_folder') }}
        </button>
        <button type="button" class="btn btn-danger btn-sm marble-media-folder-remove {{ $folderId ? '' : 'marble-hidden' }}"
                data-field="{{ $field->id }}" data-lang="{{ $languageId }}">
            @include('marble::components.famicon', ['name' => 'bin'])
        </button>
    </div>
</div>

<script>
(function(){
    var fieldId   = '{{ $field->id }}';
    var langId    = '{{ $languageId }}';
    var inputSel  = '#media-folder-input-' + fieldId + '-' + langId;
    var labelSel  = '#media-folder-label-' + fieldId + '-' + langId;
    var removeSel = '.marble-media-folder-remove[data-field="' + fieldId + '"][data-lang="' + langId + '"]';

    function buildBreadcrumb(folder) {
        var html = '';
        if (folder.breadcrumb && folder.breadcrumb.length) {
            folder.breadcrumb.forEach(function(seg) {
                html += '<span class="marble-folder-bc-seg text-muted">' + $('<span>').text(seg.name).html() + '</span>' +
                        '<span class="marble-folder-bc-sep">›</span>';
            });
        }
        html += '<strong>' + $('<span>').text(folder.name).html() + '</strong>';
        return html;
    }

    $(document).on('click', '.marble-media-folder-pick[data-field="' + fieldId + '"][data-lang="' + langId + '"]', function() {
        MarbleMediaFolder.pick(function(folder) {
            $(inputSel).val('folder:' + folder.id);
            $(labelSel).html(buildBreadcrumb(folder));
            $(removeSel).removeClass('marble-hidden');
        });
    });

    $(document).on('click', removeSel, function() {
        $(inputSel).val('remove');
        $(labelSel).html('<span class="text-muted">{{ trans('marble::admin.no_folder_selected') }}</span>');
        $(this).addClass('marble-hidden');
    });
})();
</script>
