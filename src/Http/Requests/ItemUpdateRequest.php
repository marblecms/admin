<?php

namespace Marble\Admin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ItemUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fields'         => ['sometimes', 'array'],
            'fields.*.*.\'name\'' => ['sometimes', 'string', 'max:255'],
        ];
    }
}
