/**
 * MarbleMedia — media library picker modal
 *
 * Usage:
 *   MarbleMedia.open(function(media) { ... });
 *   MarbleMedia.open(callback, { type: 'image' }); // images only
 *   MarbleMedia.open(callback, { type: 'all' });   // all files (default)
 */
var MarbleMedia = (function ($) {

    var _callback      = null;
    var _type          = 'all';
    var _currentFolder = null;
    var _searchTimer   = null;
    var _built         = false;

    // ── Public API ─────────────────────────────────────────────────────────────

    function open(callback, options) {
        _callback      = callback;
        _type          = (options && options.type) ? options.type : 'all';
        _currentFolder = null;

        _buildModal();
        $('#marble-media-picker-modal').modal('show');
        $('#marble-picker-search').val('').trigger('focus');
        _load(null, '');
    }

    // ── Modal HTML ─────────────────────────────────────────────────────────────

    function _buildModal() {
        if (_built) return;
        _built = true;

        $('body').append(
            '<div class="modal fade" id="marble-media-picker-modal" tabindex="-1" role="dialog">' +
              '<div class="modal-dialog marble-picker-dialog" role="document">' +
                '<div class="modal-content">' +
                  '<div class="marble-picker-wrap">' +

                    '<div class="marble-picker-header">' +
                      '<div class="marble-picker-header-top">' +
                        '<span class="marble-picker-title">Media Library</span>' +
                        '<button type="button" class="marble-picker-close" data-dismiss="modal">&times;</button>' +
                      '</div>' +
                      '<div class="marble-picker-header-bar">' +
                        '<nav class="marble-picker-breadcrumb" id="marble-picker-breadcrumb">' +
                          '<span class="marble-picker-bc-item marble-picker-bc-root" data-folder-id="">&#8962; Root</span>' +
                        '</nav>' +
                        '<input type="text" id="marble-picker-search" class="marble-picker-search" placeholder="&#128269; Search files\u2026" />' +
                      '</div>' +
                    '</div>' +

                    '<div class="marble-picker-body">' +
                      '<div class="marble-picker-sidebar" id="marble-picker-sidebar"></div>' +
                      '<div class="marble-picker-main">' +
                        '<div class="marble-picker-grid" id="marble-picker-grid"></div>' +
                      '</div>' +
                    '</div>' +

                  '</div>' +
                '</div>' +
              '</div>' +
            '</div>'
        );

        // Search with debounce
        $('#marble-picker-search').on('input', function () {
            clearTimeout(_searchTimer);
            var q = $(this).val();
            _searchTimer = setTimeout(function () { _load(_currentFolder, q); }, 300);
        });

        // Breadcrumb clicks
        $('body').on('click', '#marble-picker-breadcrumb .marble-picker-bc-item', function () {
            var fid = $(this).data('folder-id');
            $('#marble-picker-search').val('');
            _load(fid || null, '');
        });

        // Sidebar folder clicks
        $('body').on('click', '#marble-picker-sidebar .marble-picker-folder', function () {
            var fid = $(this).data('folder-id');
            $('#marble-picker-search').val('');
            _load(fid || null, '');
        });

        // Grid item click — select and close
        $('body').on('click', '#marble-picker-grid .marble-picker-item', function () {
            var $el = $(this);
            if (_callback) {
                _callback({
                    id:                $el.data('id'),
                    url:               $el.data('url'),
                    thumbnail:         $el.data('thumbnail'),
                    filename:          $el.data('filename'),
                    original_filename: $el.data('name'),
                    mime_type:         $el.data('mime'),
                    size:              $el.data('size'),
                });
            }
            $('#marble-media-picker-modal').modal('hide');
        });
    }

    // ── Load ───────────────────────────────────────────────────────────────────

    function _load(folderId, q) {
        _currentFolder = (folderId !== undefined) ? folderId : null;

        var url = marbleMediaPickerJsonUrl + '?type=' + encodeURIComponent(_type);
        if (folderId) url += '&folder=' + encodeURIComponent(folderId);
        if (q)        url += '&q='      + encodeURIComponent(q);

        $('#marble-picker-grid').html('<div class="marble-picker-loading"><div class="marble-picker-spinner"></div></div>');
        $('#marble-picker-sidebar').html('');

        $.getJSON(url, function (data) {
            _renderBreadcrumb(data.breadcrumb || [], q);
            _renderSidebar(data.folders || [], q);
            _renderGrid(data.media || []);
        }).fail(function () {
            $('#marble-picker-grid').html('<p class="marble-picker-empty marble-picker-error">Failed to load media.</p>');
        });
    }

    // ── Render ─────────────────────────────────────────────────────────────────

    function _renderBreadcrumb(crumbs, q) {
        var html = '<span class="marble-picker-bc-item marble-picker-bc-root" data-folder-id="">&#8962; Root</span>';
        if (!q) {
            crumbs.forEach(function (c) {
                html += '<span class="marble-picker-bc-sep">›</span>' +
                        '<span class="marble-picker-bc-item" data-folder-id="' + c.id + '">' + _esc(c.name) + '</span>';
            });
        } else {
            html += '<span class="marble-picker-bc-sep">›</span><span class="marble-picker-bc-search">Search results</span>';
        }
        $('#marble-picker-breadcrumb').html(html);
    }

    function _renderSidebar(folders, q) {
        if (q || !folders.length) {
            $('#marble-picker-sidebar').html('');
            return;
        }
        var html = '';
        folders.forEach(function (f) {
            html += '<div class="marble-picker-folder" data-folder-id="' + f.id + '">' +
                        '<span class="marble-picker-folder-icon"></span>' +
                        '<span class="marble-picker-folder-name">' + _esc(f.name) + '</span>' +
                    '</div>';
        });
        $('#marble-picker-sidebar').html(html);
    }

    function _renderGrid(media) {
        if (!media.length) {
            $('#marble-picker-grid').html('<p class="marble-picker-empty">No files found.</p>');
            return;
        }
        var html = '';
        media.forEach(function (m) {
            var sizeKb = m.size ? (m.size / 1024).toFixed(0) + ' KB' : '';
            html +=
                '<div class="marble-picker-item"' +
                    ' data-id="'        + m.id                              + '"' +
                    ' data-url="'       + _escAttr(m.url)                   + '"' +
                    ' data-thumbnail="' + _escAttr(m.thumbnail || '')       + '"' +
                    ' data-filename="'  + _escAttr(m.filename)              + '"' +
                    ' data-name="'      + _escAttr(m.original_filename)     + '"' +
                    ' data-mime="'      + _escAttr(m.mime_type)             + '"' +
                    ' data-size="'      + (m.size || 0)                     + '"' +
                    ' title="'          + _escAttr(m.original_filename) + '\n' + _escAttr(m.mime_type) + (sizeKb ? ' · ' + sizeKb : '') + '">' +
                    (m.thumbnail
                        ? '<div class="marble-picker-thumb-wrap"><img src="' + _escAttr(m.thumbnail) + '" class="marble-picker-thumb" loading="lazy" /></div>'
                        : '<div class="marble-picker-thumb-wrap marble-picker-file-icon">' + _mimeIcon(m.mime_type) + '</div>') +
                    '<div class="marble-picker-item-name">' + _esc(m.original_filename) + '</div>' +
                    (sizeKb ? '<div class="marble-picker-item-meta">' + sizeKb + '</div>' : '') +
                '</div>';
        });
        $('#marble-picker-grid').html(html);
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    function _mimeIcon(mime) {
        if (!mime) return '📄';
        if (mime.startsWith('video/'))       return '🎬';
        if (mime.startsWith('audio/'))       return '🎵';
        if (mime === 'application/pdf')      return '📕';
        if (mime.includes('zip') || mime.includes('compressed')) return '🗜';
        if (mime.includes('word') || mime.includes('document'))  return '📝';
        return '📄';
    }

    function _esc(str) {
        return $('<div>').text(String(str || '')).html();
    }

    function _escAttr(str) {
        return _esc(str).replace(/"/g, '&quot;');
    }

    return { open: open };

})(jQuery);
