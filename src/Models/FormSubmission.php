<?php

namespace Marble\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormSubmission extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'form_submissions';

    protected $fillable = ['item_id', 'data', 'ip_address', 'user_agent', 'read'];

    protected $casts = [
        'data' => 'array',
        'read' => 'boolean',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
