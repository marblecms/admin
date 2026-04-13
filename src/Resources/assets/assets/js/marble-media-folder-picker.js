/**
 * MarbleMediaFolder — folder-only picker modal
 *
 * Usage:
 *   MarbleMediaFolder.pick(function(folder) { console.log(folder.id, folder.name); });
 */
var MarbleMediaFolder = (function ($) {

    var _callback      = null;
    var _currentFolder = null;
    var _built         = false;

    // ── Public API ─────────────────────────────────────────────────────────────

    function pick(callback) {
        _callback      = callback;
        _currentFolder = null;

        _buildModal();
        $('#marble-folder-picker-modal').modal('show');
        _load(null);
    }

    // ── Modal HTML ─────────────────────────────────────────────────────────────

    function _buildModal() {
        if (_built) return;
        _built = true;

        $('body').append(
            '<div class="modal fade" id="marble-folder-picker-modal" tabindex="-1" role="dialog">' +
              '<div class="modal-dialog marble-folder-picker-dialog" role="document">' +
                '<div class="modal-content">' +
                  '<div class="marble-picker-wrap">' +

                    '<div class="marble-picker-header">' +
                      '<div class="marble-picker-header-top">' +
                        '<span class="marble-picker-title">Select Folder</span>' +
                        '<button type="button" class="marble-picker-close" data-dismiss="modal">&times;</button>' +
                      '</div>' +
                      '<div class="marble-picker-header-bar">' +
                        '<nav class="marble-picker-breadcrumb" id="marble-folder-picker-breadcrumb">' +
                          '<span class="marble-folder-bc-item marble-picker-bc-root" data-folder-id="">&#8962; Root</span>' +
                        '</nav>' +
                      '</div>' +
                    '</div>' +

                    '<div class="marble-folder-picker-body">' +
                      '<div id="marble-folder-picker-list"></div>' +
                    '</div>' +

                    '<div class="marble-folder-picker-footer">' +
                      '<button type="button" id="marble-folder-picker-select" class="btn btn-success btn-sm">' +
                        '&#10003; Select this folder' +
                      '</button>' +
                      '<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cancel</button>' +
                    '</div>' +

                  '</div>' +
                '</div>' +
              '</div>' +
            '</div>'
        );

        // Breadcrumb clicks
        $('body').on('click', '#marble-folder-picker-breadcrumb .marble-folder-bc-item', function () {
            var fid = $(this).data('folder-id') || null;
            _load(fid);
        });

        // Folder list item click — navigate into folder
        $('body').on('click', '#marble-folder-picker-list .marble-folder-picker-item', function () {
            var fid = $(this).data('folder-id');
            _load(fid);
        });

        // Select button — pick current folder
        $('body').on('click', '#marble-folder-picker-select', function () {
            if (_currentFolder && _callback) {
                _callback({ id: _currentFolder.id, name: _currentFolder.name });
                $('#marble-folder-picker-modal').modal('hide');
            }
        });
    }

    // ── Load ───────────────────────────────────────────────────────────────────

    function _load(folderId) {
        var url = marbleMediaPickerJsonUrl + '?type=all';
        if (folderId) url += '&folder=' + encodeURIComponent(folderId);

        $('#marble-folder-picker-list').html('<div class="marble-picker-loading"><div class="marble-picker-spinner"></div></div>');
        $('#marble-folder-picker-select').prop('disabled', !folderId);

        $.getJSON(url, function (data) {
            var crumbs = data.breadcrumb || [];
            if (folderId && crumbs.length) {
                var last = crumbs[crumbs.length - 1];
                _currentFolder = {
                    id:         last.id,
                    name:       last.name,
                    breadcrumb: crumbs.slice(0, -1), // ancestors only, not the folder itself
                };
            } else {
                _currentFolder = null;
            }
            _renderBreadcrumb(crumbs);
            _renderList(data.folders || []);
            $('#marble-folder-picker-select').prop('disabled', !_currentFolder);
        }).fail(function () {
            $('#marble-folder-picker-list').html('<p class="marble-picker-empty marble-picker-error">Failed to load folders.</p>');
        });
    }

    // ── Render ─────────────────────────────────────────────────────────────────

    function _renderBreadcrumb(crumbs) {
        var html = '<span class="marble-folder-bc-item marble-picker-bc-root" data-folder-id="">&#8962; Root</span>';
        crumbs.forEach(function (c) {
            html += '<span class="marble-picker-bc-sep">›</span>' +
                    '<span class="marble-folder-bc-item marble-picker-bc-item" data-folder-id="' + c.id + '">' + _esc(c.name) + '</span>';
        });
        $('#marble-folder-picker-breadcrumb').html(html);
    }

    function _renderList(folders) {
        if (!folders.length) {
            $('#marble-folder-picker-list').html('<p class="marble-picker-empty">No subfolders.</p>');
            return;
        }
        var html = '';
        folders.forEach(function (f) {
            html += '<div class="marble-folder-picker-item" data-folder-id="' + f.id + '">' +
                        '<span class="marble-picker-folder-icon"></span>' +
                        '<span class="marble-picker-folder-name">' + _esc(f.name) + '</span>' +
                        '<span class="marble-folder-picker-arrow">›</span>' +
                    '</div>';
        });
        $('#marble-folder-picker-list').html(html);
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    function _esc(str) {
        return $('<div>').text(String(str || '')).html();
    }

    return { pick: pick };

})(jQuery);
