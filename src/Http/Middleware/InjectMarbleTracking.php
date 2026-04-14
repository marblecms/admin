<?php

namespace Marble\Admin\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Marble\Admin\MarbleTrackingContext;

class InjectMarbleTracking
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (!config('marble.traffic_tracking', false)) {
            return $response;
        }

        // Only for frontend pages (not admin UI)
        $adminPrefix = trim(config('marble.route_prefix', 'admin'), '/');
        if ($request->is($adminPrefix) || $request->is($adminPrefix . '/*')) {
            return $response;
        }

        if (!($response instanceof Response)) {
            return $response;
        }

        $contentType = $response->headers->get('Content-Type', '');
        if (!str_contains($contentType, 'text/html')) {
            return $response;
        }

        $item = MarbleTrackingContext::getItem();
        if (!$item) {
            return $response;
        }

        $content = $response->getContent();
        if (!str_contains($content, '</body>')) {
            return $response;
        }

        $endpoint   = url(config('marble.route_prefix', 'admin') . '-track');
        $languageId = MarbleTrackingContext::getLanguageId() ?? 0;
        $siteId     = MarbleTrackingContext::getSiteId() ?? 0;
        $itemId     = $item->id;

        $snippet = <<<JS
<script>
(function(){
    var d = {item_id:{$itemId},language_id:{$languageId},site_id:{$siteId},path:location.pathname,referrer:document.referrer};
    if(navigator.sendBeacon){
        navigator.sendBeacon('{$endpoint}',new Blob([JSON.stringify(d)],{type:'application/json'}));
    }
})();
</script>
JS;

        $response->setContent(str_replace('</body>', $snippet . '</body>', $content));
        MarbleTrackingContext::clear();

        return $response;
    }
}
