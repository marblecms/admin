@extends('marble::layouts.app')

@section('content_class', 'col-lg-12')

@section('content')
    <h1>
        {{ trans('marble::admin.media_library') }}
        @if($currentFolder)
            <small class="marble-meta marble-fw-normal">/ {{ $currentFolder->name }}</small>
        @endif
    </h1>

    {{-- Breadcrumb --}}
    @if(!empty($breadcrumb))
    <div class="marble-breadcrumb">
        <a href="{{ route('marble.media.index') }}" class="marble-link">@include('marble::components.famicon', ['name' => 'folder']) {{ trans('marble::admin.all_files') }}</a>
        @foreach($breadcrumb as $crumb)
            <span class="marble-breadcrumb-sep">›</span>
            @if($crumb->id === $currentFolder?->id)
                <span class="text-muted">{{ $crumb->name }}</span>
            @else
                <a href="{{ route('marble.media.index', ['folder' => $crumb->id]) }}" class="marble-link">{{ $crumb->name }}</a>
            @endif
        @endforeach
    </div>
    @endif

    {{-- Folders --}}
    <div class="main-box">
        <header class="main-box-header clearfix">
            <h2>
                @include('marble::components.famicon', ['name' => 'folder']) {{ trans('marble::admin.folders') }}
                <button class="btn btn-success btn-xs pull-right" onclick="$('#new-folder-modal').modal('show')">
                    @include('marble::components.famicon', ['name' => 'add']) {{ trans('marble::admin.new_folder') }}
                </button>
            </h2>
        </header>
        <div class="main-box-body clearfix marble-pad-md">
            @if($folders->isEmpty())
                <p class="text-muted marble-mb-0">{{ trans('marble::admin.no_subfolders') }}</p>
            @else
                <div class="marble-folder-grid">
                    @foreach($folders as $folder)
                        <div class="marble-folder-wrap">
                            <a href="{{ route('marble.media.index', ['folder' => $folder->id]) }}"
                               class="marble-folder-card">
                                <img src="{{ asset('vendor/marble/assets/images/famicons/folder.svg') }}" width="40" height="40" alt="">
                                <span class="marble-folder-name">{{ $folder->name }}</span>
                                <small class="marble-folder-count">{{ $folder->media()->count() }} files</small>
                            </a>
                            {{-- Rename + Delete actions overlay --}}
                            <div class="marble-folder-actions">
                                <button type="button"
                                    onclick="event.stopPropagation();marbleRenameFolder({{ $folder->id }}, '{{ addslashes($folder->name) }}', '{{ route('marble.media.folder.rename', $folder) }}')"
                                    class="btn btn-xs btn-default marble-btn-icon" title="Rename">
                                    @include('marble::components.famicon', ['name' => 'pencil'])
                                </button>
                                <form method="POST" action="{{ route('marble.media.folder.delete', $folder) }}"
                                      onsubmit="return confirm('{{ trans('marble::admin.are_you_sure') }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger marble-btn-icon" title="{{ trans('marble::admin.delete') }}">
                                        @include('marble::components.famicon', ['name' => 'bin'])
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>


    {{-- Files --}}
    <div class="main-box">
        <header class="main-box-header clearfix">
            <h2>{{ trans('marble::admin.media_library') }} ({{ $media->total() }})</h2>
        </header>
        <div class="main-box-body clearfix">

            {{-- Drop Zone --}}
            <div id="media-drop-zone" class="marble-drop-zone">
                <form id="media-upload-form" action="{{ route('marble.media.upload') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="folder_id" value="{{ $currentFolder?->id }}">
                    <input type="file" name="file" id="media-file-input" class="marble-hidden" />
                </form>
                <span id="drop-zone-label" class="marble-drop-zone-label">
                    @include('marble::components.famicon', ['name' => 'image'])
                    Drop file here or <a href="javascript:;" onclick="document.getElementById('media-file-input').click()">browse</a>
                </span>
                <div id="drop-zone-progress" class="marble-hidden marble-mt-sm">
                    <div class="progress"class="marble-table-flush">
                        <div class="progress-bar progress-bar-striped active" style="width:100%">Uploading…</div>
                    </div>
                </div>
            </div>

            @if($media->isEmpty())
                <p class="text-muted text-center marble-mt-xs marble-mb-xs">{{ trans('marble::admin.no_media') }}</p>
            @else
                <div class="media-library-grid">
                    @foreach($media as $item)
                        <div class="media-library-item">
                            <div class="media-library-thumb marble-relative">
                                @if($item->isImage())
                                    <img src="{{ url('/image/160/120/' . $item->filename) }}" alt="{{ $item->original_filename }}" loading="lazy" />
                                    {{-- Focal point indicator dot --}}
                                    @if($item->focal_x !== 50 || $item->focal_y !== 50)
                                        <div class="marble-focal-indicator" style="left:calc({{ $item->focal_x }}% - 5px);top:calc({{ $item->focal_y }}% - 5px)"></div>
                                    @endif
                                @else
                                    <div class="marble-file-thumb">
                                        @include('marble::components.famicon', ['name' => 'attachment'])
                                        <span class="marble-file-ext">{{ strtoupper(pathinfo($item->original_filename, PATHINFO_EXTENSION)) }}</span>
                                    </div>
                                @endif
                            </div>
                            <div class="media-library-info">
                                <div class="media-library-name" title="{{ $item->original_filename }}">{{ $item->original_filename }}</div>
                                <div class="media-library-meta">
                                    {{ number_format($item->size / 1024, 1) }} KB
                                    @if($item->width && $item->height)
                                        &nbsp;&middot;&nbsp;{{ $item->width }}&times;{{ $item->height }}
                                    @endif
                                </div>
                            </div>
                            <div class="media-library-actions">
                                <button type="button" class="btn btn-xs btn-default" title="Copy URL"
                                    onclick="marbleCopyUrl('{{ $item->isImage() ? url('/image/' . $item->filename) : url('/file/' . $item->filename) }}')">
                                    @include('marble::components.famicon', ['name' => 'link'])
                                </button>
                                <a href="{{ $item->isImage() ? url('/image/' . $item->filename) : url('/file/' . $item->filename) }}" target="_blank" class="btn btn-xs btn-default">
                                    @include('marble::components.famicon', ['name' => 'zoom'])
                                </a>
                                @if($item->blueprint_id)
                                <a href="{{ route('marble.media.fields.edit', $item) }}" class="btn btn-xs btn-default"
                                   title="{{ trans('marble::admin.edit_fields') }}">
                                    @include('marble::components.famicon', ['name' => 'pencil'])
                                </a>
                                @endif
                                @if($item->isImage())
                                <button type="button" class="btn btn-xs btn-default"
                                    title="{{ trans('marble::admin.set_focal_point') }}"
                                    onclick="marbleOpenFocalPoint({{ $item->id }}, '{{ url('/image/' . $item->filename) }}', {{ $item->focal_x ?? 50 }}, {{ $item->focal_y ?? 50 }}, '{{ route('marble.media.focal-point', $item) }}')">
                                    @include('marble::components.famicon', ['name' => 'target'])
                                </button>
                                @endif
                                <form method="POST" action="{{ route('marble.media.delete', $item) }}" class="marble-inline-form" onsubmit="return confirm('{{ trans('marble::admin.are_you_sure') }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger">
                                        @include('marble::components.famicon', ['name' => 'bin'])
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="text-center marble-mt-md">
                    {{ $media->appends(request()->query())->links() }}
                </div>
            @endif

            <div class="alert alert-warning marble-text-sm marble-alert-sm">
                @include('marble::components.famicon', ['name' => 'error'])
                {{ trans('marble::admin.media_public_notice') }}
            </div>
        </div>
    </div>
