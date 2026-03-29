<?php

namespace Marble\Admin\Models;

use Illuminate\Database\Eloquent\Model;

class Webhook extends Model
{
    protected $fillable = ['name', 'url', 'events', 'secret', 'active'];

    protected $casts = [
        'events' => 'array',
        'active' => 'boolean',
    ];

    public function listensTo(string $event): bool
    {
        return in_array($event, $this->events ?? []);
    }
}
