<?php

namespace Marble\Admin\Support;

use Marble\Admin\Models\Blueprint;
use Marble\Admin\Models\BlueprintField;
use Marble\Admin\Models\FieldType;

/**
 * Idempotent blueprint registration for plugins.
 *
 * Call this from your plugin's install command (not ServiceProvider::boot) so
 * blueprints are only seeded when explicitly installed, not on every request.
 *
 * Usage:
 *
 *   use Marble\Admin\Support\BlueprintInstaller;
 *
 *   app(BlueprintInstaller::class)->install([
 *       'identifier'   => 'product',
 *       'name'         => 'Product',
 *       'show_in_tree' => true,
 *       'icon'         => 'bag',
 *       'fields' => [
 *           ['identifier' => 'price',       'type' => 'textfield', 'label' => 'Price',       'sort_order' => 10],
 *           ['identifier' => 'sku',         'type' => 'textfield', 'label' => 'SKU',         'sort_order' => 20],
 *           ['identifier' => 'stock',       'type' => 'textfield', 'label' => 'Stock',       'sort_order' => 30],
 *           ['identifier' => 'description', 'type' => 'htmlblock', 'label' => 'Description', 'sort_order' => 40, 'translatable' => true],
 *           ['identifier' => 'images',      'type' => 'images',    'label' => 'Images',      'sort_order' => 50],
 *           ['identifier' => 'categories',  'type' => 'object_relation_list', 'label' => 'Categories', 'sort_order' => 60,
 *               'config' => ['allowed_blueprints' => ['product_category']]],
 *       ],
 *   ]);
 */
class BlueprintInstaller
{
    /**
     * Idempotently create or update a blueprint and its fields.
     * Existing blueprints / fields are never overwritten — only created if absent.
     */
    public function install(array $config): Blueprint
    {
        // Resolve blueprint_group_id: use provided value, look up by name, or use first available
        $groupId = $config['blueprint_group_id']
            ?? (isset($config['blueprint_group'])
                ? \Marble\Admin\Models\BlueprintGroup::where('name', $config['blueprint_group'])->value('id')
                : null)
            ?? \Marble\Admin\Models\BlueprintGroup::where('name', '!=', 'System')->value('id')
            ?? \Marble\Admin\Models\BlueprintGroup::value('id');

        $blueprint = Blueprint::firstOrCreate(
            ['identifier' => $config['identifier']],
            array_filter([
                'name'               => $config['name'],
                'blueprint_group_id' => $groupId,
                'is_system'          => $config['is_system']    ?? false,
                'show_in_tree'       => $config['show_in_tree'] ?? true,
                'versionable'        => $config['versionable']  ?? false,
                'icon'               => $config['icon']         ?? null,
                'has_slug'           => $config['has_slug']     ?? true,
            ], fn($v) => $v !== null)
        );

        foreach ($config['fields'] ?? [] as $fieldConfig) {
            $this->installField($blueprint, $fieldConfig);
        }

        return $blueprint;
    }

    protected function installField(Blueprint $blueprint, array $config): void
    {
        $fieldType = FieldType::where('identifier', $config['type'])->first();

        if (!$fieldType) {
            return; // Field type not registered — skip gracefully
        }

        BlueprintField::firstOrCreate(
            [
                'blueprint_id' => $blueprint->id,
                'identifier'   => $config['identifier'],
            ],
            array_filter([
                'name'             => $config['label'],
                'field_type_id'    => $fieldType->id,
                'translatable'     => $config['translatable']  ?? false,
                'sort_order'       => $config['sort_order']     ?? 0,
                'configuration'    => isset($config['config']) ? json_encode($config['config']) : null,
                'validation_rules' => $config['validation_rules'] ?? null,
            ], fn($v) => $v !== null)
        );
    }
}
