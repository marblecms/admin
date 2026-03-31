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

        $rootFolder = Blueprint::firstOrCreate(
            ['identifier' => 'root_folder'],
            [
                'name'               => 'Root Folder',
                'icon'               => 'folder',
                'blueprint_group_id' => $systemGroup->id,
                'allow_children'     => true,
                'list_children'      => true,
                'show_in_tree'       => false,
                'locked'             => true,
            ]
        );
        $this->ensureAllowAllChildren($rootFolder->id);
        $this->ensureNameField($rootFolder, $textfield);

        $systemFolder = Blueprint::firstOrCreate(
            ['identifier' => 'system_folder'],
            [
                'name'               => 'System Folder',
                'icon'               => 'folder',
                'blueprint_group_id' => $systemGroup->id,
                'allow_children'     => true,
                'list_children'      => true,
                'show_in_tree'       => true,
                'locked'             => true,
            ]
        );
        $this->ensureAllowAllChildren($systemFolder->id);
        $this->ensureNameField($systemFolder, $textfield);

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
                'icon'               => 'application_form',
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
            'identifier'    => 'subject',
            'name'          => 'Subject',
            'sort_order'    => 1,
            'translatable'  => true,
        ]);
        $this->ensureBlueprintField($contactForm, $textblock, [
            'identifier'    => 'message',
            'name'          => 'Message',
            'sort_order'    => 2,
            'translatable'  => true,
        ]);

        // ── Site Settings Blueprint ───────────────────────────────────────────

        $siteSettings = Blueprint::firstOrCreate(
            ['identifier' => 'site_settings'],
            [
                'name'               => 'Site Settings',
                'icon'               => 'cog',
                'blueprint_group_id' => $systemGroup->id,
                'allow_children'     => false,
                'list_children'      => false,
                'show_in_tree'       => true,
                'hide_system_fields' => true,
                'locked'             => false,
                'versionable'        => false,
                'schedulable'        => false,
            ]
        );

        // Internal name (hidden in UI via hide_system_fields, shown in tree)
        $this->ensureBlueprintField($siteSettings, $textfield,  ['identifier' => 'name', 'name' => 'Name', 'sort_order' => -1, 'translatable' => false]);

        // Branding
        $this->ensureBlueprintField($siteSettings, $textfield,  ['identifier' => 'site_name',  'name' => 'Site Name',  'sort_order' => 10, 'translatable' => true]);
        $this->ensureBlueprintField($siteSettings, $textfield,  ['identifier' => 'tagline',    'name' => 'Tagline',    'sort_order' => 20, 'translatable' => true]);
        $this->ensureBlueprintField($siteSettings, $fileFt,     ['identifier' => 'logo',       'name' => 'Logo',       'sort_order' => 30, 'translatable' => false]);
        $this->ensureBlueprintField($siteSettings, $fileFt,     ['identifier' => 'favicon',    'name' => 'Favicon',    'sort_order' => 40, 'translatable' => false]);

        // SEO
        $this->ensureBlueprintField($siteSettings, $textfield,  ['identifier' => 'meta_title_template', 'name' => 'Meta Title Template', 'sort_order' => 50, 'translatable' => true]);
        $this->ensureBlueprintField($siteSettings, $textblock,  ['identifier' => 'meta_description',    'name' => 'Meta Description',    'sort_order' => 60, 'translatable' => true]);
        $this->ensureBlueprintField($siteSettings, $fileFt,     ['identifier' => 'og_image',            'name' => 'OG Image',            'sort_order' => 70, 'translatable' => false]);
        $this->ensureBlueprintField($siteSettings, $textfield,  ['identifier' => 'robots',              'name' => 'Robots',              'sort_order' => 80, 'translatable' => false]);

        // Contact
        $this->ensureBlueprintField($siteSettings, $textblock,  ['identifier' => 'address', 'name' => 'Address', 'sort_order' => 100, 'translatable' => false]);
        $this->ensureBlueprintField($siteSettings, $textfield,  ['identifier' => 'phone',   'name' => 'Phone',   'sort_order' => 110, 'translatable' => false]);
        $this->ensureBlueprintField($siteSettings, $textfield,  ['identifier' => 'email',   'name' => 'E-Mail',  'sort_order' => 120, 'translatable' => true]);

        // Social Media
        $this->ensureBlueprintField($siteSettings, $textfield,  ['identifier' => 'instagram_url', 'name' => 'Instagram', 'sort_order' => 140, 'translatable' => false]);
        $this->ensureBlueprintField($siteSettings, $textfield,  ['identifier' => 'facebook_url',  'name' => 'Facebook',  'sort_order' => 150, 'translatable' => false]);
        $this->ensureBlueprintField($siteSettings, $textfield,  ['identifier' => 'linkedin_url',  'name' => 'LinkedIn',  'sort_order' => 160, 'translatable' => false]);

        // Footer
        $this->ensureBlueprintField($siteSettings, $textfield,  ['identifier' => 'copyright', 'name' => 'Copyright', 'sort_order' => 180, 'translatable' => true]);

        // ── Root Item Tree ────────────────────────────────────────────────────

        $root = $this->createItem($rootFolder, null, 'published', 0, '/1/', false);
        $root->id === 1 ?: null; // always id=1 on fresh install
        $this->setFieldValues($root, $rootFolder, $textfield, ['name' => ['en' => 'Root']]);

        $contentItem = $this->createItem($systemFolder, $root->id, 'published', 0);
        $this->setFieldValues($contentItem, $systemFolder, $textfield, ['name' => ['en' => 'Content']]);

        $settingsItem = $this->createItem($systemFolder, $root->id, 'published', 1);
        $this->setFieldValues($settingsItem, $systemFolder, $textfield, ['name' => ['en' => 'Settings']]);

        $siteSettingsItem = $this->createItem($siteSettings, $settingsItem->id, 'published', 0, null, false);
        $this->setFieldValues($siteSettingsItem, $siteSettings, $textfield, [
            'name'                 => ['en' => 'Site Settings'],
            'site_name'            => ['en' => 'My Website'],
            'tagline'              => ['en' => 'Building great things'],
            'meta_title_template'  => ['en' => '%title% | My Website'],
            'robots'               => ['en' => 'index, follow'],
            'phone'                => ['en' => '+1 234 567 890'],
            'email'                => ['en' => 'hello@mywebsite.com'],
            'instagram_url'        => ['en' => 'https://instagram.com/mywebsite'],
            'facebook_url'         => ['en' => 'https://facebook.com/mywebsite'],
            'linkedin_url'         => ['en' => 'https://linkedin.com/company/mywebsite'],
            'copyright'            => ['en' => '© 2024 My Website. All rights reserved.'],
        ]);
        $this->setFieldValues($siteSettingsItem, $siteSettings, $textblock, [
            'meta_description' => ['en' => 'We are a passionate team building great software.'],
            'address'          => ['en' => "123 Main Street\nCity, Country"],
        ]);

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

        // Our Customers — 3 fake customer child pages
        $customers1 = $this->createItem($simplePage, $customers->id, 'published', 0);
        $this->setFieldValues($customers1, $simplePage, $textfield, [
            'name' => ['en' => 'Acme Corp'],
            'slug' => ['en' => 'acme-corp'],
        ]);
        $this->setHtmlValue($customers1, $simplePage, $htmlblock, 'content', '<p>Acme Corp is a global leader in innovative solutions.</p>');

        $customers2 = $this->createItem($simplePage, $customers->id, 'published', 1);
        $this->setFieldValues($customers2, $simplePage, $textfield, [
            'name' => ['en' => 'Globex Industries'],
            'slug' => ['en' => 'globex-industries'],
        ]);
        $this->setHtmlValue($customers2, $simplePage, $htmlblock, 'content', '<p>Globex Industries powers manufacturing across 40 countries.</p>');

        $customers3 = $this->createItem($simplePage, $customers->id, 'published', 2);
        $this->setFieldValues($customers3, $simplePage, $textfield, [
            'name' => ['en' => 'Initech Solutions'],
            'slug' => ['en' => 'initech-solutions'],
        ]);
        $this->setHtmlValue($customers3, $simplePage, $htmlblock, 'content', '<p>Initech Solutions delivers enterprise software to Fortune 500 companies.</p>');

        // Contact
        $contactPage = $this->createItem($contactForm, $startpage->id, 'published', 3);
        $this->setFieldValues($contactPage, $contactForm, $textfield, [
            'name'    => ['en' => 'Contact'],
            'slug'    => ['en' => 'contact'],
            'subject' => ['en' => 'Get in touch'],
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
        // Ensure root_item_id and settings_item_id are always up to date (idempotent re-seed)
        $defaultSite->update([
            'root_item_id'     => $startpage->id,
            'settings_item_id' => $siteSettingsItem->id,
        ]);

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
