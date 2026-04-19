<?php

namespace Marble\Admin\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Marble\Admin\Models\PortalUser;

/**
 * Fired after a portal user successfully registers.
 *
 * Usage:
 *   Event::listen(\Marble\Admin\Events\PortalUserRegistered::class, function ($e) {
 *       // $e->user — the newly created PortalUser model
 *   });
 */
class PortalUserRegistered
{
    use Dispatchable;

    public function __construct(
        public readonly PortalUser $user,
    ) {}
}
