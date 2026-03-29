<?php

namespace Marble\Admin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BlueprintRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'               => ['required', 'string', 'max:255'],
            'identifier'         => ['required', 'string', 'max:100', 'regex:/^[a-z0-9_]+$/'],
            'allow_children'     => ['required', 'boolean'],
            'list_children'      => ['required', 'boolean'],
            'show_in_tree'       => ['required', 'boolean'],
            'locked'             => ['required', 'boolean'],
            'blueprint_group_id' => ['required', 'integer', 'exists:blueprint_groups,id'],
        ];
    }
}