@endsection

@section('javascript')
<script>
function marbleCopyUrl(url) {
    navigator.clipboard.writeText(url).then(function() {
        var btn = event.currentTarget;
        var orig = btn.innerHTML;
        btn.innerHTML = '✓';
        btn.classList.add('btn-success');
        btn.classList.remove('btn-default');
        setTimeout(function(){ btn.innerHTML = orig; btn.classList.remove('btn-success'); btn.classList.add('btn-default'); }, 1500);
    });
}

// Focal Point
var _focalSaveUrl = null, _focalX = 50, _focalY = 50;

function marbleOpenFocalPoint(id, imageUrl, fx, fy, saveUrl) {
    _focalSaveUrl = saveUrl;
    _focalX = fx; _focalY = fy;
    var img = document.getElementById('focal-point-image');
    img.src = imageUrl;
    $('#focal-point-modal').modal('show');
    img.onload = function() { marblePlaceCrosshair(_focalX, _focalY); };
    if (img.complete) marblePlaceCrosshair(_focalX, _focalY);
}

function marblePlaceCrosshair(px, py) {
    var img = document.getElementById('focal-point-image');
    var ch  = document.getElementById('focal-point-crosshair');
    ch.style.left = (px / 100 * img.offsetWidth)  + 'px';
    ch.style.top  = (py / 100 * img.offsetHeight) + 'px';
    document.getElementById('focal-point-coords').textContent = px + '% / ' + py + '%';
}

