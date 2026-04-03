<?php

namespace Marble\Admin\FieldTypes;

class Textblock extends BaseFieldType
{
    public function identifier(): string
    {
        return 'textblock';
    }

    public function name(): string
    {
        return 'Text Block';
    }

    public function allowInForm(): bool
    {
        return true;
    }

    public function formComponent(): string
    {
        return 'marble::components.form-fields.textarea';
    }

    public function configComponent(): ?string
    {
        return 'marble::field-types.textblock-config';
    }

    public function defaultConfig(): array
    {
        return ['rows' => 5];
    }
}
