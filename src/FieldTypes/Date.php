<?php

namespace Marble\Admin\FieldTypes;

class Date extends BaseFieldType
{
    public function identifier(): string
    {
        return 'date';
    }

    public function name(): string
    {
        return 'Date';
    }

    public function allowInForm(): bool
    {
        return true;
    }

    public function formComponent(): string
    {
        return 'marble::components.form-fields.date';
    }

    public function rules(): array
    {
        return ['nullable', 'date'];
    }
}
