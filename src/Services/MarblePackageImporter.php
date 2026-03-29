<?php

namespace Marble\Admin\Services;

use Illuminate\Support\Facades\DB;
use Marble\Admin\Models\Blueprint;
use Marble\Admin\Models\BlueprintField;
use Marble\Admin\Models\BlueprintGroup;
use Marble\Admin\Models\FieldType;
use ZipArchive;

class MarblePackageImporter
{
    protected array $log = [];

    /**
     * Import a .marble.zip package.
     *
     * @param string $zipPath Path to the ZIP file
     * @return array ['success' => bool, 'log' => string[]]
     */
    public function import(string $zipPath): array
    {
        $this->log = [];

        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            return ['success' => false, 'log' => ['Failed to open ZIP file.']];
        }

        // ---- Security: validate all entries ----
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (!$this->isPathSafe($name)) {
                $zip->close();
                return ['success' => false, 'log' => ["Security violation: unsafe path in ZIP: {$name}"]];
            }
        }

        // ---- Read manifest ----
        $manifestJson = $zip->getFromName('marble-package.json');
        if (!$manifestJson) {
            $zip->close();
            return ['success' => false, 'log' => ['Missing marble-package.json in ZIP.']];
        }

        $manifest = json_decode($manifestJson, true);
        if (!$manifest || !isset($manifest['name'])) {
            $zip->close();
            return ['success' => false, 'log' => ['Invalid marble-package.json.']];
        }

        $this->log[] = "Importing package: {$manifest['name']} v{$manifest['version']}";

        // ---- Install field types first ----
        foreach ($manifest['field_types'] ?? [] as $ftIdentifier) {
            $this->installFieldType($zip, $ftIdentifier);
        }

        // ---- Install blueprints second ----
        foreach ($manifest['blueprints'] ?? [] as $bpIdentifier) {
            $this->installBlueprint($zip, $bpIdentifier);
        }

        $zip->close();

        return ['success' => true, 'log' => $this->log];
    }

    protected function isPathSafe(string $path): bool
    {
        // No absolute paths
        if (str_starts_with($path, '/')) {
            return false;
        }

        // No traversal
        if (str_contains($path, '..')) {
            return false;
        }

        // Only allow alphanumeric, slash, dot, dash, underscore
        if (!preg_match('#^[a-zA-Z0-9/_.\-]+$#', $path)) {
            return false;
        }

        // Whitelist by extension + location
        if (str_ends_with($path, '.php')) {
            // Only allow FieldType.php inside field-types/{id}/
            if (!preg_match('#^field-types/[a-zA-Z0-9_\-]+/FieldType\.php$#', $path)) {
                return false;
            }
        } elseif (str_ends_with($path, '.blade.php')) {
            // Only in views subdirectory or views/
            if (!preg_match('#^field-types/[a-zA-Z0-9_\-]+/views/#', $path)) {
                return false;
            }
        } elseif (str_ends_with($path, '.js')) {
            if (!preg_match('#^field-types/[a-zA-Z0-9_\-]+/js/#', $path)) {
                return false;
            }
        } elseif (str_ends_with($path, '.json')) {
            // manifest.json, marble-package.json, blueprints/*.json
            // ok
        } else {
            // Directories (end with /) are fine, everything else must match
            if (!str_ends_with($path, '/')) {
                return false;
            }
        }

        return true;
    }

    protected function installFieldType(ZipArchive $zip, string $identifier): void
    {
        $manifestJson = $zip->getFromName("field-types/{$identifier}/manifest.json");
        if (!$manifestJson) {
            $this->log[] = "Warning: field type {$identifier} has no manifest.json — skipped.";
            return;
        }

        $ftManifest = json_decode($manifestJson, true);
        if (!$ftManifest) {
            $this->log[] = "Warning: invalid manifest for field type {$identifier} — skipped.";
            return;
        }

        $ftName      = $ftManifest['name'] ?? $identifier;
        $ftClassName = $ftManifest['class_name'] ?? $identifier;
        $targetBase  = app_path("MarbleFieldTypes/{$ftClassName}/");

        // Ensure directory exists
        if (!is_dir($targetBase)) {
            mkdir($targetBase, 0755, true);
        }

        // Extract FieldType.php
        $phpContent = $zip->getFromName("field-types/{$identifier}/FieldType.php");
        if ($phpContent) {
            file_put_contents($targetBase . 'FieldType.php', $phpContent);
            $this->log[] = "Field type {$ftName} installed to app/MarbleFieldTypes/{$ftClassName}/";
            $this->log[] = "Note: Register {$ftClassName} in your ServiceProvider: Marble::registerFieldType(new \\App\\MarbleFieldTypes\\{$ftClassName}\\FieldType());";
        }

        // Extract views
        $viewsTarget = __DIR__ . '/../Resources/views/field-types/';
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (preg_match("#^field-types/{$identifier}/views/(.+\.blade\.php)$#", $name, $m)) {
                $content = $zip->getFromIndex($i);
                file_put_contents($viewsTarget . $m[1], $content);
                $this->log[] = "Installed view: {$m[1]}";
            }
        }

        // Extract JS
        $jsTarget = __DIR__ . '/../Resources/assets/assets/js/attributes/';
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (preg_match("#^field-types/{$identifier}/js/(.+\.js)$#", $name, $m)) {
                $content = $zip->getFromIndex($i);
                if (!is_dir($jsTarget)) {
                    mkdir($jsTarget, 0755, true);
                }
                file_put_contents($jsTarget . $m[1], $content);
                $this->log[] = "Installed JS: {$m[1]}";
            }
        }

        // Upsert FieldType record
        FieldType::updateOrCreate(
            ['identifier' => $identifier],
            [
                'name'  => $ftName,
                'class' => "App\\MarbleFieldTypes\\{$ftClassName}\\FieldType",
            ]
        );
    }

    protected function installBlueprint(ZipArchive $zip, string $identifier): void
    {
        $bpJson = $zip->getFromName("blueprints/{$identifier}.json");
        if (!$bpJson) {
            $this->log[] = "Warning: blueprint {$identifier} not found in ZIP — skipped.";
            return;
        }

        $bpData = json_decode($bpJson, true);
        if (!$bpData) {
            $this->log[] = "Warning: invalid JSON for blueprint {$identifier} — skipped.";
            return;
        }

        // Find or create group
        $groupName = $bpData['group'] ?? 'Content';
        $group = BlueprintGroup::firstOrCreate(['name' => $groupName]);

        // Create or update blueprint
        $blueprint = Blueprint::firstOrCreate(
            ['identifier' => $identifier],
            [
                'name'               => $bpData['name'] ?? $identifier,
                'icon'               => $bpData['icon'] ?? '',
                'blueprint_group_id' => $group->id,
                'allow_children'     => $bpData['allow_children'] ?? false,
                'list_children'      => $bpData['list_children'] ?? false,
                'show_in_tree'       => $bpData['show_in_tree'] ?? true,
                'locked'             => $bpData['locked'] ?? false,
                'versionable'        => $bpData['versionable'] ?? true,
                'schedulable'        => $bpData['schedulable'] ?? false,
                'is_form'            => $bpData['is_form'] ?? false,
                'api_public'         => $bpData['api_public'] ?? false,
            ]
        );

        // Set up allow_all_children
        if ($bpData['allow_all_children'] ?? false) {
            $existing = DB::table('blueprint_allowed_children')
                ->where('blueprint_id', $blueprint->id)
                ->where('allow_all', true)
                ->exists();

            if (!$existing) {
                DB::table('blueprint_allowed_children')->insert([
                    'blueprint_id'       => $blueprint->id,
                    'child_blueprint_id' => null,
                    'allow_all'          => true,
                ]);
            }
        }

        // Import fields
        $sortOrder = 0;
        foreach ($bpData['fields'] ?? [] as $fieldData) {
            $ftIdentifier = $fieldData['field_type'] ?? '';
            $fieldType    = FieldType::where('identifier', $ftIdentifier)->first();

            if (!$fieldType) {
                $this->log[] = "Warning: field type '{$ftIdentifier}' not found — skipping field '{$fieldData['identifier']}'.";
                continue;
            }

            BlueprintField::firstOrCreate(
                [
                    'blueprint_id' => $blueprint->id,
                    'identifier'   => $fieldData['identifier'],
                ],
                [
                    'name'          => $fieldData['name'] ?? $fieldData['identifier'],
                    'field_type_id' => $fieldType->id,
                    'sort_order'    => $fieldData['sort_order'] ?? $sortOrder,
                    'translatable'  => $fieldData['translatable'] ?? true,
                    'locked'        => $fieldData['locked'] ?? false,
                    'configuration' => $fieldData['config'] ?? [],
                    'validation_rules' => $fieldData['validation_rules'] ?? null,
                ]
            );

            $sortOrder++;
        }

        $this->log[] = "Blueprint {$bpData['name']} imported.";
    }
}
