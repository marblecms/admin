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
use Marble\Admin\Models\Site;
use Marble\Admin\Models\User;
use Marble\Admin\Models\UserGroup;

class MarbleSeeder extends Seeder
{
    private Language $en;

    public function run(): void
    {
        $this->call(FieldTypeSeeder::class);

        // ── Languages ─────────────────────────────────────────────────────────
        $this->en = Language::firstOrCreate(['code' => 'en'], ['name' => 'English']);
        Language::firstOrCreate(['code' => 'de'], ['name' => 'Deutsch']);
        Language::firstOrCreate(['code' => 'sk'], ['name' => 'Slovenčina']);

        // ── Field Type references ──────────────────────────────────────────────
        $textfield  = FieldType::where('identifier', 'textfield')->first();
        $htmlblock  = FieldType::where('identifier', 'htmlblock')->first();
        $repeater   = FieldType::where('identifier', 'repeater')->first();
        $fileFt     = FieldType::where('identifier', 'file')->first();
        $checkbox   = FieldType::where('identifier', 'checkbox')->first();
        $textblock  = FieldType::where('identifier', 'textblock')->first();

        // ── Blueprint Groups ──────────────────────────────────────────────────
        $systemGroup  = BlueprintGroup::firstOrCreate(['name' => 'System']);
        $contentGroup = BlueprintGroup::firstOrCreate(['name' => 'Content']);
        $formGroup    = BlueprintGroup::firstOrCreate(['name' => 'Forms']);

        // ── System Blueprints ─────────────────────────────────────────────────

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

        // ── SimplePage Blueprint ──────────────────────────────────────────────

        $simplePage = Blueprint::firstOrCreate(
            ['identifier' => 'simple_page'],
            [
                'name'               => 'Simple Page',
                'icon'               => 'page',
                'blueprint_group_id' => $contentGroup->id,
                'allow_children'     => true,
                'list_children'      => false,
                'show_in_tree'       => true,
                'versionable'        => true,
                'schedulable'        => true,
            ]
        );
        $this->ensureAllowAllChildren($simplePage->id);

        $this->ensureBlueprintField($simplePage, $textfield, [
            'identifier' => 'name', 'name' => 'Name', 'sort_order' => -1, 'translatable' => true,
        ]);
        $this->ensureBlueprintField($simplePage, $textfield, [
            'identifier' => 'slug', 'name' => 'Slug', 'sort_order' => 0, 'translatable' => true,
        ]);
        $this->ensureBlueprintField($simplePage, $htmlblock, [
            'identifier' => 'content', 'name' => 'Content', 'sort_order' => 1, 'translatable' => true,
        ]);
        $this->ensureBlueprintField($simplePage, $repeater, [
            'identifier'    => 'meta',
            'name'          => 'Meta',
            'sort_order'    => 2,
            'translatable'  => false,
            'configuration' => ['sub_fields' => [
                ['name' => 'Key',   'identifier' => 'key'],
                ['name' => 'Value', 'identifier' => 'value'],
            ]],
        ]);
        $this->ensureBlueprintField($simplePage, $fileFt, [
            'identifier' => 'file', 'name' => 'File', 'sort_order' => 3, 'translatable' => false,
        ]);

        // ── ContactForm Blueprint ─────────────────────────────────────────────

        $contactForm = Blueprint::firstOrCreate(
            ['identifier' => 'contact_form'],
            [
                'name'               => 'Contact Form',
                'icon'               => 'page',
                'blueprint_group_id' => $formGroup->id,
                'allow_children'     => false,
                'show_in_tree'       => true,
                'is_form'            => true,
                'versionable'        => false,
                'schedulable'        => false,
            ]
        );

        $this->ensureBlueprintField($contactForm, $textfield, [
            'identifier' => 'name', 'name' => 'Name', 'sort_order' => -1, 'translatable' => true,
        ]);
        $this->ensureBlueprintField($contactForm, $textfield, [
            'identifier' => 'slug', 'name' => 'Slug', 'sort_order' => 0, 'translatable' => true,
        ]);
        $this->ensureBlueprintField($contactForm, $textfield, [
            'identifier'    => 'form_title',
            'name'          => 'Form Title',
            'sort_order'    => 1,
            'translatable'  => true,
        ]);
        $this->ensureBlueprintField($contactForm, $textblock, [
            'identifier'    => 'intro_text',
            'name'          => 'Intro Text',
            'sort_order'    => 2,
            'translatable'  => true,
        ]);
        $this->ensureBlueprintField($contactForm, $checkbox, [
            'identifier'    => 'show_phone',
            'name'          => 'Show phone field',
            'sort_order'    => 3,
            'translatable'  => false,
        ]);

        // ── Root Item Tree ────────────────────────────────────────────────────

        $root = $this->createItem($systemFolder, null, 'published', 0, '/1/', false);
        $root->id === 1 ?: null; // always id=1 on fresh install
        $this->setFieldValues($root, $systemFolder, $textfield, ['name' => ['en' => 'Root']]);

        $contentItem = $this->createItem($contentFolder, $root->id, 'published', 0);
        $this->setFieldValues($contentItem, $contentFolder, $textfield, ['name' => ['en' => 'Content']]);

        $settingsItem = $this->createItem($settingsFolder, $root->id, 'published', 1);
        $this->setFieldValues($settingsItem, $settingsFolder, $textfield, ['name' => ['en' => 'Settings']]);

        // Startpage — site root, slug is empty, not shown in nav
        $startpage = $this->createItem($simplePage, $contentItem->id, 'published', 0, null, false);
        $this->setFieldValues($startpage, $simplePage, $textfield, [
            'name' => ['en' => 'Startpage'],
            'slug' => ['en' => ''],
        ]);

        // About Us
        $about = $this->createItem($simplePage, $startpage->id, 'published', 1);
        $this->setFieldValues($about, $simplePage, $textfield, [
            'name' => ['en' => 'About Us'],
            'slug' => ['en' => 'about-us'],
        ]);
        $this->setHtmlValue($about, $simplePage, $htmlblock, 'content', '<p>We are a passionate team building great software.</p>');

        // Our Customers
        $customers = $this->createItem($simplePage, $startpage->id, 'published', 2);
        $this->setFieldValues($customers, $simplePage, $textfield, [
            'name' => ['en' => 'Our Customers'],
            'slug' => ['en' => 'our-customers'],
        ]);
        $this->setHtmlValue($customers, $simplePage, $htmlblock, 'content', '<p>We work with clients across many industries.</p>');

        // Contact
        $contactPage = $this->createItem($contactForm, $startpage->id, 'published', 3);
        $this->setFieldValues($contactPage, $contactForm, $textfield, [
            'name'       => ['en' => 'Contact'],
            'slug'       => ['en' => 'contact'],
            'form_title' => ['en' => 'Get in touch'],
        ]);

        // ── Default Site ──────────────────────────────────────────────────────

        $defaultSite = Site::firstOrCreate(
            ['is_default' => true],
            [
                'name'         => 'Default',
                'domain'       => null,
                'root_item_id' => $startpage->id,
                'active'       => true,
            ]
        );
        // Ensure root_item_id is always up to date (idempotent re-seed)
        $defaultSite->update(['root_item_id' => $startpage->id]);

        // ── User Group & Admin User ───────────────────────────────────────────

        $adminGroup = UserGroup::firstOrCreate(
            ['name' => 'Admin'],
            [
                'entry_item_id'           => $root->id,
                'can_create_users'        => true,
                'can_edit_users'          => true,
                'can_delete_users'        => true,
                'can_list_users'          => true,
                'can_create_blueprints'   => true,
                'can_edit_blueprints'     => true,
                'can_delete_blueprints'   => true,
                'can_list_blueprints'     => true,
                'can_create_groups'       => true,
                'can_edit_groups'         => true,
                'can_delete_groups'       => true,
                'can_list_groups'         => true,
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
            ['email' => 'admin@admin'],
            [
                'name'          => 'Admin',
                'password'      => Hash::make('admin'),
                'user_group_id' => $adminGroup->id,
                'language'      => 'en',
            ]
        );
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function createItem(
        Blueprint $blueprint,
        ?int $parentId,
        string $status = 'published',
        int $sortOrder = 0,
        ?string $forcePath = null,
        bool $showInNav = true,
    ): Item {
        $item = Item::create([
            'blueprint_id' => $blueprint->id,
            'parent_id'    => $parentId,
            'status'       => $status,
            'sort_order'   => $sortOrder,
            'show_in_nav'  => $showInNav,
            'path'         => $forcePath ?? '/tmp/',
        ]);

        if ($forcePath === null) {
            // Build path: walk up to root
            $path = '/' . $item->id . '/';
            $pid  = $parentId;
            while ($pid) {
                $parent = Item::find($pid);
                $path   = '/' . $parent->id . $path;
                $pid    = $parent->parent_id;
            }
            $item->path = $path;
            $item->save();
        }

        return $item;
    }

    private function setFieldValues(Item $item, Blueprint $blueprint, FieldType $fieldType, array $fieldValues): void
    {
        foreach ($fieldValues as $identifier => $localeValues) {
            $field = BlueprintField::where('blueprint_id', $blueprint->id)
                ->where('identifier', $identifier)
                ->first();

            if (!$field) continue;

            foreach ($localeValues as $langCode => $value) {
                $lang = Language::where('code', $langCode)->first();
                if (!$lang) continue;

                ItemValue::firstOrCreate(
                    ['item_id' => $item->id, 'blueprint_field_id' => $field->id, 'language_id' => $lang->id],
                    ['value' => $value]
                );
            }
        }
    }

    private function setHtmlValue(Item $item, Blueprint $blueprint, ?FieldType $fieldType, string $identifier, string $html): void
    {
        if (!$fieldType) return;

        $field = BlueprintField::where('blueprint_id', $blueprint->id)
            ->where('identifier', $identifier)
            ->first();

        if (!$field) return;

        ItemValue::firstOrCreate(
            ['item_id' => $item->id, 'blueprint_field_id' => $field->id, 'language_id' => $this->en->id],
            ['value' => $html]
        );
    }

    private function ensureBlueprintField(Blueprint $blueprint, ?FieldType $fieldType, array $attrs): void
    {
        if (!$fieldType) return;

        $identifier    = $attrs['identifier'];
        $configuration = $attrs['configuration'] ?? null;

        $field = BlueprintField::firstOrCreate(
            ['blueprint_id' => $blueprint->id, 'identifier' => $identifier],
            array_merge([
                'name'          => $attrs['name'],
                'field_type_id' => $fieldType->id,
                'sort_order'    => $attrs['sort_order'] ?? 0,
                'translatable'  => $attrs['translatable'] ?? false,
                'locked'        => false,
            ], $configuration ? ['configuration' => $configuration] : [])
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
        $this->ensureBlueprintField($blueprint, $textfield, [
            'identifier'   => 'name',
            'name'         => 'Name',
            'sort_order'   => -1,
            'translatable' => true,
        ]);
    }
}