$(document).on('click', '#focal-point-container', function(e) {
    var img  = document.getElementById('focal-point-image');
    var rect = img.getBoundingClientRect();
    _focalX  = Math.max(0, Math.min(100, Math.round((e.clientX - rect.left) / img.offsetWidth  * 100)));
    _focalY  = Math.max(0, Math.min(100, Math.round((e.clientY - rect.top)  / img.offsetHeight * 100)));
    marblePlaceCrosshair(_focalX, _focalY);
});

$(document).on('click', '#focal-point-save', function() {
    $.post(_focalSaveUrl, {
        _token: $('meta[name="csrf-token"]').attr('content'),
        focal_x: _focalX, focal_y: _focalY
    }).done(function() {
        $('#focal-point-modal').modal('hide');
        window.location.reload();
    });
});

function marbleRenameFolder(id, currentName, url) {
    document.getElementById('rename-folder-input').value = currentName;
    document.getElementById('rename-folder-form').action = url;
    $('#rename-folder-modal').modal('show');
    setTimeout(function(){ document.getElementById('rename-folder-input').select(); }, 300);
}

(function(){
    var zone = document.getElementById('media-drop-zone');
    var form = document.getElementById('media-upload-form');
    var input = document.getElementById('media-file-input');
    var progress = document.getElementById('drop-zone-progress');
    var label = document.getElementById('drop-zone-label');

    zone.addEventListener('dragover', function(e){
        e.preventDefault();
        zone.classList.add('dragging');
    });
    zone.addEventListener('dragleave', function(){
        zone.classList.remove('dragging');
    });
    zone.addEventListener('drop', function(e){
        e.preventDefault();
        zone.classList.remove('dragging');
        if (e.dataTransfer.files.length) uploadFile(e.dataTransfer.files[0]);
    });
    input.addEventListener('change', function(){
        if (this.files.length) uploadFile(this.files[0]);
        this.value = '';
    });

    function uploadFile(file) {
        var fd = new FormData(form);
        fd.set('file', file);
        label.classList.add('marble-hidden');
        progress.classList.remove('marble-hidden');

        var xhr = new XMLHttpRequest();
        xhr.open('POST', form.action, true);
        xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.onload = function() {
            window.location.reload();
        };
        xhr.onerror = function() {
            label.style.display = '';
            progress.style.display = 'none';
            alert('Upload failed.');
        };
        xhr.send(fd);
    }
})();
</script>
@endsection

@push('modals')
    {{-- New Folder Modal --}}
    <div class="modal fade" id="new-folder-modal">
        <form action="{{ route('marble.media.folder.create') }}" method="POST">
            @csrf
            <input type="hidden" name="parent_id" value="{{ $currentFolder?->id }}">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">{{ trans('marble::admin.new_folder') }}</h4>
                    </div>
                    <div class="modal-body">
                        <input type="text" name="name" class="form-control" autofocus placeholder="{{ trans('marble::admin.name') }}" />
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('marble::admin.cancel') }}</button>
                        <button type="submit" class="btn btn-success">{{ trans('marble::admin.save') }}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Rename Folder Modal --}}
    <div class="modal fade" id="rename-folder-modal">
        <form id="rename-folder-form" method="POST">
            @csrf @method('PATCH')
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Rename Folder</h4>
                    </div>
                    <div class="modal-body">
                        <input type="text" name="name" id="rename-folder-input" class="form-control" />
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('marble::admin.cancel') }}</button>
                        <button type="submit" class="btn btn-success">{{ trans('marble::admin.save') }}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="modal fade" id="focal-point-modal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">{{ trans('marble::admin.set_focal_point') }}</h4>
                </div>
                <div class="modal-body marble-focal-body">
                    <div id="focal-point-container" class="marble-focal-wrap">
                        <img id="focal-point-image" src="" class="marble-focal-img" />
                        <div id="focal-point-crosshair" class="marble-focal-crosshair">
                            <div class="marble-focal-ch-h"></div>
                            <div class="marble-focal-ch-v"></div>
                            <div class="marble-focal-dot"></div>
                        </div>
                    </div>
                    <p class="marble-focal-hint">{{ trans('marble::admin.focal_point_hint') }}</p>
                </div>
                <div class="modal-footer">
                    <span id="focal-point-coords" class="marble-focal-coords"></span>
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('marble::admin.cancel') }}</button>
                    <button type="button" class="btn btn-success" id="focal-point-save">
                        @include('marble::components.famicon', ['name' => 'disk']) {{ trans('marble::admin.save') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endpush
