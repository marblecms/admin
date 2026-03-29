<?php

namespace Marble\Admin\FieldTypes;

class Selectbox extends BaseFieldType
{
    public function identifier(): string
    {
        return 'selectbox';
    }

    public function name(): string
    {
        return 'Select Box';
    }

    public function allowInForm(): bool
    {
        return true;
    }

    public function formComponent(): string
    {
        return 'marble::components.form-fields.select';
    }

    public function configComponent(): ?string
    {
        return 'marble::field-types.selectbox-config';
    }

    public function configSchema(): array
    {
        return [
            'options' => [
                'type' => 'array',
                'items' => [
                    'key' => ['type' => 'string'],
                    'value' => ['type' => 'string'],
                ],
            ],
            'multiple' => ['type' => 'boolean', 'default' => false],
        ];
    }

    public function defaultConfig(): array
    {
        return [
            'options' => [],
            'multiple' => false,
        ];
    }
}
