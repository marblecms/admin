<?php

namespace Marble\Admin\FieldTypes;

class Textfield extends BaseFieldType
{
    public function identifier(): string
    {
        return 'textfield';
    }

    public function name(): string
    {
        return 'Text Field';
    }

    public function allowInForm(): bool
    {
        return true;
    }
}
