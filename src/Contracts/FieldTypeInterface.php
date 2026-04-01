<?php

namespace Marble\Admin\Contracts;

use Illuminate\Http\Request;

interface FieldTypeInterface
{
    /**
     * Unique identifier for this field type (e.g. 'textfield', 'image', 'selectbox').
     */
    public function identifier(): string;

    /**
     * Human-readable name.
     */
    public function name(): string;

    /**
     * Process raw stored value into a usable format for the frontend API.
     * e.g. ObjectRelation resolves an ID to an Item model.
     */
    public function process(mixed $raw, int $languageId): mixed;

    /**
     * Serialize a value for storage in the database.
     */
    public function serialize(mixed $value): string;

    /**
     * Deserialize a stored value.
     */
    public function deserialize(string $stored): mixed;

    /**
     * Process incoming form value before storing.
     * Handles file uploads, transformations, etc.
     */
    public function processInput(mixed $oldValue, mixed $newValue, Request $request, int $blueprintFieldId, int $languageId): mixed;

    /**
     * Laravel validation rules for this field type.
     */
    public function rules(): array;

    /**
     * Default value for new items.
     */
    public function defaultValue(): mixed;

    /**
     * Whether this field type stores structured/complex data (JSON).
     */
    public function isStructured(): bool;

    /**
     * Whether the given raw DB value is considered empty (no translation entered).
     * Used to decide whether to fall back to the primary language.
     */
    public function isEmpty(?string $raw): bool;

    /**
     * Whether this field type can be rendered as an input in a public-facing form.
     * ObjectRelation, Media, Repeater etc. return false.
     */
    public function allowInForm(): bool;

    /**
     * Blade view name for rendering this field as a public form input.
     * Only called when allowInForm() is true.
     */
    public function formComponent(): string;

    /**
     * Blade component name for the admin edit form.
     * e.g. 'marble::field-types.textfield'
     */
    public function adminComponent(): string;

    /**
     * Blade component name for the admin config form (optional).
     * Return null if no configuration is needed.
     */
    public function configComponent(): ?string;

    /**
     * Register any routes this field type needs (e.g. AJAX upload endpoints).
     */
    public function registerRoutes(): void;

    /**
     * JavaScript files required by the admin UI for this field type.
     */
    public function scripts(): array;

    /**
     * Schema for the configuration this field type accepts.
     * Used for validation and documentation.
     */
    public function configSchema(): array;

    /**
     * Default configuration values.
     */
    public function defaultConfig(): array;
}
