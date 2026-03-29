<?php

namespace Marble\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiToken extends Model
{
    protected $table = 'marble_api_tokens';

    protected $fillable = [
        'name',
        'token',
        'user_id',
        'abilities',
        'last_used_at',
        'expires_at',
    ];

    protected $casts = [
        'abilities'    => 'array',
        'last_used_at' => 'datetime',
        'expires_at'   => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hasAbility(string $ability): bool
    {
        $abilities = $this->abilities ?? [];

        if (in_array('*', $abilities, true)) {
            return true;
        }

        return in_array($ability, $abilities, true);
    }

    public static function findByPlainToken(string $plain): ?self
    {
        $hash = hash('sha256', $plain);

        $token = static::where('token', $hash)->first();

        if (!$token) {
            return null;
        }

        if ($token->expires_at && $token->expires_at->isPast()) {
            return null;
        }

        return $token;
    }
}
