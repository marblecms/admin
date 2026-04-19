<?php

namespace Marble\Admin\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Marble\Admin\Models\PortalUser;

/**
 * Fired after a portal user successfully logs in.
 *
 * Usage:
 *   Event::listen(\Marble\Admin\Events\PortalUserLoggedIn::class, function ($e) {
 *       // $e->user — the PortalUser model
 *   });
 */
class PortalUserLoggedIn
{
    use Dispatchable;

    public function __construct(
        public readonly PortalUser $user,
    ) {}
}
