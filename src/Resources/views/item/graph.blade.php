@extends('marble::layouts.app')

@section('content_class', 'col-lg-12')

@section('content')
<h1>
    {{ trans('marble::admin.relations_graph') }}
    <small class="marble-meta marble-fw-normal">{{ $item->name() }}</small>
</h1>

<div class="main-box">
    <div class="main-box-body clearfix marble-pad-md">

        {{-- Legend --}}
        <div style="display:flex;flex-wrap:wrap;gap:16px;margin-bottom:14px;font-size:12px;align-items:center;">
            @foreach([
                ['focus',    '#1976D2', '#BBDEFB', trans('marble::admin.relations_graph') . ' (Focus)'],
                ['parent',   '#7B1FA2', '#E1BEE7', trans('marble::admin.graph_parent')],
                ['child',    '#388E3C', '#C8E6C9', trans('marble::admin.graph_child')],
                ['relation', '#E65100', '#FFE0B2', trans('marble::admin.graph_relation')],
                ['mount',    '#455A64', '#CFD8DC', trans('marble::admin.graph_mount')],
            ] as [$role, $stroke, $fill, $label])
            <span style="display:flex;align-items:center;gap:5px;">
                <span style="display:inline-block;width:14px;height:14px;border-radius:3px;background:{{ $fill }};border:2px solid {{ $stroke }};"></span>
                {{ $label }}
            </span>
            @endforeach
            <span style="color:#888;margin-left:auto;">{{ trans('marble::admin.graph_hint') }}</span>
        </div>

        <div id="marble-graph-wrap" style="width:100%;border:1px solid #e0e0e0;border-radius:4px;background:#f7f8fa;overflow:hidden;position:relative;">
            <svg id="marble-graph-svg" style="display:block;"></svg>
        </div>

        <div id="marble-graph-tooltip" style="display:none;position:fixed;background:rgba(30,30,30,0.92);color:#fff;padding:8px 12px;border-radius:5px;font-size:12px;pointer-events:none;z-index:9999;max-width:260px;line-height:1.6;box-shadow:0 2px 8px rgba(0,0,0,0.3);"></div>
    </div>
</div>

<a href="{{ route('marble.item.edit', $item) }}" class="btn btn-default btn-sm marble-mt-sm">
    @include('marble::components.famicon', ['name' => 'arrow_left']) {{ trans('marble::admin.back') }}
</a>
@endsection

