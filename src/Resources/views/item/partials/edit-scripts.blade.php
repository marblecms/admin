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
        var STORAGE_PREFIX = 'marble_sidebar.';

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
                    var collapsed = !isCollapsed(key);
                    setCollapsed(key, collapsed);
                    applyState($content[0], $toggle[0], collapsed);
                });
            });
        });
    })();

    // ── Field History Timeline ────────────────────────────────────────────────
    (function () {
        var csrfToken = $('meta[name="csrf-token"]').attr('content');
        var modalBuilt = false;
        var $modal, $slider, $display, $restoreBtn, $title, $counter;
        var currentHistory = [], currentLanguageId = null, currentRestoreUrl = null;

        function buildModal() {
            if (modalBuilt) return;
            modalBuilt = true;

            var html = [
                '<div id="field-history-modal">',
                '  <div class="fh-backdrop" id="fh-backdrop"></div>',
                '  <div class="fh-card">',
                '    <div class="fh-card-head">',
                '      <div class="fh-card-head-icon">&#x1F552;</div>',
                '      <div class="fh-card-head-text">',
                '        <h5 id="fh-title">{{ trans('marble::admin.field_history') }}</h5>',
                '        <p>{{ trans('marble::admin.field_history_hint') }}</p>',
                '      </div>',
                '      <button class="fh-close" id="fh-close-btn" title="Close">&times;</button>',
                '    </div>',
                '    <div class="fh-card-body">',
                '      <input type="range" id="fh-slider" class="marble-fh-slider" min="0" value="0" step="1" />',
                '      <div id="fh-meta" class="marble-fh-meta"></div>',
                '      <div id="fh-display" class="marble-fh-display"></div>',
                '      <p id="fh-counter" class="fh-counter"></p>',
                '    </div>',
                '    <div class="fh-card-foot">',
                '      <button type="button" class="btn btn-default" id="fh-cancel-btn">{{ trans('marble::admin.cancel') }}</button>',
                '      <button type="button" class="btn btn-primary" id="fh-restore-btn">&#x21BA; {{ trans('marble::admin.field_history_restore') }}</button>',
                '    </div>',
                '  </div>',
                '</div>',
            ].join('');

            $('body').append(html);
            $modal      = $('#field-history-modal');
            $slider     = $('#fh-slider');
            $display    = $('#fh-display');
            $restoreBtn = $('#fh-restore-btn');
            $title      = $('#fh-title');
            $counter    = $('#fh-counter');

            $slider.on('input', function () { renderEntry(parseInt(this.value)); });

            $('#fh-close-btn, #fh-cancel-btn, #fh-backdrop').on('click', closeModal);

            $restoreBtn.on('click', function () {
                if (!currentRestoreUrl || !currentHistory.length) return;
                var entry = currentHistory[parseInt($slider.val())];
                if (entry.revision_id === null) { closeModal(); return; }

                $restoreBtn.prop('disabled', true).text('Restoring…');
                $.post(currentRestoreUrl, {
                    _token: csrfToken,
                    language_id: currentLanguageId,
                    value: entry.value,
                }).done(function () {
                    closeModal();
                    window.location.reload();
                }).fail(function () {
                    $restoreBtn.prop('disabled', false).html('&#x21BA; {{ trans('marble::admin.field_history_restore') }}');
                    alert('Could not restore value.');
                });
            });
        }

        function openModal()  { buildModal(); $modal.addClass('fh-open'); $('body').addClass('marble-fh-noscroll'); }
        function closeModal() { $modal.removeClass('fh-open'); $('body').removeClass('marble-fh-noscroll'); }

        function renderEntry(idx) {
            var entry = currentHistory[idx];
            if (!entry) return;

            // Meta line: dot + date + user
            var metaHtml = '<span class="fh-meta-dot"></span>'
                + '<span class="fh-meta-date">' + $('<span>').text(entry.label).html() + '</span>';
            if (entry.user) {
                metaHtml += '<span class="fh-meta-sep">·</span>'
                    + '<span class="fh-meta-user">' + $('<span>').text(entry.user).html() + '</span>';
            }
            $('#fh-meta').html(metaHtml);

            // Value display
            var val = entry.value || '';
            var plain = $('<div>').html(val).text().trim();
            if (plain) {
                $display.text(plain).removeClass('fh-empty');
            } else {
                $display.text('—').addClass('fh-empty');
            }

            $counter.text((idx + 1) + ' / ' + currentHistory.length);
            $restoreBtn.prop('disabled', entry.revision_id === null);
        }

        $(document).on('click', '.marble-field-history-btn', function () {
            var $btn = $(this);
            var url  = $btn.data('history-url');
            currentRestoreUrl = $btn.data('restore-url');

            var $langSwitch = $btn.closest('.form-group').find('.lang-switch.active');
            currentLanguageId = $langSwitch.length ? $langSwitch.data('lang') : null;

            buildModal();
            $title.text('{{ trans('marble::admin.field_history') }}: ' + $btn.data('field-name'));
            $display.text('…').removeClass('fh-empty');
            $('#fh-meta').html('');
            $counter.text('');
            $slider.hide();
            $restoreBtn.hide().prop('disabled', false).html('&#x21BA; {{ trans('marble::admin.field_history_restore') }}');
            openModal();

            $.get(url, currentLanguageId ? { language_id: currentLanguageId } : {}, function (data) {
                currentHistory    = data.history;
                currentLanguageId = data.language;
                if (!currentHistory.length) {
                    $display.text('{{ trans('marble::admin.field_history_no_history') }}').addClass('fh-empty');
                    return;
                }
                $slider.show().attr('max', currentHistory.length - 1).val(0);
                $restoreBtn.show();
                renderEntry(0);
            });
        });

        // Close on Escape key
        $(document).on('keydown', function (e) {
            if (e.key === 'Escape' && $modal && $modal.hasClass('fh-open')) closeModal();
        });
    })();
</script>
