@extends('marble::layouts.app')

@section('content_class', 'col-lg-12')

@section('content')
<div style="display:flex;align-items:center;gap:16px;margin-bottom:16px;flex-wrap:wrap;">
    <h1 style="margin:0;">{{ trans('marble::admin.calendar') }}</h1>

    <select id="calendar-blueprint-filter" class="form-control input-sm" style="width:auto;min-width:160px;">
        <option value="">{{ trans('marble::admin.all_blueprints') }}</option>
        @foreach($blueprints as $bp)
            <option value="{{ $bp->id }}">{{ $bp->name }}</option>
        @endforeach
    </select>

    <div style="margin-left:auto;display:flex;gap:12px;font-size:12px;align-items:center;">
        <span style="display:flex;align-items:center;gap:5px;">
            <span style="background:#388E3C;width:12px;height:12px;border-radius:2px;display:inline-block;"></span> {{ trans('marble::admin.calendar_published') }}
        </span>
        <span style="display:flex;align-items:center;gap:5px;">
            <span style="background:#1976D2;width:12px;height:12px;border-radius:2px;display:inline-block;"></span> {{ trans('marble::admin.calendar_scheduled') }}
        </span>
        <span style="display:flex;align-items:center;gap:5px;">
            <span style="background:#C62828;width:12px;height:12px;border-radius:2px;display:inline-block;"></span> {{ trans('marble::admin.calendar_expiry') }}
        </span>
    </div>
</div>

<div class="main-box">
    <div class="main-box-body clearfix" style="padding:16px;">
        <div id="marble-calendar"></div>
    </div>
</div>

{{-- Event detail popover --}}
<div id="cal-popover" style="display:none;position:fixed;background:#fff;border:1px solid #ddd;border-radius:6px;box-shadow:0 4px 16px rgba(0,0,0,0.15);padding:14px 16px;z-index:9999;min-width:220px;font-size:13px;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
        <strong id="cal-pop-title" style="font-size:14px;"></strong>
        <button type="button" id="cal-pop-close" style="background:none;border:none;font-size:18px;cursor:pointer;line-height:1;padding:0 0 0 12px;">&times;</button>
    </div>
    <div id="cal-pop-blueprint" style="color:#888;margin-bottom:6px;font-size:12px;"></div>
    <div id="cal-pop-status" style="margin-bottom:10px;"></div>
    <a id="cal-pop-edit" href="#" class="btn btn-xs btn-default">{{ trans('marble::admin.edit') }}</a>
</div>
@endsection

@section('javascript')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" />
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script>
(function(){
    var eventsUrl   = '{{ route('marble.calendar.events') }}';
    var reschedUrl  = '{{ url(config('marble.route_prefix', 'admin') . '/calendar/reschedule') }}';
    var csrfToken   = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    var cal = new FullCalendar.Calendar(document.getElementById('marble-calendar'), {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left:   'prev,next today',
            center: 'title',
            right:  'dayGridMonth,timeGridWeek,listMonth'
        },
        height: 'auto',
        editable: true,
        eventSources: [{
            url:   eventsUrl,
            extraParams: function() {
                return { blueprint_id: document.getElementById('calendar-blueprint-filter').value };
            },
        }],

        eventClick: function(info) {
            info.jsEvent.preventDefault();
            var p = info.event.extendedProps;
            var pop = document.getElementById('cal-popover');
            document.getElementById('cal-pop-title').textContent     = info.event.title.replace(/^⏎\s*/, '');
            document.getElementById('cal-pop-blueprint').textContent  = p.blueprint || '';
            document.getElementById('cal-pop-edit').href              = info.event.url;

            var typeLabel = p.type === 'expire'
                ? '<span style="color:#C62828">{{ trans('marble::admin.calendar_expiry') }}</span>'
                : (p.status === 'published'
                    ? '<span style="color:#388E3C">{{ trans('marble::admin.calendar_published') }}</span>'
                    : '<span style="color:#1976D2">{{ trans('marble::admin.calendar_scheduled') }}</span>');
            document.getElementById('cal-pop-status').innerHTML = typeLabel;

            pop.style.display = 'block';
            pop.style.left = Math.min(info.jsEvent.pageX + 12, window.innerWidth - 260) + 'px';
            pop.style.top  = (info.jsEvent.pageY - 10) + 'px';
        },

        eventDrop: function(info) {
            var field = info.event.extendedProps.type === 'expire' ? 'expires_at' : 'published_at';
            var itemId = info.event.extendedProps.itemId;
            var newDate = info.event.start.toISOString();

            fetch(reschedUrl + '/' + itemId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ field: field, date: newDate }),
            }).then(function(r) {
                if (!r.ok) {
                    info.revert();
                    alert('{{ trans('marble::admin.calendar_reschedule_error') }}');
                }
            }).catch(function() { info.revert(); });
        },

        eventDidMount: function(info) {
            info.el.title = info.event.extendedProps.blueprint || '';
        },
    });

    cal.render();

    document.getElementById('calendar-blueprint-filter').addEventListener('change', function() {
        cal.refetchEvents();
    });

    document.getElementById('cal-pop-close').addEventListener('click', function() {
        document.getElementById('cal-popover').style.display = 'none';
    });
    document.addEventListener('click', function(e) {
        var pop = document.getElementById('cal-popover');
        if (!pop.contains(e.target) && !e.target.closest('.fc-event')) {
            pop.style.display = 'none';
        }
    });
})();
</script>
@endsection
