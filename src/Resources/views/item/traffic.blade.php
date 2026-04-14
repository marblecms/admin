@extends('marble::layouts.app')

@section('content_class', 'col-lg-12')

@section('content')
<h1>
    {{ trans('marble::admin.traffic') }}
    <small class="marble-meta marble-fw-normal">{{ $item->name() }}</small>
</h1>

<div class="main-box">
    <header class="main-box-header clearfix">
        <h2>
            @include('marble::components.famicon', ['name' => 'chart_bar'])
            {{ trans('marble::admin.traffic_pageviews') }}
            <span class="pull-right">
                <select id="traffic-days" class="form-control input-sm" style="width:auto;display:inline-block">
                    <option value="7">{{ trans('marble::admin.traffic_last_7') }}</option>
                    <option value="30" selected>{{ trans('marble::admin.traffic_last_30') }}</option>
                    <option value="90">{{ trans('marble::admin.traffic_last_90') }}</option>
                </select>
            </span>
        </h2>
    </header>
    <div class="main-box-body clearfix">
        <div id="traffic-summary" class="marble-traffic-summary"></div>
        <div id="traffic-chart" class="marble-traffic-chart"></div>
    </div>
</div>

<div class="main-box">
    <header class="main-box-header clearfix">
        <h2>@include('marble::components.famicon', ['name' => 'link']) {{ trans('marble::admin.traffic_flow') }}</h2>
    </header>
    <div class="main-box-body clearfix">
        <div id="traffic-flow" class="marble-traffic-flow">
            <p class="text-muted">{{ trans('marble::admin.loading') }}</p>
        </div>
    </div>
</div>
@endsection

