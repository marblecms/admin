<?php

namespace Marble\Admin\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class PortalUser extends Authenticatable
{
    use Notifiable;

    protected $table = 'portal_users';

    protected $fillable = [
        'item_id',
        'email',
        'password',
        'enabled',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'enabled'           => 'boolean',
        'email_verified_at' => 'datetime',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
