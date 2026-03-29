<?php

namespace Marble\Admin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ItemCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'parent_id'    => ['required', 'integer', 'exists:items,id'],
            'blueprint_id' => ['required', 'integer', 'exists:blueprints,id'],
            'name'         => ['required', 'string', 'max:255'],
        ];
    }
}
