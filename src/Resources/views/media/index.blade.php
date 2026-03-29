@extends('marble::layouts.app')

@section('content_class', 'col-lg-12')

@section('content')
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

    <h1>
        {{ trans('marble::admin.media_library') }}
        @if($currentFolder)
            <small style="font-size:14px;font-weight:normal;color:#999">/ {{ $currentFolder->name }}</small>
        @endif
    </h1>

    {{-- Breadcrumb --}}
    <ol class="breadcrumb" style="margin-bottom:16px">
        <li class="{{ empty($breadcrumb) ? 'active' : '' }}">
            @if(empty($breadcrumb))
                @include('marble::components.famicon', ['name' => 'folder']) {{ trans('marble::admin.all_files') }}
            @else
                <a href="{{ route('marble.media.index') }}">@include('marble::components.famicon', ['name' => 'folder']) {{ trans('marble::admin.all_files') }}</a>
            @endif
        </li>
        @foreach($breadcrumb as $crumb)
            <li class="{{ $crumb->id === $currentFolder?->id ? 'active' : '' }}">
                @if($crumb->id === $currentFolder?->id)
                    {{ $crumb->name }}
                @else
                    <a href="{{ route('marble.media.index', ['folder' => $crumb->id]) }}">{{ $crumb->name }}</a>
                @endif
            </li>
        @endforeach
    </ol>

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
        <div class="main-box-body clearfix" style="padding:16px">
            @if($folders->isEmpty())
                <p class="text-muted" style="margin:0;font-size:13px">{{ trans('marble::admin.no_subfolders') }}</p>
            @else
                <div style="display:flex;flex-wrap:wrap;gap:12px">
                    @foreach($folders as $folder)
                        <div class="marble-folder-wrap" style="position:relative;width:120px"
                             onmouseover="this.querySelector('.folder-actions').style.opacity='1'"
                             onmouseout="this.querySelector('.folder-actions').style.opacity='0.5'">
                            <a href="{{ route('marble.media.index', ['folder' => $folder->id]) }}"
                               style="display:flex;flex-direction:column;align-items:center;padding:14px 10px;background:#f8f9fa;border:1px solid #e9ecef;border-radius:6px;text-decoration:none;color:inherit;transition:background .15s;width:100%;position:relative"
                               onmouseover="this.style.background='#e9ecef'" onmouseout="this.style.background='#f8f9fa'">
                                <img src="{{ asset('vendor/marble/assets/images/famicons/folder.svg') }}" width="40" height="40" alt="">
                                <span style="margin-top:8px;font-size:12px;text-align:center;word-break:break-word;max-width:100px">{{ $folder->name }}</span>
                                <small style="color:#999;font-size:11px">{{ $folder->media()->count() }} files</small>
                            </a>
                            {{-- Rename + Delete actions overlay --}}
                            <div class="folder-actions" style="position:absolute;top:4px;right:4px;display:flex;gap:3px;opacity:0.5;transition:opacity .15s;z-index:2">
                                <button type="button"
                                    onclick="event.stopPropagation();marbleRenameFolder({{ $folder->id }}, '{{ addslashes($folder->name) }}', '{{ route('marble.media.folder.rename', $folder) }}')"
                                    class="btn btn-xs btn-default" title="Rename" style="padding:2px 5px;line-height:1">
                                    @include('marble::components.famicon', ['name' => 'pencil'])
                                </button>
                                <form method="POST" action="{{ route('marble.media.folder.delete', $folder) }}"
                                      onsubmit="return confirm('{{ trans('marble::admin.are_you_sure') }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger" title="{{ trans('marble::admin.delete') }}" style="padding:2px 5px;line-height:1">
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

    {{-- Focal Point Modal --}}
    <div class="modal fade" id="focal-point-modal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">{{ trans('marble::admin.set_focal_point') }}</h4>
                </div>
                <div class="modal-body" style="text-align:center;background:#222;padding:20px">
                    <div id="focal-point-container" style="position:relative;display:inline-block;cursor:crosshair">
                        <img id="focal-point-image" src="" style="max-width:100%;max-height:60vh;display:block" />
                        <div id="focal-point-crosshair" style="position:absolute;width:24px;height:24px;margin-left:-12px;margin-top:-12px;pointer-events:none">
                            <div style="position:absolute;top:50%;left:0;right:0;height:2px;background:#fff;margin-top:-1px;box-shadow:0 0 3px rgba(0,0,0,.8)"></div>
                            <div style="position:absolute;left:50%;top:0;bottom:0;width:2px;background:#fff;margin-left:-1px;box-shadow:0 0 3px rgba(0,0,0,.8)"></div>
                            <div style="position:absolute;top:50%;left:50%;width:8px;height:8px;background:#e74c3c;border-radius:50%;margin:-4px 0 0 -4px;border:2px solid #fff"></div>
                        </div>
                    </div>
                    <p style="color:#aaa;font-size:12px;margin-top:10px">{{ trans('marble::admin.focal_point_hint') }}</p>
                </div>
                <div class="modal-footer">
                    <span id="focal-point-coords" style="color:#999;font-size:12px;margin-right:auto"></span>
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('marble::admin.cancel') }}</button>
                    <button type="button" class="btn btn-success" id="focal-point-save">
                        @include('marble::components.famicon', ['name' => 'disk']) {{ trans('marble::admin.save') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Files --}}
    <div class="main-box">
        <header class="main-box-header clearfix">
            <h2>{{ trans('marble::admin.media_library') }} ({{ $media->total() }})</h2>
        </header>
        <div class="main-box-body clearfix">

            {{-- Drop Zone --}}
            <div id="media-drop-zone" style="border:2px dashed #ccc;border-radius:6px;padding:20px;text-align:center;margin-bottom:20px;transition:all .2s;background:#fafafa">
                <form id="media-upload-form" action="{{ route('marble.media.upload') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="folder_id" value="{{ $currentFolder?->id }}">
                    <input type="file" name="file" id="media-file-input" style="display:none" accept="image/*" />
                </form>
                <span id="drop-zone-label" style="color:#999;font-size:13px">
                    @include('marble::components.famicon', ['name' => 'image'])
                    Drop image here or <a href="javascript:;" onclick="document.getElementById('media-file-input').click()">browse</a>
                </span>
                <div id="drop-zone-progress" style="display:none;margin-top:10px">
                    <div class="progress" style="margin-bottom:0">
                        <div class="progress-bar progress-bar-striped active" style="width:100%">Uploading…</div>
                    </div>
                </div>
            </div>

            @if($media->isEmpty())
                <p class="text-muted" style="padding:10px 0;text-align:center">{{ trans('marble::admin.no_media') }}</p>
            @else
                <div class="media-library-grid">
                    @foreach($media as $item)
                        <div class="media-library-item">
                            <div class="media-library-thumb" style="position:relative">
                                <img src="{{ url('/image/160/120/' . $item->filename) }}" alt="{{ $item->original_filename }}" loading="lazy" />
                                {{-- Focal point indicator dot --}}
                                @if($item->focal_x !== 50 || $item->focal_y !== 50)
                                    <div style="position:absolute;width:10px;height:10px;border-radius:50%;background:#e74c3c;border:2px solid #fff;box-shadow:0 0 3px rgba(0,0,0,.5);pointer-events:none;left:calc({{ $item->focal_x }}% - 5px);top:calc({{ $item->focal_y }}% - 5px)"></div>
                                @endif
                            </div>
                            <div class="media-library-info">
                                <div class="media-library-name" title="{{ $item->original_filename }}">{{ $item->original_filename }}</div>
                                <div class="media-library-meta">{{ number_format($item->size / 1024, 1) }} KB</div>
                            </div>
                            <div class="media-library-actions">
                                <a href="{{ url('/image/' . $item->filename) }}" target="_blank" class="btn btn-xs btn-default">
                                    @include('marble::components.famicon', ['name' => 'zoom'])
                                </a>
                                <button type="button" class="btn btn-xs btn-default"
                                    title="{{ trans('marble::admin.set_focal_point') }}"
                                    onclick="marbleOpenFocalPoint({{ $item->id }}, '{{ url('/image/' . $item->filename) }}', {{ $item->focal_x ?? 50 }}, {{ $item->focal_y ?? 50 }}, '{{ route('marble.media.focal-point', $item) }}')">
                                    @include('marble::components.famicon', ['name' => 'target'])
                                </button>
                                <form method="POST" action="{{ route('marble.media.delete', $item) }}" style="display:inline" onsubmit="return confirm('{{ trans('marble::admin.are_you_sure') }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger">
                                        @include('marble::components.famicon', ['name' => 'bin'])
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="text-center" style="margin-top:16px">
                    {{ $media->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

@section('javascript')
<script>
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

var _fpContainer = document.getElementById('focal-point-container');
var _fpSaveBtn   = document.getElementById('focal-point-save');

if (_fpContainer) {
    _fpContainer.addEventListener('click', function(e) {
        var img  = document.getElementById('focal-point-image');
        var rect = img.getBoundingClientRect();
        _focalX  = Math.max(0, Math.min(100, Math.round((e.clientX - rect.left) / img.offsetWidth  * 100)));
        _focalY  = Math.max(0, Math.min(100, Math.round((e.clientY - rect.top)  / img.offsetHeight * 100)));
        marblePlaceCrosshair(_focalX, _focalY);
    });
}

if (_fpSaveBtn) {
    _fpSaveBtn.addEventListener('click', function() {
        $.post(_focalSaveUrl, {
            _token: $('meta[name="csrf-token"]').attr('content'),
            focal_x: _focalX, focal_y: _focalY
        }).done(function() {
            $('#focal-point-modal').modal('hide');
            window.location.reload();
        });
    });
}

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
        zone.style.borderColor = '#27ae60';
        zone.style.background = '#f0fff4';
    });
    zone.addEventListener('dragleave', function(){
        zone.style.borderColor = '#ccc';
        zone.style.background = '#fafafa';
    });
    zone.addEventListener('drop', function(e){
        e.preventDefault();
        zone.style.borderColor = '#ccc';
        zone.style.background = '#fafafa';
        if (e.dataTransfer.files.length) uploadFile(e.dataTransfer.files[0]);
    });
    input.addEventListener('change', function(){
        if (this.files.length) uploadFile(this.files[0]);
        this.value = '';
    });

    function uploadFile(file) {
        var fd = new FormData(form);
        fd.set('file', file);
        label.style.display = 'none';
        progress.style.display = 'block';

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
