<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Marble\Admin\Models\Item;
use Marble\Admin\Models\MarblePageview;

class ItemTrafficController extends Controller
{
    use AuthorizesRequests;

    public function show(Item $item)
    {
        $this->authorize('update', $item);

        return view('marble::item.traffic', ['item' => $item]);
    }

    public function data(Item $item, Request $request)
    {
        $this->authorize('update', $item);

        $days  = min((int) $request->query('days', 30), 365);
        $since = now()->subDays($days)->startOfDay();

        // Daily pageviews for this item
        $daily = MarblePageview::where('item_id', $item->id)
            ->where('created_at', '>=', $since)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as views, COUNT(DISTINCT session_id) as sessions')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get();

        // Incoming: top referrers to this item
        $referrers = MarblePageview::where('item_id', $item->id)
            ->where('created_at', '>=', $since)
            ->whereNotNull('referrer')
            ->where('referrer', '!=', '')
            ->selectRaw('referrer, COUNT(*) as cnt')
            ->groupBy('referrer')
            ->orderByDesc('cnt')
            ->limit(8)
            ->get()
            ->map(fn ($r) => ['label' => $r->referrer, 'cnt' => (int) $r->cnt]);

        // Outgoing: pages visited by users who came from this item's URL
        $itemSlug  = $item->slug();
        $outgoing  = collect();
        if ($itemSlug) {
            $frontendUrl = rtrim(config('marble.frontend_url', ''), '/');
            $pattern     = '%' . addcslashes($itemSlug, '%_\\') . '%';
            $outgoing = MarblePageview::where('created_at', '>=', $since)
                ->where('referrer', 'LIKE', $pattern)
                ->whereNotNull('item_id')
                ->where('item_id', '!=', $item->id)
                ->selectRaw('item_id, COUNT(*) as cnt')
                ->groupBy('item_id')
                ->orderByDesc('cnt')
                ->limit(8)
                ->get()
                ->map(function ($row) use ($frontendUrl) {
                    $target = \Marble\Admin\Models\Item::find($row->item_id);
                    return [
                        'label' => $target ? $target->name() : '—',
                        'url'   => $target ? ($frontendUrl . $target->slug()) : null,
                        'cnt'   => (int) $row->cnt,
                    ];
                });
        }

        // Summary
        $total    = $daily->sum('views');
        $sessions = MarblePageview::where('item_id', $item->id)
            ->where('created_at', '>=', $since)
            ->distinct('session_id')
            ->count('session_id');

        return response()->json([
            'daily'     => $daily,
            'referrers' => $referrers,
            'outgoing'  => $outgoing,
            'total'     => $total,
            'sessions'  => $sessions,
            'days'      => $days,
            'page'      => $item->name(),
        ]);
    }

    public function siteData(Request $request)
    {
        $days  = min((int) $request->query('days', 30), 365);
        $since = now()->subDays($days)->startOfDay();

        // Top pages
        $topPages = MarblePageview::with('item')
            ->where('created_at', '>=', $since)
            ->whereNotNull('item_id')
            ->selectRaw('item_id, COUNT(*) as views')
            ->groupBy('item_id')
            ->orderByDesc('views')
            ->limit(20)
            ->get()
            ->map(fn ($pv) => [
                'item_id' => $pv->item_id,
                'name'    => $pv->item?->name() ?? '—',
                'views'   => $pv->views,
            ]);

        // Daily totals
        $daily = MarblePageview::where('created_at', '>=', $since)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as views, COUNT(DISTINCT session_id) as sessions')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get();

        return response()->json([
            'topPages' => $topPages,
            'daily'    => $daily,
            'days'     => $days,
        ]);
    }
}