@section('javascript')
<script src="https://cdn.jsdelivr.net/npm/d3@7/dist/d3.min.js"></script>
<script>
(function () {
    var itemId   = @json($item->id);
    var itemName = @json($item->name());
    var prefix   = @json(config('marble.route_prefix', 'admin'));
    var i18n     = {
        totalViews:  @json(trans('marble::admin.traffic_total_views')),
        sessions:    @json(trans('marble::admin.traffic_sessions')),
        noData:      @json(trans('marble::admin.traffic_no_data')),
        loading:     @json(trans('marble::admin.loading'))
    };

    function load(days) {
        fetch('/' + prefix + '/item/traffic-data/' + itemId + '?days=' + days, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r){ return r.json(); })
        .then(function(data){ render(data); })
        .catch(function(e){ console.error('Traffic load error', e); });
    }

    function render(data) {
        document.getElementById('traffic-summary').innerHTML =
            '<div class="marble-traffic-stats">' +
            '<div class="marble-traffic-stat"><div class="marble-traffic-stat-val">' + data.total + '</div><div class="marble-traffic-stat-lbl">' + i18n.totalViews + '</div></div>' +
            '<div class="marble-traffic-stat"><div class="marble-traffic-stat-val">' + data.sessions + '</div><div class="marble-traffic-stat-lbl">' + i18n.sessions + '</div></div>' +
            '</div>';

        renderBarChart(data.daily);
        renderFlowGraph(data.referrers || [], data.outgoing || [], data.page || itemName);
    }

    /* ── Bar chart ─────────────────────────────────────────────── */
    function renderBarChart(daily) {
        var el = document.getElementById('traffic-chart');
        el.innerHTML = '';
        if (!daily || !daily.length) {
            el.innerHTML = '<p class="text-muted marble-mt-xs">' + i18n.noData + '</p>';
            return;
        }

        var margin = {top: 16, right: 20, bottom: 44, left: 44};
        var width  = el.offsetWidth - margin.left - margin.right;
        var height = 240 - margin.top - margin.bottom;

        var svg = d3.select(el).append('svg')
            .attr('width',  width  + margin.left + margin.right)
            .attr('height', height + margin.top  + margin.bottom)
          .append('g')
            .attr('transform', 'translate(' + margin.left + ',' + margin.top + ')');

        var parseDate = d3.timeParse('%Y-%m-%d');
        daily.forEach(function(d){ d.dateObj = parseDate(d.date); d.views = +d.views; });

        var x = d3.scaleBand()
            .domain(daily.map(function(d){ return d.dateObj; }))
            .range([0, width]).padding(0.25);

        var maxVal = d3.max(daily, function(d){ return d.views; }) || 1;
        var y = d3.scaleLinear().domain([0, maxVal]).nice().range([height, 0]);

        var defs = svg.append('defs');
        var grad = defs.append('linearGradient').attr('id','bar-grad').attr('x1','0').attr('y1','0').attr('x2','0').attr('y2','1');
        grad.append('stop').attr('offset','0%').attr('stop-color','#4a90d9');
        grad.append('stop').attr('offset','100%').attr('stop-color','#2060a8');

        svg.append('g').attr('class', 'marble-chart-grid')
            .call(d3.axisLeft(y).ticks(5).tickSize(-width).tickFormat(''));

        svg.selectAll('.bar').data(daily).enter()
          .append('rect')
            .attr('class', 'marble-chart-bar')
            .attr('fill', 'url(#bar-grad)')
            .attr('rx', 2)
            .attr('x', function(d){ return x(d.dateObj); })
            .attr('y', function(d){ return y(d.views); })
            .attr('width', x.bandwidth())
            .attr('height', function(d){ return height - y(d.views); });

        var tickEvery = daily.length > 60 ? 14 : daily.length > 20 ? 7 : 1;
        svg.append('g').attr('transform', 'translate(0,' + height + ')')
            .call(d3.axisBottom(x)
                .tickValues(daily.filter(function(d,i){ return i % tickEvery === 0; }).map(function(d){ return d.dateObj; }))
                .tickFormat(d3.timeFormat('%b %d')))
            .selectAll('text')
                .attr('transform','rotate(-35)')
                .style('text-anchor','end')
                .style('font-size','11px');

        svg.append('g').call(d3.axisLeft(y).ticks(5).tickFormat(d3.format('d')));
    }

    /* ── Flow graph ─────────────────────────────────────────────── */
    function renderFlowGraph(incoming, outgoing, pageName) {
        var el = document.getElementById('traffic-flow');
        el.innerHTML = '';

        if (!incoming.length && !outgoing.length) {
            el.innerHTML = '<p class="text-muted marble-mt-xs marble-mb-xs">' + i18n.noData + '</p>';
            return;
        }

        var W     = el.offsetWidth || 900;
        var PAD   = 20;
        var rows  = Math.max(incoming.length, outgoing.length, 1);
        var ROW_H = 40;
        var H     = Math.max(rows * ROW_H + PAD * 2, 100);

        var svg = d3.select(el).append('svg').attr('width', W).attr('height', H);

        var cIn   = 160;
        var cPage = W / 2;
        var cOut  = W - 160;
        var nodeW = 140, nodeH = 26, r = 4;

        function drawNode(g, cx, cy, label, cnt, fill) {
            var nx = cx - nodeW/2, ny = cy - nodeH/2;
            g.append('rect')
                .attr('x', nx).attr('y', ny)
                .attr('width', nodeW).attr('height', nodeH)
                .attr('rx', r).attr('fill', fill)
                .attr('stroke', d3.color(fill).darker(0.5)).attr('stroke-width', 1);
            g.append('text')
                .attr('x', cx).attr('y', cy)
                .attr('text-anchor','middle').attr('dominant-baseline','middle')
                .attr('fill','#fff').attr('font-size', 11).attr('font-family','inherit')
                .text(trunc(label, 17));
            if (cnt != null) {
                g.append('text')
                    .attr('x', nx + nodeW - 5).attr('y', ny + 8)
                    .attr('text-anchor','end').attr('fill','#fff')
                    .attr('font-size', 9).attr('opacity', 0.75)
                    .text(cnt);
            }
        }

        function drawLink(g, x1, y1, x2, y2, weight, color) {
            var mx = (x1 + x2) / 2;
            g.append('path')
                .attr('d','M'+x1+','+y1+' C'+mx+','+y1+' '+mx+','+y2+' '+x2+','+y2)
                .attr('fill','none')
                .attr('stroke', color)
                .attr('stroke-width', Math.max(1.5, Math.min(weight * 5, 7)))
                .attr('opacity', 0.4);
        }

        var g = svg.append('g');

        // Column headers
        [['INCOMING', cIn, '#4a90d9'], ['THIS PAGE', cPage, '#15428B'], ['OUTGOING', cOut, '#2d9a6b']].forEach(function(c) {
            svg.append('text').attr('x', c[0]==='THIS PAGE' ? cPage : c[1]).attr('y', 11)
                .attr('text-anchor','middle').attr('fill', c[2])
                .attr('font-size', 9).attr('font-weight', 700).text(c[0]);
        });

        var pageY = H / 2;
        drawNode(g, cPage, pageY, pageName, null, '#15428B');

        var maxIn = incoming.length ? d3.max(incoming, function(d){ return d.cnt; }) : 1;
        incoming.forEach(function(d, i) {
            var y = PAD + (i + 0.5) * (H - PAD*2) / Math.max(incoming.length, 1);
            var label = d.label.replace(/^https?:\/\/[^\/]+/, '') || d.label;
            drawLink(g, cIn + nodeW/2, y, cPage - nodeW/2, pageY, d.cnt / maxIn, '#4a90d9');
            drawNode(g, cIn, y, label || '/', d.cnt, '#4a90d9');
        });

        var maxOut = outgoing.length ? d3.max(outgoing, function(d){ return d.cnt; }) : 1;
        outgoing.forEach(function(d, i) {
            var y = PAD + (i + 0.5) * (H - PAD*2) / Math.max(outgoing.length, 1);
            drawLink(g, cPage + nodeW/2, pageY, cOut - nodeW/2, y, d.cnt / maxOut, '#2d9a6b');
            drawNode(g, cOut, y, d.label, d.cnt, '#2d9a6b');
        });
    }

    function trunc(s, n) { return s && s.length > n ? s.slice(0, n-1) + '\u2026' : (s || ''); }

    document.getElementById('traffic-days').addEventListener('change', function(){ load(this.value); });
    load(30);
})();
</script>
@endsection
