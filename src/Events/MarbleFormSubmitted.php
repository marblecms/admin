<?php

namespace Marble\Admin\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Marble\Admin\Models\FormSubmission;
use Marble\Admin\Models\Item;

/**
 * Fired after a frontend form submission is successfully stored.
 *
 * Usage in the application:
 *
 *   Event::listen(\Marble\Admin\Events\MarbleFormSubmitted::class, function ($event) {
 *       // $event->submission  — the FormSubmission model
 *       // $event->item        — the Item (form page)
 *       // $event->data        — assoc array of submitted field values
 *   });
 *
 *   // Or via a dedicated listener class in EventServiceProvider:
 *   MarbleFormSubmitted::class => [SendToCrm::class, SlackNotify::class],
 */
class MarbleFormSubmitted
{
    use Dispatchable;

    public function __construct(
        public readonly FormSubmission $submission,
        public readonly Item           $item,
        public readonly array          $data,
    ) {}
}
