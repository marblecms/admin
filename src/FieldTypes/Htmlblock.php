<?php

namespace Marble\Admin\FieldTypes;

use Marble\Admin\Models\Item;

class Htmlblock extends BaseFieldType
{
    public function identifier(): string
    {
        return 'htmlblock';
    }

    public function name(): string
    {
        return 'HTML Block';
    }

    public function process(mixed $raw, int $languageId): mixed
    {
        // Resolve node-link placeholders: {% node-link:42 %}
        return preg_replace_callback('~\{% node-link:(\d+) %\}~m', function ($match) use ($languageId) {
            $item = Item::find($match[1]);
            if ($item && $slug = $item->slug($languageId)) {
                return $slug;
            }
            return $match[0];
        }, $raw ?? '');
    }

    public function configComponent(): ?string
    {
        return 'marble::field-types.htmlblock-config';
    }

    public function configSchema(): array
    {
        return [
            'toolbar' => ['type' => 'string', 'default' => 'full'],
        ];
    }

    public function defaultConfig(): array
    {
        return ['toolbar' => 'full'];
    }
}
