<?php

namespace Marble\Admin\FieldTypes;

class Checkbox extends BaseFieldType
{
    public function identifier(): string
    {
        return 'checkbox';
    }

    public function name(): string
    {
        return 'Checkbox';
    }

    public function allowInForm(): bool
    {
        return true;
    }

    public function formComponent(): string
    {
        return 'marble::components.form-fields.checkbox';
    }

    public function defaultValue(): mixed
    {
        return '0';
    }
}
