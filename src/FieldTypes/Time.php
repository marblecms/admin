<?php

namespace Marble\Admin\FieldTypes;

class Time extends BaseFieldType
{
    public function identifier(): string
    {
        return 'time';
    }

    public function name(): string
    {
        return 'Time';
    }

    public function allowInForm(): bool
    {
        return true;
    }

    public function formComponent(): string
    {
        return 'marble::components.form-fields.time';
    }

    public function isStructured(): bool
    {
        return true;
    }

    public function defaultValue(): mixed
    {
        return ['hour' => '', 'minute' => ''];
    }

    public function isEmpty(?string $raw): bool
    {
        if ($raw === null || $raw === '') return true;
        $decoded = json_decode($raw, true);
        return !is_array($decoded) || (($decoded['hour'] ?? '') === '' && ($decoded['minute'] ?? '') === '');
    }
}
