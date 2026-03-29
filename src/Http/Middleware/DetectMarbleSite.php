<?php

namespace Marble\Admin\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Marble\Admin\Facades\Marble;
use Marble\Admin\Models\Site;

class DetectMarbleSite
{
    public function handle(Request $request, Closure $next): mixed
    {
        $site = Site::current();

        if ($site) {
            // Apply the site's default language if set
            if ($site->default_language_id && $site->defaultLanguage) {
                Marble::setLocale($site->defaultLanguage->code);
            }
        }

        return $next($request);
    }
}
