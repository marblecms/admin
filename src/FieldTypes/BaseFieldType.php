<?php

namespace Marble\Admin\FieldTypes;

use Illuminate\Http\Request;
use Marble\Admin\Contracts\FieldTypeInterface;

abstract class BaseFieldType implements FieldTypeInterface
{
    public function process(mixed $raw, int $languageId): mixed
    {
        return $raw;
    }

    public function serialize(mixed $value): string
    {
        if ($this->isStructured()) {
            return json_encode($value);
        }

        return (string) $value;
    }

    public function deserialize(string $stored): mixed
    {
        if ($this->isStructured()) {
            return json_decode($stored, true);
        }

        return $stored;
    }

    public function processInput(mixed $oldValue, mixed $newValue, Request $request, int $blueprintFieldId, int $languageId): mixed
    {
        return $newValue;
    }

    public function rules(): array
    {
        return [];
    }

    public function defaultValue(): mixed
    {
        return '';
    }

    public function isStructured(): bool
    {
        return false;
    }

    public function isEmpty(?string $raw): bool
    {
        return $raw === null || $raw === '';
    }

    public function adminComponent(): string
    {
        return 'marble::field-types.' . $this->identifier();
    }

    public function configComponent(): ?string
    {
        return null;
    }

    public function allowInForm(): bool
    {
        return false;
    }

    public function formComponent(): string
    {
        return 'marble::components.form-fields.text';
    }

    public function frontendComponent(): string
    {
        $specific = 'marble::field-types.frontend.' . $this->identifier();
        return view()->exists($specific) ? $specific : 'marble::field-types.frontend.default';
    }

    public function registerRoutes(): void
    {
        // No routes by default.
    }

    public function scripts(): array
    {
        return [];
    }

    public function getJavascripts(): array
    {
        $files = [];
        foreach ($this->scripts() as $file) {
            $files[] = asset('vendor/marble/assets/js/attributes/' . $file);
        }
        return $files;
    }

    public function configSchema(): array
    {
        return [];
    }

    public function defaultConfig(): array
    {
        return [];
    }
}
