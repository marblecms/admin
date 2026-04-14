<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Marble\Admin\Models\MarblePageview;

class PageviewTrackingController extends Controller
{
    public function store(Request $request)
    {
        if (!config('marble.traffic_tracking', false)) {
            return response()->noContent();
        }

        $data = $request->json()->all();

        $itemId     = isset($data['item_id'])     ? (int) $data['item_id']     : null;
        $languageId = isset($data['language_id']) ? (int) $data['language_id'] : null;
        $siteId     = isset($data['site_id'])     ? (int) $data['site_id']     : null;
        $path       = isset($data['path'])        ? substr((string) $data['path'], 0, 500) : null;
        $referrer   = isset($data['referrer'])    ? substr((string) $data['referrer'], 0, 500) : null;

        if (!$itemId) {
            return response()->noContent();
        }

        $sessionCookie = config('marble.tracking_session_cookie', 'marble_sess');
        $sessionId     = $request->cookie($sessionCookie) ?? substr(md5(uniqid((string) mt_rand(), true)), 0, 24);

        MarblePageview::create([
            'item_id'     => $itemId,
            'language_id' => $languageId ?: null,
            'site_id'     => $siteId ?: null,
            'path'        => $path,
            'referrer'    => $referrer ?: null,
            'session_id'  => $sessionId,
            'ip'          => $request->ip(),
            'created_at'  => now(),
        ]);

        return response()->noContent();
    }
}
