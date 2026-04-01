<?php

namespace Marble\Admin\FieldTypes;

class Datetime extends BaseFieldType
{
    public function identifier(): string
    {
        return 'datetime';
    }

    public function name(): string
    {
        return 'Date & Time';
    }

    public function allowInForm(): bool
    {
        return true;
    }

    public function formComponent(): string
    {
        return 'marble::components.form-fields.datetime';
    }

    public function isStructured(): bool
    {
        return true;
    }

    public function defaultValue(): mixed
    {
        return ['date' => '', 'time' => ''];
    }

    public function rules(): array
    {
        return ['nullable'];
    }

    public function isEmpty(?string $raw): bool
    {
        if ($raw === null || $raw === '') return true;
        $decoded = json_decode($raw, true);
        return !is_array($decoded) || (($decoded['date'] ?? '') === '' && ($decoded['time'] ?? '') === '');
    }
}
