<?php

namespace Marble\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BlueprintGroup extends Model
{
    public $timestamps = false;

    protected $fillable = ['name'];

    public function blueprints(): HasMany
    {
        return $this->hasMany(Blueprint::class);
    }
}
