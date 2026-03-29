<?php

namespace Marble\Admin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'email', 'max:255'],
            'user_group_id' => ['required', 'integer', 'exists:user_groups,id'],
            'password'      => $this->routeIs('marble.user.create')
                                ? ['required', 'string', 'min:8']
                                : ['sometimes', 'nullable', 'string', 'min:8'],
        ];
    }
}
