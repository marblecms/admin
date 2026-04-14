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
    protected Language $en;
    protected FieldType $textfield;
    protected FieldType $htmlblock;
    protected FieldType $textblock;

    public function run(): void
    {
        $this->call(FieldTypeSeeder::class);

        // ── Languages ─────────────────────────────────────────────────────────
        $this->en = Language::firstOrCreate(['code' => 'en'], ['name' => 'English']);

        // ── Field Type references ──────────────────────────────────────────────
        $this->textfield = FieldType::where('identifier', 'textfield')->first();
        $this->htmlblock  = FieldType::where('identifier', 'htmlblock')->first();
        $this->textblock  = FieldType::where('identifier', 'textblock')->first();
        $fileFt           = FieldType::where('identifier', 'file')->first();

        // ── Blueprint Groups ──────────────────────────────────────────────────
        $systemGroup  = BlueprintGroup::firstOrCreate(['name' => 'System']);
        $contentGroup = BlueprintGroup::firstOrCreate(['name' => 'Content']);

        // ════════════════════════════════════════════════════════════════════════
        // SYSTEM BLUEPRINTS
        // ════════════════════════════════════════════════════════════════════════

        $rootFolder = Blueprint::firstOrCreate(
            ['identifier' => 'root_folder'],
            ['name' => 'Root Folder', 'icon' => 'folder', 'blueprint_group_id' => $systemGroup->id,
             'allow_children' => true, 'list_children' => true, 'show_in_tree' => false, 'locked' => true]
        );
        $this->ensureAllowAllChildren($rootFolder->id);
        $this->ensureField($rootFolder, $this->textfield, ['identifier' => 'name', 'name' => 'Name', 'sort_order' => -1, 'translatable' => true]);

        $systemFolder = Blueprint::firstOrCreate(
            ['identifier' => 'system_folder'],
            ['name' => 'System Folder', 'icon' => 'folder', 'blueprint_group_id' => $systemGroup->id,
             'allow_children' => true, 'list_children' => true, 'show_in_tree' => true, 'locked' => true]
        );
        $this->ensureAllowAllChildren($systemFolder->id);
        $this->ensureField($systemFolder, $this->textfield, ['identifier' => 'name', 'name' => 'Name', 'sort_order' => -1, 'translatable' => true]);

        // ── Site Settings Blueprint ───────────────────────────────────────────
        $siteSettings = Blueprint::firstOrCreate(
            ['identifier' => 'site_settings'],
            ['name' => 'Site Settings', 'icon' => 'cog', 'blueprint_group_id' => $systemGroup->id,
             'allow_children' => false, 'show_in_tree' => true, 'locked' => false, 'versionable' => false]
        );
        foreach ([
            ['identifier' => 'name',               'name' => 'Name',               'sort_order' => -1,  'translatable' => false],
            ['identifier' => 'site_name',           'name' => 'Site Name',          'sort_order' => 10,  'translatable' => true],
            ['identifier' => 'tagline',             'name' => 'Tagline',            'sort_order' => 20,  'translatable' => true],
            ['identifier' => 'meta_title_template', 'name' => 'Meta Title Template','sort_order' => 50,  'translatable' => true],
            ['identifier' => 'robots',              'name' => 'Robots',             'sort_order' => 80,  'translatable' => false],
            ['identifier' => 'phone',               'name' => 'Phone',              'sort_order' => 110, 'translatable' => false],
            ['identifier' => 'email',               'name' => 'E-Mail',             'sort_order' => 120, 'translatable' => true],
            ['identifier' => 'instagram_url',       'name' => 'Instagram',          'sort_order' => 140, 'translatable' => false],
            ['identifier' => 'facebook_url',        'name' => 'Facebook',           'sort_order' => 150, 'translatable' => false],
            ['identifier' => 'linkedin_url',        'name' => 'LinkedIn',           'sort_order' => 160, 'translatable' => false],
            ['identifier' => 'copyright',           'name' => 'Copyright',          'sort_order' => 180, 'translatable' => true],
        ] as $f) {
            $this->ensureField($siteSettings, $this->textfield, $f);
        }
        $this->ensureField($siteSettings, $this->textblock, ['identifier' => 'meta_description', 'name' => 'Meta Description', 'sort_order' => 60,  'translatable' => true]);
        $this->ensureField($siteSettings, $this->textblock, ['identifier' => 'address',          'name' => 'Address',          'sort_order' => 100, 'translatable' => false]);
        $this->ensureField($siteSettings, $fileFt,          ['identifier' => 'logo',             'name' => 'Logo',             'sort_order' => 30,  'translatable' => false]);
        $this->ensureField($siteSettings, $fileFt,          ['identifier' => 'og_image',         'name' => 'OG Image',         'sort_order' => 70,  'translatable' => false]);

        // ── Home Blueprint ────────────────────────────────────────────────────
        $home = Blueprint::firstOrCreate(
            ['identifier' => 'home'],
            ['name' => 'Home', 'icon' => 'house', 'blueprint_group_id' => $contentGroup->id,
             'allow_children' => false, 'show_in_tree' => true, 'versionable' => true]
        );
        foreach ([
            ['identifier' => 'name',          'name' => 'Name',          'sort_order' => -1, 'translatable' => true],
            ['identifier' => 'slug',          'name' => 'Slug',          'sort_order' => 0,  'translatable' => true],
            ['identifier' => 'hero_title',    'name' => 'Hero Title',    'sort_order' => 1,  'translatable' => true],
            ['identifier' => 'hero_subtitle', 'name' => 'Hero Subtitle', 'sort_order' => 2,  'translatable' => true],
            ['identifier' => 'intro_title',   'name' => 'Intro Title',   'sort_order' => 3,  'translatable' => true],
        ] as $f) {
            $this->ensureField($home, $this->textfield, $f);
        }
        $this->ensureField($home, $this->htmlblock, ['identifier' => 'intro_text', 'name' => 'Intro Text', 'sort_order' => 4, 'translatable' => true]);

        // ════════════════════════════════════════════════════════════════════════
        // ITEM TREE
        // ════════════════════════════════════════════════════════════════════════

        $root = $this->item($rootFolder, null, 0, '/1/', false);
        $this->vals($root, ['name' => 'Root']);

        $settingsFolder = $this->item($systemFolder, $root->id, 1, null, false);
        $this->vals($settingsFolder, ['name' => 'Settings']);

        $contentFolder = $this->item($systemFolder, $root->id, 0, null, false);
        $this->vals($contentFolder, ['name' => 'Content']);

        $siteSettingsItem = $this->item($siteSettings, $settingsFolder->id, 0, null, false);
        $this->vals($siteSettingsItem, ['name' => 'Site Settings']);

        $startpage = $this->item($home, $contentFolder->id, 0, null, false);
        $this->vals($startpage, ['name' => 'Home', 'slug' => '']);

        // ════════════════════════════════════════════════════════════════════════
        // SITE & USERS
        // ════════════════════════════════════════════════════════════════════════

        $defaultSite = Site::firstOrCreate(
            ['is_default' => true],
            ['name' => 'Marble CMS', 'domain' => null, 'root_item_id' => $startpage->id, 'active' => true]
        );
        $defaultSite->update(['root_item_id' => $startpage->id, 'settings_item_id' => $siteSettingsItem->id]);

        $adminGroup = UserGroup::firstOrCreate(
            ['name' => 'Admin'],
            [
                'entry_item_id'         => $root->id,
                'can_create_users'      => true, 'can_edit_users'      => true, 'can_delete_users'      => true, 'can_list_users'      => true,
                'can_create_blueprints' => true, 'can_edit_blueprints' => true, 'can_delete_blueprints' => true, 'can_list_blueprints' => true,
                'can_create_groups'     => true, 'can_edit_groups'     => true, 'can_delete_groups'     => true, 'can_list_groups'     => true,
            ]
        );
        if (!$adminGroup->allowedBlueprints()->exists()) {
            DB::table('user_group_allowed_blueprints')->insert([
                'user_group_id' => $adminGroup->id, 'blueprint_id' => null, 'allow_all' => true,
                'can_create' => true, 'can_read' => true, 'can_update' => true, 'can_delete' => true,
            ]);
        }

        User::firstOrCreate(
            ['email' => 'admin@admin'],
            ['name' => 'Admin', 'password' => Hash::make('admin'), 'user_group_id' => $adminGroup->id, 'language' => 'en']
        );
    }

    // ════════════════════════════════════════════════════════════════════════════
    // HELPERS
    // ════════════════════════════════════════════════════════════════════════════

    protected function item(Blueprint $blueprint, ?int $parentId, int $sortOrder = 0, ?string $forcePath = null, bool $showInNav = true, string $status = 'published', ?int $workflowStepId = null): Item
    {
        $item = Item::create([
            'blueprint_id'             => $blueprint->id,
            'parent_id'                => $parentId,
            'status'                   => $status,
            'sort_order'               => $sortOrder,
            'show_in_nav'              => $showInNav,
            'path'                     => $forcePath ?? '/tmp/',
            'current_workflow_step_id' => $workflowStepId,
        ]);

        if ($forcePath === null) {
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

    protected function vals(Item $item, array $fields): void
    {
        foreach ($fields as $identifier => $value) {
            $field = BlueprintField::where('blueprint_id', $item->blueprint_id)
                ->where('identifier', $identifier)
                ->first();
            if (!$field) continue;

            ItemValue::firstOrCreate(
                ['item_id' => $item->id, 'blueprint_field_id' => $field->id, 'language_id' => $this->en->id],
                ['value' => $value]
            );
        }
    }

    protected function html(Item $item, Blueprint $blueprint, string $identifier, string $html): void
    {
        $field = BlueprintField::where('blueprint_id', $blueprint->id)
            ->where('identifier', $identifier)
            ->first();
        if (!$field) return;

        ItemValue::firstOrCreate(
            ['item_id' => $item->id, 'blueprint_field_id' => $field->id, 'language_id' => $this->en->id],
            ['value' => $html]
        );
    }

    protected function ensureField(Blueprint $blueprint, ?FieldType $fieldType, array $attrs): void
    {
        if (!$fieldType) return;
        $config = $attrs['configuration'] ?? null;

        BlueprintField::firstOrCreate(
            ['blueprint_id' => $blueprint->id, 'identifier' => $attrs['identifier']],
            array_merge([
                'name'          => $attrs['name'],
                'field_type_id' => $fieldType->id,
                'sort_order'    => $attrs['sort_order'] ?? 0,
                'translatable'  => $attrs['translatable'] ?? false,
                'locked'        => false,
            ], $config ? ['configuration' => $config] : [])
        );
    }

    protected function ensureAllowAllChildren(int $blueprintId): void
    {
        if (!DB::table('blueprint_allowed_children')->where('blueprint_id', $blueprintId)->exists()) {
            DB::table('blueprint_allowed_children')->insert(['blueprint_id' => $blueprintId, 'child_blueprint_id' => null, 'allow_all' => true]);
        }
    }
}
