<?php

namespace Marble\Admin\Services;

use Marble\Admin\Facades\Marble;
use Marble\Admin\Models\Blueprint;
use ZipArchive;

class MarblePackageExporter
{
    /**
     * Export selected blueprints and field types to a ZIP package.
     *
     * @param int[]    $blueprintIds
     * @param string[] $fieldTypeIdentifiers
     * @param string   $packageName
     * @return string Path to the generated ZIP file
     */
    public function export(array $blueprintIds, array $fieldTypeIdentifiers, string $packageName = 'marble-package'): string
    {
        $tmpPath = sys_get_temp_dir() . '/' . $packageName . '-' . time() . '.marble.zip';

        $zip = new ZipArchive();
        if ($zip->open($tmpPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException("Cannot create ZIP at {$tmpPath}");
        }

        $blueprintIdentifiers = [];
        $exportedFieldTypes   = [];

        // ---- Blueprints ----
        foreach ($blueprintIds as $id) {
            $blueprint = Blueprint::with('fields.fieldType')->find($id);
            if (!$blueprint) {
                continue;
            }

            $blueprintIdentifiers[] = $blueprint->identifier;

            $fields = [];
            foreach ($blueprint->fields as $field) {
                $fields[] = [
                    'name'             => $field->name,
                    'identifier'       => $field->identifier,
                    'field_type'       => $field->fieldType?->identifier ?? '',
                    'sort_order'       => $field->sort_order,
                    'translatable'     => (bool) $field->translatable,
                    'locked'           => (bool) $field->locked,
                    'required'         => false,
                    'validation_rules' => $field->validation_rules ?? null,
                    'config'           => $field->configuration ?? [],
                ];
            }

            $blueprintData = [
                'name'               => $blueprint->name,
                'identifier'         => $blueprint->identifier,
                'icon'               => $blueprint->icon ?? 'page',
                'group'              => $blueprint->group?->name ?? 'Content',
                'allow_children'     => (bool) $blueprint->allow_children,
                'list_children'      => (bool) $blueprint->list_children,
                'show_in_tree'       => (bool) $blueprint->show_in_tree,
                'locked'             => (bool) $blueprint->locked,
                'versionable'        => (bool) ($blueprint->versionable ?? true),
                'schedulable'        => (bool) $blueprint->schedulable,
                'is_form'            => (bool) $blueprint->is_form,
                'api_public'         => (bool) ($blueprint->api_public ?? false),
                'allow_all_children' => $blueprint->allowsAllChildren(),
                'fields'             => $fields,
            ];

            $zip->addFromString(
                'blueprints/' . $blueprint->identifier . '.json',
                json_encode($blueprintData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            );
        }

        // ---- Field Types ----
        foreach ($fieldTypeIdentifiers as $identifier) {
            $instance = Marble::fieldType($identifier);
            if (!$instance) {
                continue;
            }

            $exportedFieldTypes[] = $identifier;
            $shortClass = (new \ReflectionClass($instance))->getShortName();

            $manifest = [
                'identifier' => $identifier,
                'name'       => $instance->name(),
                'class_name' => $shortClass,
            ];

            $zip->addFromString(
                "field-types/{$identifier}/manifest.json",
                json_encode($manifest, JSON_PRETTY_PRINT)
            );

            // PHP class file
            $phpFile = (new \ReflectionClass($instance))->getFileName();
            if ($phpFile && file_exists($phpFile)) {
                $zip->addFile($phpFile, "field-types/{$identifier}/FieldType.php");
            }

            // Views — look for {identifier}.blade.php and {identifier}-config.blade.php
            $viewsBase = __DIR__ . '/../Resources/views/field-types/';
            foreach (["{$identifier}.blade.php", "{$identifier}-config.blade.php"] as $viewFile) {
                $viewPath = $viewsBase . $viewFile;
                if (file_exists($viewPath)) {
                    $zip->addFile($viewPath, "field-types/{$identifier}/views/{$viewFile}");
                }
            }

            // JS — look for files matching identifier in assets/js/attributes/
            $jsBase = __DIR__ . '/../Resources/assets/assets/js/attributes/';
            if (is_dir($jsBase)) {
                foreach (glob($jsBase . '*' . $identifier . '*.js') as $jsFile) {
                    $zip->addFile($jsFile, "field-types/{$identifier}/js/" . basename($jsFile));
                }
            }
        }

        // ---- Manifest ----
        $manifest = [
            'name'           => $packageName,
            'version'        => '1.0.0',
            'description'    => '',
            'marble_version' => '1.0',
            'field_types'    => $exportedFieldTypes,
            'blueprints'     => $blueprintIdentifiers,
        ];

        $zip->addFromString(
            'marble-package.json',
            json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        $zip->close();

        return $tmpPath;
    }
}
