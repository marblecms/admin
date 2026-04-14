<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Admin Route Prefix
    |--------------------------------------------------------------------------
    */
    'route_prefix' => 'admin',

    /*
    |--------------------------------------------------------------------------
    | Admin Auth Guard
    |--------------------------------------------------------------------------
    */
    'guard' => 'marble',

    /*
    |--------------------------------------------------------------------------
    | Primary Locale
    |--------------------------------------------------------------------------
    | The default language used for non-translatable fields.
    */
    'primary_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Current Locale
    |--------------------------------------------------------------------------
    | The language used when no explicit language is passed.
    | Typically set dynamically per request in middleware.
    */
    'locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | URI Locale Prefix
    |--------------------------------------------------------------------------
    | If true, routes are prefixed with the language code: /en/about, /de/ueber-uns
    */
    'uri_locale_prefix' => false,

    /*
    |--------------------------------------------------------------------------
    | System Items ID
    |--------------------------------------------------------------------------
    | The item ID that holds system node references (settings, pages, menu).
    */
    'system_items_id' => null,

    /*
    |--------------------------------------------------------------------------
    | Entry Item ID
    |--------------------------------------------------------------------------
    | The root item for the tree. Used as fallback for user groups without
    | a specific entry item.
    */
    'entry_item_id' => 1,

    /*
    |--------------------------------------------------------------------------
    | Cache TTL
    |--------------------------------------------------------------------------
    | How long to cache items (in seconds). Set to 0 to disable.
    */
    'cache_ttl' => 3600,

    /*
    |--------------------------------------------------------------------------
    | Storage Disk
    |--------------------------------------------------------------------------
    | The disk used for media uploads.
    */
    'storage_disk' => 'public',

    /*
    |--------------------------------------------------------------------------
    | Frontend URL
    |--------------------------------------------------------------------------
    | Base URL of the frontend, used for preview links and sitemap generation.
    */
    'frontend_url' => env('MARBLE_FRONTEND_URL', ''),

    /*
    |--------------------------------------------------------------------------
    | Content Lock TTL
    |--------------------------------------------------------------------------
    | How long (in seconds) a content lock is valid before expiring.
    */
    'lock_ttl' => 300,

    /*
    |--------------------------------------------------------------------------
    | Autosave
    |--------------------------------------------------------------------------
    | Enable autosave on the item edit form.
    | autosave_interval: seconds between saves (after last change).
    */
    'autosave'          => false,
    'autosave_interval' => 30,

    /*
    |--------------------------------------------------------------------------
    | Auto Routing
    |--------------------------------------------------------------------------
    | When enabled, Marble registers a catch-all frontend route automatically.
    | No need to add Route::marble() in routes/web.php.
    |
    | View convention: resources/views/marble-pages/{blueprint_identifier}.blade.php
    | Fallback:        resources/views/marble-pages/default.blade.php
    */
    'auto_routing' => env('MARBLE_AUTO_ROUTING', false),

    /*
    |--------------------------------------------------------------------------
    | Debugbar
    |--------------------------------------------------------------------------
    | When enabled, a floating debug panel is injected into every frontend HTML
    | response — but only when a Marble admin is logged in.
    | Safe to leave on in production: unauthorized visitors never see it.
    */
    'debugbar' => env('MARBLE_DEBUGBAR', false),

    /*
    |--------------------------------------------------------------------------
    | Traffic Tracking
    |--------------------------------------------------------------------------
    | When enabled, Marble records a pageview in marble_pageviews for every
    | frontend page resolved by MarbleRouter. Data is viewable per-item via
    | the Traffic tab in the admin.
    */
    'traffic_tracking' => env('MARBLE_TRAFFIC_TRACKING', false),

    /*
    |--------------------------------------------------------------------------
    | Portal Users
    |--------------------------------------------------------------------------
    | portal_registration: allow visitors to self-register via /portal/register
    | portal_home:         redirect target after login / registration
    */
    'portal_registration' => env('MARBLE_PORTAL_REGISTRATION', false),
    'portal_home'         => env('MARBLE_PORTAL_HOME', '/'),
];
