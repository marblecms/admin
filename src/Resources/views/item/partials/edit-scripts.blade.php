<script type="text/javascript" src="{{ asset('vendor/marble/assets/js/language-switch.js') }}"></script>
<script type="text/javascript" src="{{ asset('vendor/marble/assets/js/attributes/attributes-init.js') }}"></script>
<script>
    // Unsaved changes warning
    var formDirty = false;
    $(function() {
        $('#marble-edit-form :input').on('change input', function() { formDirty = true; });
        window.onbeforeunload = function() {
            if (formDirty) return 'Du hast ungespeicherte Änderungen.';
        };
        $('#marble-edit-form').on('submit', function() {
            formDirty = false;
            marbleReleaseLock();
        });
    });

    // Content locking — acquire on load, refresh every 2 min, release on leave
    var lockUrl   = '{{ route('marble.item.lock', $item) }}';
    var unlockUrl = '{{ route('marble.item.unlock', $item) }}';

    function marbleAcquireLock() {
        $.post(lockUrl);
    }
    function marbleReleaseLock() {
        navigator.sendBeacon(unlockUrl + '?_method=DELETE&_token={{ csrf_token() }}');
    }

    $(function() {
        marbleAcquireLock();
        setInterval(marbleAcquireLock, 120000); // refresh every 2 min
        $(window).on('beforeunload', function() { marbleReleaseLock(); });
    });

    // Slug auto-generation from name field
    $(function() {
        var $nameInput = $('[data-field-identifier="name"] input[type=text], [data-field-identifier="name"] textarea').first();
        var $slugInput = $('[data-field-identifier="slug"] input[type=text]').first();

        if ($nameInput.length && $slugInput.length) {
            $nameInput.on('input', function() {
                if ($slugInput.val() !== '') return;
                var slug = $(this).val()
                    .toLowerCase().trim()
                    .replace(/[äöüß]/g, function(c) { return {ä:'ae',ö:'oe',ü:'ue',ß:'ss'}[c]; })
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-');
                $slugInput.val(slug);
            });
        }
    });

    // URL Alias — add new row
    $(function() {
        var aliasIndex = {{ $aliases->count() }};
        var languages = @json($languages->map(fn($l) => ['id' => $l->id, 'code' => strtoupper($l->code)]));

        $('#add-alias-btn').on('click', function() {
            var opts = languages.map(function(l) {
                return '<option value="' + l.id + '">' + l.code + '</option>';
            }).join('');
            var row = '<div class="alias-row marble-flex-center-sm marble-mb-xs">'
                + '<input type="hidden" name="aliases[' + aliasIndex + '][id]" value="" />'
                + '<input type="text" name="aliases[' + aliasIndex + '][alias]" placeholder="/kampagne" class="form-control input-sm marble-flex-1" />'
                + '<select name="aliases[' + aliasIndex + '][language_id]" class="form-control input-sm marble-lang-select">' + opts + '</select>'
                + '<a href="javascript:;" onclick="this.closest(\'.alias-row\').remove()" class="marble-remove-row">&times;</a>'
                + '</div>';
            $('#aliases-list').append(row);
            aliasIndex++;
        });
    });

    @if(config('marble.autosave', false))
    // Autosave
    var autosaveDelay = {{ config('marble.autosave_interval', 30) * 1000 }};
    var autosaveTimer = null;

    function marbleShowToast(msg, type) {
        var $toast = $('#marble-autosave-toast');
        $toast.removeClass('toast-success toast-error toast-saving').addClass('toast-' + (type || 'success'));
        $toast.text(msg).stop(true).fadeIn(200);
        if (type !== 'error') {
            setTimeout(function() { $toast.fadeOut(400); }, 2000);
        }
    }

    function marbleAutosave() {
        var $form = $('#marble-edit-form');
        marbleShowToast('Saving…', 'saving');
        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            data: $form.serialize(),
            success: function() {
                formDirty = false;
                marbleShowToast('Autosaved ✓', 'success');
            },
            error: function() {
                marbleShowToast('Autosave failed', 'error');
            }
        });
    }

    $(function() {
        $('#marble-edit-form :input').on('change input', function() {
            clearTimeout(autosaveTimer);
            autosaveTimer = setTimeout(marbleAutosave, autosaveDelay);
        });
    });

    if (typeof CKEDITOR !== 'undefined') {
        CKEDITOR.on('instanceCreated', function(e) {
            e.editor.on('change', function() {
                clearTimeout(autosaveTimer);
                autosaveTimer = setTimeout(marbleAutosave, autosaveDelay);
            });
        });
    }
    @endif

    // Save button spinner
    $(function() {
        $('form').on('submit', function() {
            var $btn = $(this).find('.marble-save-btn');
            if ($btn.length) {
                $btn.prop('disabled', true).html('<span class="marble-spinner"></span> {{ trans('marble::admin.saving') }}');
            }
        });
    });

    // ── Collapsible sidebar boxes ─────────────────────────────────────────────
    (function () {
        var STORAGE_PREFIX = 'marble_sidebar.{{ $item->id }}.';

        function isCollapsed(key) {
            try { return localStorage.getItem(STORAGE_PREFIX + key) === '1'; } catch(e) { return false; }
        }
        function setCollapsed(key, v) {
            try { localStorage.setItem(STORAGE_PREFIX + key, v ? '1' : '0'); } catch(e) {}
        }
        function applyState(content, toggle, collapsed) {
            content.style.display = collapsed ? 'none' : '';
            toggle.textContent    = collapsed ? '+' : '−';
        }

        $(function () {
            $('.profile-box-header').each(function () {
                var $header  = $(this);
                var $box     = $header.closest('.main-box');
                var $content = $box.find('.profile-box-content').first();
                if (!$content.length) return;

                var key = ($header.find('h2').text().trim()
                            .replace(/\s+/g, '_')
                            .replace(/[^a-z0-9_]/gi, '')
                            .toLowerCase()) || 'box';

                var $toggle = $('<span>').addClass('marble-sidebar-toggle');

                $header.addClass('marble-sidebar-header');
                $header.find('h2').before($toggle);

                applyState($content[0], $toggle[0], isCollapsed(key));

                $header.on('click', function () {
                    var collapsed = $content.is(':visible');
                    setCollapsed(key, collapsed);
                    applyState($content[0], $toggle[0], collapsed);
                });
            });
        });
    })();
</script>
