<?php

namespace Marble\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Marble\Admin\Contracts\FieldTypeInterface;
use Marble\Admin\Facades\Marble;

class FieldType extends Model
{
    public $timestamps = false;

    protected $fillable = ['name', 'identifier', 'class'];

    /**
     * Resolve the FieldType PHP class instance.
     */
    public function instance(): FieldTypeInterface
    {
        return Marble::fieldType($this->identifier);
    }
}