@section('javascript')
<script src="https://cdn.jsdelivr.net/npm/d3@7/dist/d3.min.js"></script>
<script>
(function(){
    var NODE_W = 140, NODE_H = 52, NODE_R = 7;

    var ROLE_STYLE = {
        focus:    { stroke: '#1976D2', fill: '#BBDEFB', text: '#0D47A1' },
        parent:   { stroke: '#7B1FA2', fill: '#E1BEE7', text: '#4A148C' },
        child:    { stroke: '#388E3C', fill: '#C8E6C9', text: '#1B5E20' },
        relation: { stroke: '#E65100', fill: '#FFE0B2', text: '#BF360C' },
        mount:    { stroke: '#455A64', fill: '#CFD8DC', text: '#263238' },
        'default':{ stroke: '#78909C', fill: '#ECEFF1', text: '#37474F' },
    };

    var dataUrl = '{{ route('marble.item.graph-data', $item) }}';

    d3.json(dataUrl).then(function(data) {
        var wrap = document.getElementById('marble-graph-wrap');
        var W = wrap.offsetWidth || 900;
        var H = Math.max(520, Math.min(720, data.nodes.length * 80 + 100));

        var svg = d3.select('#marble-graph-svg').attr('width', W).attr('height', H);

        // Defs: arrow markers per role
        var defs = svg.append('defs');
        Object.entries(ROLE_STYLE).forEach(function([role, s]) {
            defs.append('marker')
                .attr('id', 'arr-' + role)
                .attr('viewBox', '0 -4 8 8')
                .attr('refX', NODE_W / 2 + 6)
                .attr('refY', 0)
                .attr('markerWidth', 7).attr('markerHeight', 7)
                .attr('orient', 'auto')
              .append('path')
                .attr('d', 'M0,-4L8,0L0,4')
                .attr('fill', s.stroke);
        });

        // Build links before forceLink (D3 resolves source/target immediately)
        var links = data.edges.map(function(e) {
            return { source: e.from, target: e.to, label: e.label };
        });

        var simulation = d3.forceSimulation(data.nodes)
            .force('link', d3.forceLink(links).id(function(d){ return d.id; }).distance(200))
            .force('charge', d3.forceManyBody().strength(-600))
            .force('center', d3.forceCenter(W / 2, H / 2))
            .force('collision', d3.forceCollide(NODE_W / 2 + 18));

        var g = svg.append('g');

        svg.call(d3.zoom().scaleExtent([0.2, 3]).on('zoom', function(e) {
            g.attr('transform', e.transform);
        }));

        // Edge lines
        var edgeLine = g.append('g').selectAll('line').data(links).enter().append('line')
            .attr('stroke', function(d) { return roleFromLabel(d.label).stroke; })
            .attr('stroke-width', 2)
            .attr('stroke-opacity', 0.7)
            .attr('marker-end', function(d) {
                return 'url(#arr-' + roleKeyFromLabel(d.label) + ')';
            });

        // Edge labels with background pill
        var edgeLabelG = g.append('g').selectAll('g').data(links).enter().append('g');
        edgeLabelG.append('rect')
            .attr('rx', 8).attr('ry', 8)
            .attr('width', 64).attr('height', 16)
            .attr('x', -32).attr('y', -8)
            .attr('fill', '#fff')
            .attr('stroke', function(d){ return roleFromLabel(d.label).stroke; })
            .attr('stroke-width', 1)
            .attr('opacity', 0.9);
        edgeLabelG.append('text')
            .attr('text-anchor', 'middle')
            .attr('dy', '0.35em')
            .attr('font-size', '10')
            .attr('fill', function(d){ return roleFromLabel(d.label).stroke; })
            .attr('font-weight', '600')
            .text(function(d){ return d.label; });

        // Nodes
        var nodeG = g.append('g').selectAll('g').data(data.nodes).enter().append('g')
            .style('cursor', 'grab')
            .call(d3.drag()
                .on('start', function(e,d){ if (!e.active) simulation.alphaTarget(0.3).restart(); d.fx=d.x; d.fy=d.y; })
                .on('drag',  function(e,d){ d.fx=e.x; d.fy=e.y; })
                .on('end',   function(e,d){ if (!e.active) simulation.alphaTarget(0); d.fx=null; d.fy=null; }));

        // Node rect
        nodeG.append('rect')
            .attr('x', -NODE_W/2).attr('y', -NODE_H/2)
            .attr('width', NODE_W).attr('height', NODE_H)
            .attr('rx', NODE_R).attr('ry', NODE_R)
            .attr('fill', function(d){ return styleFor(d).fill; })
            .attr('stroke', function(d){ return styleFor(d).stroke; })
            .attr('stroke-width', function(d){ return d.role === 'focus' ? 3 : 2; });

        // Status dot (top-right corner)
        nodeG.append('circle')
            .attr('cx', NODE_W/2 - 9).attr('cy', -NODE_H/2 + 9).attr('r', 5)
            .attr('fill', function(d){ return d.status === 'published' ? '#4CAF50' : '#9E9E9E'; })
            .attr('stroke', '#fff').attr('stroke-width', 1.5);

        // Primary label (item name)
        nodeG.append('text')
            .attr('text-anchor', 'middle')
            .attr('y', -7)
            .attr('font-size', '12')
            .attr('font-weight', '700')
            .attr('fill', function(d){ return styleFor(d).text; })
            .text(function(d){ return trunc(d.label, 17); });

        // Secondary label (blueprint type)
        nodeG.append('text')
            .attr('text-anchor', 'middle')
            .attr('y', 10)
            .attr('font-size', '10')
            .attr('fill', '#555')
            .text(function(d){ return trunc(d.type || '', 20); });

        // Tooltip
        var tooltip = document.getElementById('marble-graph-tooltip');
        nodeG
            .on('mouseover', function(e, d){
                var html = '<strong>' + esc(d.label) + '</strong><br>'
                    + (d.type  ? '<span style="color:#aaa">' + esc(d.type)  + '</span><br>' : '')
                    + (d.status === 'published'
                        ? '<span style="color:#81C784">● published</span>'
                        : '<span style="color:#aaa">● draft</span>');
                if (d.aliases) html += '<br><span style="color:#aaa">Alias: ' + esc(d.aliases) + '</span>';
                html += '<br><small style="color:#aaa;font-size:10px">Doppelklick → bearbeiten</small>';
                tooltip.innerHTML = html;
                tooltip.style.display = 'block';
            })
            .on('mousemove', function(e){
                tooltip.style.left = (e.pageX + 14) + 'px';
                tooltip.style.top  = (e.pageY - 36) + 'px';
            })
            .on('mouseout', function(){
                tooltip.style.display = 'none';
            })
            .on('dblclick', function(e, d){
                if (d.url) window.location.href = d.url;
            });

        simulation.on('tick', function(){
            edgeLine
                .attr('x1', function(d){ return d.source.x; })
                .attr('y1', function(d){ return d.source.y; })
                .attr('x2', function(d){ return d.target.x; })
                .attr('y2', function(d){ return d.target.y; });

            edgeLabelG.attr('transform', function(d){
                return 'translate(' + ((d.source.x + d.target.x)/2) + ',' + ((d.source.y + d.target.y)/2) + ')';
            });

            nodeG.attr('transform', function(d){ return 'translate(' + d.x + ',' + d.y + ')'; });
        });

        // Helper functions
        function styleFor(d) { return ROLE_STYLE[d.role] || ROLE_STYLE['default']; }
        function roleKeyFromLabel(label) {
            var en = {
                '{{ trans('marble::admin.graph_parent') }}':   'parent',
                '{{ trans('marble::admin.graph_child') }}':    'child',
                '{{ trans('marble::admin.graph_relation') }}': 'relation',
                '{{ trans('marble::admin.graph_mount') }}':    'mount',
            };
            return en[label] || 'default';
        }
        function roleFromLabel(label) { return ROLE_STYLE[roleKeyFromLabel(label)] || ROLE_STYLE['default']; }
        function trunc(s, n){ return s && s.length > n ? s.slice(0, n-1) + '…' : (s || ''); }
        function esc(s){ var d=document.createElement('div'); d.textContent=s||''; return d.innerHTML; }
    });
})();
</script>
@endsection
