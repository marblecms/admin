<?php

namespace Marble\Admin\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Marble\Admin\Models\Blueprint;
use Marble\Admin\Models\BlueprintField;
use Marble\Admin\Models\BlueprintGroup;
use Marble\Admin\Models\FieldType;
use Marble\Admin\Models\Item;
use Marble\Admin\Models\ItemValue;
use Marble\Admin\Models\Language;
use Marble\Admin\Models\User;
use Marble\Admin\Models\UserGroup;

class MarbleSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(FieldTypeSeeder::class);

        // Languages
        $en = Language::firstOrCreate(['code' => 'en'], ['name' => 'English']);
        $de = Language::firstOrCreate(['code' => 'de'], ['name' => 'Deutsch']);
        $sk = Language::firstOrCreate(['code' => 'sk'], ['name' => 'Slovenčina']);

        $textfield    = FieldType::where('identifier', 'textfield')->first();
        $systemGroup  = BlueprintGroup::firstOrCreate(['name' => 'System']);
        $contentGroup = BlueprintGroup::firstOrCreate(['name' => 'Content']);

        // ── Blueprints ────────────────────────────────────────────────────────

        // SystemFolder — hidden root, never appears in tree
        $systemFolder = Blueprint::firstOrCreate(
            ['identifier' => 'system_folder'],
            [
                'name'               => 'System Folder',
                'icon'               => 'folder',
                'blueprint_group_id' => $systemGroup->id,
                'allow_children'     => true,
                'list_children'      => true,
                'show_in_tree'       => false,
                'locked'             => true,
            ]
        );
        $this->ensureAllowAllChildren($systemFolder->id);
        $this->ensureNameField($systemFolder, $textfield);

        // SystemContentFolder — "Content" node, visible top-level
        $contentFolder = Blueprint::firstOrCreate(
            ['identifier' => 'system_content_folder'],
            [
                'name'               => 'Content Folder',
                'icon'               => 'folder',
                'blueprint_group_id' => $systemGroup->id,
                'allow_children'     => true,
                'list_children'      => true,
                'show_in_tree'       => true,
                'locked'             => true,
            ]
        );
        $this->ensureAllowAllChildren($contentFolder->id);
        $this->ensureNameField($contentFolder, $textfield);

        // SystemSettingsFolder — "Settings" node, visible top-level
        $settingsFolder = Blueprint::firstOrCreate(
            ['identifier' => 'system_settings_folder'],
            [
                'name'               => 'Settings Folder',
                'icon'               => 'folder',
                'blueprint_group_id' => $systemGroup->id,
                'allow_children'     => true,
                'list_children'      => true,
                'show_in_tree'       => true,
                'locked'             => true,
            ]
        );
        $this->ensureAllowAllChildren($settingsFolder->id);
        $this->ensureNameField($settingsFolder, $textfield);

        // ── Items ─────────────────────────────────────────────────────────────

        // Root (hidden)
        $root = Item::firstOrCreate(
            ['id' => 1],
            [
                'blueprint_id' => $systemFolder->id,
                'parent_id'    => null,
                'status'       => 'published',
                'sort_order'   => 0,
                'path'         => '/1/',
            ]
        );
        $this->setItemName($root, $systemFolder, $textfield, [$en->id => 'Root', $de->id => 'Root']);

        // Content
        $content = Item::firstOrCreate(
            ['blueprint_id' => $contentFolder->id, 'parent_id' => $root->id],
            [
                'status'     => 'published',
                'sort_order' => 0,
                'path'       => '/1/' . ($root->id) . '/',
            ]
        );
        $content->path = '/1/' . $content->id . '/';
        $content->save();
        $this->setItemName($content, $contentFolder, $textfield, [$en->id => 'Content', $de->id => 'Content']);

        // Settings
        $settings = Item::firstOrCreate(
            ['blueprint_id' => $settingsFolder->id, 'parent_id' => $root->id],
            [
                'status'     => 'published',
                'sort_order' => 1,
                'path'       => '/1/' . ($root->id) . '/',
            ]
        );
        $settings->path = '/1/' . $settings->id . '/';
        $settings->save();
        $this->setItemName($settings, $settingsFolder, $textfield, [$en->id => 'Settings', $de->id => 'Einstellungen']);

        // ── User Group & Admin User ───────────────────────────────────────────

        $adminGroup = UserGroup::firstOrCreate(
            ['name' => 'Admin'],
            [
                'entry_item_id'       => $root->id,
                'can_create_users'    => true,
                'can_edit_users'      => true,
                'can_delete_users'    => true,
                'can_list_users'      => true,
                'can_create_blueprints' => true,
                'can_edit_blueprints'   => true,
                'can_delete_blueprints' => true,
                'can_list_blueprints'   => true,
                'can_create_groups'   => true,
                'can_edit_groups'     => true,
                'can_delete_groups'   => true,
                'can_list_groups'     => true,
            ]
        );

        if (!$adminGroup->allowedBlueprints()->exists()) {
            DB::table('user_group_allowed_blueprints')->insert([
                'user_group_id' => $adminGroup->id,
                'blueprint_id'  => null,
                'allow_all'     => true,
            ]);
        }

        User::firstOrCreate(
            ['email' => 'admin@marble.local'],
            [
                'name'          => 'Admin',
                'password'      => Hash::make('password'),
                'user_group_id' => $adminGroup->id,
                'language'      => 'en',
            ]
        );
    }

    private function ensureAllowAllChildren(int $blueprintId): void
    {
        $exists = DB::table('blueprint_allowed_children')
            ->where('blueprint_id', $blueprintId)
            ->exists();

        if (!$exists) {
            DB::table('blueprint_allowed_children')->insert([
                'blueprint_id'       => $blueprintId,
                'child_blueprint_id' => null,
                'allow_all'          => true,
            ]);
        }
    }

    private function ensureNameField(Blueprint $blueprint, ?FieldType $textfield): void
    {
        if (!$textfield) return;

        BlueprintField::firstOrCreate(
            ['blueprint_id' => $blueprint->id, 'identifier' => 'name'],
            [
                'name'          => 'Name',
                'field_type_id' => $textfield->id,
                'sort_order'    => -1,
                'translatable'  => true,
                'locked'        => false,
            ]
        );
    }

    private function setItemName(Item $item, Blueprint $blueprint, ?FieldType $textfield, array $valuesByLangId): void
    {
        if (!$textfield) return;

        $nameField = BlueprintField::where('blueprint_id', $blueprint->id)
            ->where('identifier', 'name')
            ->first();

        if (!$nameField) return;

        foreach ($valuesByLangId as $langId => $value) {
            ItemValue::firstOrCreate(
                ['item_id' => $item->id, 'blueprint_field_id' => $nameField->id, 'language_id' => $langId],
                ['value' => $value]
            );
        }
    }
}
