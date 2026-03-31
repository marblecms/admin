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
use Marble\Admin\Models\PortalUser;
use Marble\Admin\Models\Site;
use Marble\Admin\Models\User;
use Marble\Admin\Models\UserGroup;
use Marble\Admin\Models\Workflow;
use Marble\Admin\Models\WorkflowStep;

class MarbleSeeder extends Seeder
{
    private Language $en;
    private FieldType $textfield;
    private FieldType $htmlblock;
    private FieldType $textblock;

    public function run(): void
    {
        $this->call(FieldTypeSeeder::class);

        // ── Languages ─────────────────────────────────────────────────────────
        $this->en = Language::firstOrCreate(['code' => 'en'], ['name' => 'English']);
        Language::firstOrCreate(['code' => 'de'], ['name' => 'Deutsch']);
        Language::firstOrCreate(['code' => 'sk'], ['name' => 'Slovenčina']);

        // ── Field Type references ──────────────────────────────────────────────
        $this->textfield = FieldType::where('identifier', 'textfield')->first();
        $this->htmlblock  = FieldType::where('identifier', 'htmlblock')->first();
        $this->textblock  = FieldType::where('identifier', 'textblock')->first();
        $repeater         = FieldType::where('identifier', 'repeater')->first();
        $fileFt           = FieldType::where('identifier', 'file')->first();
        $dateFt           = FieldType::where('identifier', 'date')->first();

        // ── Blueprint Groups ──────────────────────────────────────────────────
        $systemGroup  = BlueprintGroup::firstOrCreate(['name' => 'System']);
        $contentGroup = BlueprintGroup::firstOrCreate(['name' => 'Content']);
        $blogGroup    = BlueprintGroup::firstOrCreate(['name' => 'Blog']);
        $shopGroup    = BlueprintGroup::firstOrCreate(['name' => 'Products']);
        $formGroup    = BlueprintGroup::firstOrCreate(['name' => 'Forms']);
        $portalGroup  = BlueprintGroup::firstOrCreate(['name' => 'Portal']);

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
            ['identifier' => 'name',                  'name' => 'Name',                  'sort_order' => -1,  'translatable' => false],
            ['identifier' => 'site_name',              'name' => 'Site Name',              'sort_order' => 10,  'translatable' => true],
            ['identifier' => 'tagline',                'name' => 'Tagline',                'sort_order' => 20,  'translatable' => true],
            ['identifier' => 'meta_title_template',    'name' => 'Meta Title Template',    'sort_order' => 50,  'translatable' => true],
            ['identifier' => 'robots',                 'name' => 'Robots',                 'sort_order' => 80,  'translatable' => false],
            ['identifier' => 'phone',                  'name' => 'Phone',                  'sort_order' => 110, 'translatable' => false],
            ['identifier' => 'email',                  'name' => 'E-Mail',                 'sort_order' => 120, 'translatable' => true],
            ['identifier' => 'instagram_url',          'name' => 'Instagram',              'sort_order' => 140, 'translatable' => false],
            ['identifier' => 'facebook_url',           'name' => 'Facebook',               'sort_order' => 150, 'translatable' => false],
            ['identifier' => 'linkedin_url',           'name' => 'LinkedIn',               'sort_order' => 160, 'translatable' => false],
            ['identifier' => 'copyright',              'name' => 'Copyright',              'sort_order' => 180, 'translatable' => true],
        ] as $f) {
            $this->ensureField($siteSettings, $this->textfield, $f);
        }
        $this->ensureField($siteSettings, $this->textblock,  ['identifier' => 'meta_description', 'name' => 'Meta Description', 'sort_order' => 60, 'translatable' => true]);
        $this->ensureField($siteSettings, $this->textblock,  ['identifier' => 'address',          'name' => 'Address',          'sort_order' => 100, 'translatable' => false]);
        $this->ensureField($siteSettings, $fileFt,           ['identifier' => 'logo',             'name' => 'Logo',             'sort_order' => 30, 'translatable' => false]);
        $this->ensureField($siteSettings, $fileFt,           ['identifier' => 'og_image',         'name' => 'OG Image',         'sort_order' => 70, 'translatable' => false]);

        // ════════════════════════════════════════════════════════════════════════
        // CONTENT BLUEPRINTS
        // ════════════════════════════════════════════════════════════════════════

        // ── Home ──────────────────────────────────────────────────────────────
        $home = Blueprint::firstOrCreate(
            ['identifier' => 'home'],
            ['name' => 'Home', 'icon' => 'house', 'blueprint_group_id' => $contentGroup->id,
             'allow_children' => true, 'show_in_tree' => true, 'versionable' => true]
        );
        $this->ensureAllowAllChildren($home->id);
        foreach ([
            ['identifier' => 'name',           'name' => 'Name',           'sort_order' => -1, 'translatable' => true],
            ['identifier' => 'slug',           'name' => 'Slug',           'sort_order' => 0,  'translatable' => true],
            ['identifier' => 'hero_title',     'name' => 'Hero Title',     'sort_order' => 1,  'translatable' => true],
            ['identifier' => 'hero_subtitle',  'name' => 'Hero Subtitle',  'sort_order' => 2,  'translatable' => true],
            ['identifier' => 'intro_title',    'name' => 'Intro Title',    'sort_order' => 3,  'translatable' => true],
        ] as $f) {
            $this->ensureField($home, $this->textfield, $f);
        }
        $this->ensureField($home, $this->htmlblock, ['identifier' => 'intro_text', 'name' => 'Intro Text', 'sort_order' => 4, 'translatable' => true]);

        // ── Simple Page ───────────────────────────────────────────────────────
        $simplePage = Blueprint::firstOrCreate(
            ['identifier' => 'simple_page'],
            ['name' => 'Simple Page', 'icon' => 'page', 'blueprint_group_id' => $contentGroup->id,
             'allow_children' => true, 'show_in_tree' => true, 'versionable' => true, 'schedulable' => true]
        );
        $this->ensureAllowAllChildren($simplePage->id);
        foreach ([
            ['identifier' => 'name', 'name' => 'Name', 'sort_order' => -1, 'translatable' => true],
            ['identifier' => 'slug', 'name' => 'Slug', 'sort_order' => 0,  'translatable' => true],
        ] as $f) {
            $this->ensureField($simplePage, $this->textfield, $f);
        }
        $this->ensureField($simplePage, $this->htmlblock, ['identifier' => 'content', 'name' => 'Content', 'sort_order' => 1, 'translatable' => true]);

        // ── Team Member ───────────────────────────────────────────────────────
        $teamMember = Blueprint::firstOrCreate(
            ['identifier' => 'team_member'],
            ['name' => 'Team Member', 'icon' => 'user', 'blueprint_group_id' => $contentGroup->id,
             'allow_children' => false, 'show_in_tree' => true, 'versionable' => true]
        );
        foreach ([
            ['identifier' => 'name',         'name' => 'Name',         'sort_order' => -1, 'translatable' => false],
            ['identifier' => 'slug',         'name' => 'Slug',         'sort_order' => 0,  'translatable' => true],
            ['identifier' => 'role',         'name' => 'Role',         'sort_order' => 1,  'translatable' => true],
            ['identifier' => 'linkedin_url', 'name' => 'LinkedIn URL', 'sort_order' => 4,  'translatable' => false],
        ] as $f) {
            $this->ensureField($teamMember, $this->textfield, $f);
        }
        $this->ensureField($teamMember, $this->htmlblock, ['identifier' => 'bio',   'name' => 'Bio',   'sort_order' => 2, 'translatable' => true]);
        $this->ensureField($teamMember, $fileFt,          ['identifier' => 'photo', 'name' => 'Photo', 'sort_order' => 3, 'translatable' => false]);

        // ── Blog Index ────────────────────────────────────────────────────────
        $blogIndex = Blueprint::firstOrCreate(
            ['identifier' => 'blog_index'],
            ['name' => 'Blog Index', 'icon' => 'book', 'blueprint_group_id' => $blogGroup->id,
             'allow_children' => true, 'list_children' => true, 'show_in_tree' => true]
        );
        $this->ensureAllowAllChildren($blogIndex->id);
        foreach ([
            ['identifier' => 'name', 'name' => 'Name', 'sort_order' => -1, 'translatable' => true],
            ['identifier' => 'slug', 'name' => 'Slug', 'sort_order' => 0,  'translatable' => true],
        ] as $f) {
            $this->ensureField($blogIndex, $this->textfield, $f);
        }
        $this->ensureField($blogIndex, $this->textblock, ['identifier' => 'intro', 'name' => 'Intro', 'sort_order' => 1, 'translatable' => true]);

        // ── Blog Post ─────────────────────────────────────────────────────────
        $blogPost = Blueprint::firstOrCreate(
            ['identifier' => 'blog_post'],
            ['name' => 'Blog Post', 'icon' => 'page_white_text', 'blueprint_group_id' => $blogGroup->id,
             'allow_children' => false, 'show_in_tree' => true, 'versionable' => true, 'schedulable' => true, 'api_public' => true]
        );
        foreach ([
            ['identifier' => 'name',   'name' => 'Name',   'sort_order' => -1, 'translatable' => true],
            ['identifier' => 'slug',   'name' => 'Slug',   'sort_order' => 0,  'translatable' => true],
            ['identifier' => 'author', 'name' => 'Author', 'sort_order' => 3,  'translatable' => false],
        ] as $f) {
            $this->ensureField($blogPost, $this->textfield, $f);
        }
        $this->ensureField($blogPost, $this->textblock, ['identifier' => 'teaser',  'name' => 'Teaser',  'sort_order' => 1, 'translatable' => true]);
        $this->ensureField($blogPost, $this->htmlblock, ['identifier' => 'content', 'name' => 'Content', 'sort_order' => 2, 'translatable' => true]);
        $this->ensureField($blogPost, $dateFt,          ['identifier' => 'publish_date', 'name' => 'Publish Date', 'sort_order' => 4, 'translatable' => false]);

        // ── Blog Editorial Workflow ────────────────────────────────────────────
        $editorialWorkflow = Workflow::firstOrCreate(['name' => 'Blog Editorial']);

        $stepWritten = WorkflowStep::firstOrCreate(
            ['workflow_id' => $editorialWorkflow->id, 'name' => 'Written'],
            ['sort_order' => 1, 'reject_enabled' => false]
        );
        $stepReview = WorkflowStep::firstOrCreate(
            ['workflow_id' => $editorialWorkflow->id, 'name' => 'In Review'],
            ['sort_order' => 2, 'reject_enabled' => true]
        );
        $stepApproved = WorkflowStep::firstOrCreate(
            ['workflow_id' => $editorialWorkflow->id, 'name' => 'Approved'],
            ['sort_order' => 3, 'reject_enabled' => true]
        );

        // Wire reject targets once steps exist
        $stepReview->update(['reject_to_step_id' => $stepWritten->id]);
        $stepApproved->update(['reject_to_step_id' => $stepReview->id]);

        // Assign workflow to blog_post blueprint
        $blogPost->update(['workflow_id' => $editorialWorkflow->id]);

        // ── Product Category ──────────────────────────────────────────────────
        $productCategory = Blueprint::firstOrCreate(
            ['identifier' => 'product_category'],
            ['name' => 'Product Category', 'icon' => 'folder', 'blueprint_group_id' => $shopGroup->id,
             'allow_children' => true, 'list_children' => true, 'show_in_tree' => true]
        );
        $this->ensureAllowAllChildren($productCategory->id);
        foreach ([
            ['identifier' => 'name', 'name' => 'Name', 'sort_order' => -1, 'translatable' => true],
            ['identifier' => 'slug', 'name' => 'Slug', 'sort_order' => 0,  'translatable' => true],
            ['identifier' => 'icon', 'name' => 'Icon (emoji)', 'sort_order' => 2,  'translatable' => false],
        ] as $f) {
            $this->ensureField($productCategory, $this->textfield, $f);
        }
        $this->ensureField($productCategory, $this->textblock, ['identifier' => 'description', 'name' => 'Description', 'sort_order' => 1, 'translatable' => true]);

        // ── Product ───────────────────────────────────────────────────────────
        $product = Blueprint::firstOrCreate(
            ['identifier' => 'product'],
            ['name' => 'Product', 'icon' => 'package', 'blueprint_group_id' => $shopGroup->id,
             'allow_children' => false, 'show_in_tree' => true, 'versionable' => true, 'api_public' => true]
        );
        foreach ([
            ['identifier' => 'name',    'name' => 'Name',    'sort_order' => -1, 'translatable' => true],
            ['identifier' => 'slug',    'name' => 'Slug',    'sort_order' => 0,  'translatable' => true],
            ['identifier' => 'tagline', 'name' => 'Tagline', 'sort_order' => 1,  'translatable' => true],
            ['identifier' => 'price',   'name' => 'Price',   'sort_order' => 3,  'translatable' => false],
            ['identifier' => 'badge',   'name' => 'Badge (e.g. Popular)',   'sort_order' => 4, 'translatable' => false],
        ] as $f) {
            $this->ensureField($product, $this->textfield, $f);
        }
        $this->ensureField($product, $this->htmlblock, ['identifier' => 'description', 'name' => 'Description', 'sort_order' => 2, 'translatable' => true]);
        $this->ensureField($product, $this->textblock, ['identifier' => 'features',    'name' => 'Features (one per line)', 'sort_order' => 5, 'translatable' => true]);

        // ── Contact Form ──────────────────────────────────────────────────────
        $contactForm = Blueprint::firstOrCreate(
            ['identifier' => 'contact_form'],
            ['name' => 'Contact Form', 'icon' => 'application_form', 'blueprint_group_id' => $formGroup->id,
             'allow_children' => false, 'show_in_tree' => true, 'is_form' => true, 'versionable' => false]
        );
        foreach ([
            ['identifier' => 'name',    'name' => 'Name',    'sort_order' => -1, 'translatable' => true],
            ['identifier' => 'slug',    'name' => 'Slug',    'sort_order' => 0,  'translatable' => true],
            ['identifier' => 'subject', 'name' => 'Subject', 'sort_order' => 1,  'translatable' => true],
        ] as $f) {
            $this->ensureField($contactForm, $this->textfield, $f);
        }
        $this->ensureField($contactForm, $this->textblock, ['identifier' => 'message', 'name' => 'Message', 'sort_order' => 2, 'translatable' => true]);

        // ── Intranet Page ─────────────────────────────────────────────────────
        $intranetPage = Blueprint::firstOrCreate(
            ['identifier' => 'intranet_page'],
            ['name' => 'Intranet Page', 'icon' => 'lock', 'blueprint_group_id' => $portalGroup->id,
             'allow_children' => true, 'show_in_tree' => true, 'versionable' => true]
        );
        $this->ensureAllowAllChildren($intranetPage->id);
        foreach ([
            ['identifier' => 'name', 'name' => 'Name', 'sort_order' => -1, 'translatable' => true],
            ['identifier' => 'slug', 'name' => 'Slug', 'sort_order' => 0,  'translatable' => true],
        ] as $f) {
            $this->ensureField($intranetPage, $this->textfield, $f);
        }
        $this->ensureField($intranetPage, $this->htmlblock, ['identifier' => 'content', 'name' => 'Content', 'sort_order' => 1, 'translatable' => true]);

        // ════════════════════════════════════════════════════════════════════════
        // ITEM TREE
        // ════════════════════════════════════════════════════════════════════════

        $root = $this->item($rootFolder, null, 0, '/1/', false);
        $this->vals($root, ['name' => 'Root']);

        $contentFolder = $this->item($systemFolder, $root->id, 0, null, false);
        $this->vals($contentFolder, ['name' => 'Content']);

        $settingsFolder = $this->item($systemFolder, $root->id, 1, null, false);
        $this->vals($settingsFolder, ['name' => 'Settings']);

        // ── Site Settings ─────────────────────────────────────────────────────
        $siteSettingsItem = $this->item($siteSettings, $settingsFolder->id, 0, null, false);
        $this->vals($siteSettingsItem, [
            'name'                => 'Site Settings',
            'site_name'           => 'Marble CMS Demo',
            'tagline'             => 'A powerful, tree-based CMS built for developers',
            'meta_title_template' => '%title% | Marble CMS Demo',
            'robots'              => 'index, follow',
            'phone'               => '+43 1 234 56 789',
            'email'               => 'hello@marble-cms.dev',
            'instagram_url'       => 'https://instagram.com/marblecms',
            'facebook_url'        => 'https://facebook.com/marblecms',
            'linkedin_url'        => 'https://linkedin.com/company/marblecms',
            'copyright'           => '© ' . date('Y') . ' Marble CMS Demo. All rights reserved.',
        ]);
        $this->html($siteSettingsItem, $siteSettings, 'meta_description', 'Marble CMS is a flexible, tree-based content management system built on Laravel. It supports multi-site, multi-language, workflows, portal users and headless API.', $this->textblock);
        $this->html($siteSettingsItem, $siteSettings, 'address', "Marble Street 1\n1010 Vienna, Austria", $this->textblock);

        // ── Startpage (Homepage) ──────────────────────────────────────────────
        $startpage = $this->item($home, $contentFolder->id, 0, null, false);
        $this->vals($startpage, [
            'name'          => 'Startpage',
            'slug'          => '',
            'hero_title'    => "Content management.\nDone right.",
            'hero_subtitle' => 'Marble CMS is a tree-based, multi-language CMS built on Laravel. Everything is an Item — pages, forms, products, blog posts, team members.',
            'intro_title'   => 'Why Marble?',
        ]);
        $this->html($startpage, $home, 'intro_text',
            '<p>Marble gives developers a <strong>flexible, structured content tree</strong> and a powerful admin UI — without the bloat. '
            . 'Define your content types as Blueprints, build your frontend your way.</p>'
            . '<ul><li>Multi-language &amp; multi-site out of the box</li>'
            . '<li>Headless REST API with token auth</li>'
            . '<li>Content workflows with notifications</li>'
            . '<li>Portal users &amp; intranet sections</li>'
            . '<li>Media library, form builder, scheduled publishing</li></ul>'
        );

        // ── About Us ──────────────────────────────────────────────────────────
        $aboutUs = $this->item($simplePage, $startpage->id, 0);
        $this->vals($aboutUs, ['name' => 'About Us', 'slug' => 'about-us']);
        $this->html($aboutUs, $simplePage, 'content',
            '<p>We are a small, passionate team of developers and designers who believe content management should be simple, powerful, and developer-friendly.</p>'
            . '<p>Founded in Vienna in 2009, Marble CMS has been continuously refined over 15 years — from vanilla PHP to CodeIgniter to Laravel — always keeping the same core philosophy: <strong>everything is an Item in a tree.</strong></p>'
            . '<p>Today, Marble powers dozens of websites ranging from small corporate sites to large intranet portals with thousands of content nodes.</p>'
        );

        // Our Team
        $ourTeam = $this->item($simplePage, $aboutUs->id, 0);
        $this->vals($ourTeam, ['name' => 'Our Team', 'slug' => 'our-team']);
        $this->html($ourTeam, $simplePage, 'content',
            '<p>Meet the people behind Marble CMS. A tight-knit crew that lives and breathes clean code and great user experiences.</p>'
        );

        $alice = $this->item($teamMember, $ourTeam->id, 0);
        $this->vals($alice, ['name' => 'Alice Schmidt', 'slug' => 'alice-schmidt', 'role' => 'Lead Developer', 'linkedin_url' => 'https://linkedin.com']);
        $this->html($alice, $teamMember, 'bio',
            '<p>Alice has been writing PHP since PHP 4 and still loves it. She architected the Marble field-type system and leads the backend team.</p>'
            . '<p>When not coding, she organizes the Vienna PHP Meetup and contributes to open source projects.</p>'
        );

        $bob = $this->item($teamMember, $ourTeam->id, 1);
        $this->vals($bob, ['name' => 'Bob Müller', 'slug' => 'bob-mueller', 'role' => 'Frontend Engineer', 'linkedin_url' => 'https://linkedin.com']);
        $this->html($bob, $teamMember, 'bio',
            '<p>Bob specializes in building fast, accessible frontends. He designed the Marble admin UI and obsesses over pixel-perfect details.</p>'
            . '<p>Fan of progressive enhancement, CSS grid, and black coffee.</p>'
        );

        $carol = $this->item($teamMember, $ourTeam->id, 2);
        $this->vals($carol, ['name' => 'Carol Weber', 'slug' => 'carol-weber', 'role' => 'Product & UX', 'linkedin_url' => 'https://linkedin.com']);
        $this->html($carol, $teamMember, 'bio',
            '<p>Carol bridges the gap between users and technology. She conducts user research, defines product direction, and makes sure Marble stays intuitive.</p>'
            . '<p>Holds a Master\'s in HCI and previously led product at two SaaS startups.</p>'
        );

        // Our Story
        $ourStory = $this->item($simplePage, $aboutUs->id, 1);
        $this->vals($ourStory, ['name' => 'Our Story', 'slug' => 'our-story']);
        $this->html($ourStory, $simplePage, 'content',
            '<h2>How it all started</h2>'
            . '<p>In 2009, we needed a CMS for a client project. Nothing on the market felt right — they were either too rigid or too complex. So we built our own.</p>'
            . '<h2>The tree concept</h2>'
            . '<p>The core insight was simple: all content is hierarchical. Pages, folders, products, news articles — they all live in a tree. This single idea unlocked enormous flexibility.</p>'
            . '<h2>Open source</h2>'
            . '<p>In 2024, we decided to open-source Marble. The community response has been incredible. Today, Marble has hundreds of stars on GitHub and a growing ecosystem of plugins and integrations.</p>'
        );

        // ── Blog ──────────────────────────────────────────────────────────────
        $blog = $this->item($blogIndex, $startpage->id, 1);
        $this->vals($blog, ['name' => 'Blog', 'slug' => 'blog', 'intro' => 'Insights, tutorials, and news from the Marble CMS team.']);

        $post1 = $this->item($blogPost, $blog->id, 0, null, false);
        $this->vals($post1, ['name' => 'Introducing Marble CMS 2.0', 'slug' => 'introducing-marble-cms-2', 'author' => 'Alice Schmidt', 'publish_date' => '2025-03-15',
            'teaser' => 'After two years of development, Marble CMS 2.0 is here. A complete rewrite on Laravel 13 with a brand new admin UI, workflow system, and headless API.']);
        $this->html($post1, $blogPost, 'content',
            '<p>We\'re thrilled to announce the release of Marble CMS 2.0 — the most significant update in the project\'s 15-year history.</p>'
            . '<h2>What\'s new</h2><ul>'
            . '<li><strong>Laravel 13:</strong> Built on the latest Laravel with PHP 8.4 support</li>'
            . '<li><strong>Content Workflows:</strong> Configurable multi-step approval workflows per blueprint</li>'
            . '<li><strong>Headless REST API:</strong> Token-authenticated JSON API for all your content</li>'
            . '<li><strong>Media Library:</strong> Full-featured media management with folder organization</li>'
            . '<li><strong>Mount Points:</strong> Items can appear at multiple tree locations with their own URLs</li>'
            . '</ul><h2>Migration</h2><p>Upgrading from 1.x is straightforward — run <code>php artisan marble:install</code> and let the migration system handle the rest.</p>'
        );

        $post2 = $this->item($blogPost, $blog->id, 1, null, false);
        $this->vals($post2, ['name' => 'Building with Field Types', 'slug' => 'building-with-field-types', 'author' => 'Alice Schmidt', 'publish_date' => '2025-02-20',
            'teaser' => 'Field Types are the building blocks of Marble content. Learn how to use all 15 built-in types and how to create your own custom field type.']);
        $this->html($post2, $blogPost, 'content',
            '<p>Marble ships with 15 built-in field types, covering everything from simple text to complex object relations. Here\'s a tour of what\'s available.</p>'
            . '<h2>Basic types</h2><p><strong>Textfield</strong> — single-line text. <strong>Textblock</strong> — multi-line plain text. <strong>Htmlblock</strong> — rich text with CKEditor 5.</p>'
            . '<h2>Structured types</h2><p><strong>Repeater</strong> — repeatable sub-fields, great for FAQs or feature lists. <strong>KeyValueStore</strong> — free-form key/value pairs.</p>'
            . '<h2>Media types</h2><p><strong>Image</strong>, <strong>Images</strong>, <strong>File</strong>, <strong>Files</strong> — all backed by the Marble Media Library with focal-point support.</p>'
            . '<h2>Relation types</h2><p><strong>ObjectRelation</strong> and <strong>ObjectRelationList</strong> — link items to other items, enabling rich cross-referenced content structures.</p>'
            . '<h2>Creating custom field types</h2><p>Register a class implementing <code>FieldTypeInterface</code> in your service provider and Marble will automatically include it in blueprints.</p>'
        );

        $post3 = $this->item($blogPost, $blog->id, 2, null, false);
        $this->vals($post3, ['name' => 'Multi-Site Made Easy', 'slug' => 'multi-site-made-easy', 'author' => 'Bob Müller', 'publish_date' => '2025-01-10',
            'teaser' => 'One Marble installation, multiple domains, each with their own content tree, settings, and languages. Here\'s how multi-site works.']);
        $this->html($post3, $blogPost, 'content',
            '<p>Marble\'s multi-site feature lets you run multiple websites from a single database. Each site maps a domain to a root item in the content tree.</p>'
            . '<h2>Site resolution</h2><p>When a request comes in, Marble matches the domain against registered sites. URLs are then resolved relative to that site\'s root item — so <code>/about-us</code> on site A and <code>/ueber-uns</code> on site B are completely independent.</p>'
            . '<h2>Per-site settings</h2><p>Each site can have its own settings item, providing separate branding, SEO defaults, contact info, and social links.</p>'
            . '<h2>Shared content</h2><p>Mount Points let the same item appear under multiple sites. A product can live in site A\'s tree but also be accessible via site B — with its own URL alias per mount.</p>'
        );

        $post4 = $this->item($blogPost, $blog->id, 3, null, false, 'draft', $stepReview->id);
        $this->vals($post4, ['name' => 'Content Workflows & Approvals', 'slug' => 'content-workflows', 'author' => 'Carol Weber', 'publish_date' => '2024-12-05',
            'teaser' => 'From draft to published — Marble\'s workflow system lets you define multi-step approval processes with per-step notifications, permissions, and reject actions.']);
        $this->html($post4, $blogPost, 'content',
            '<p>Content governance is critical for larger teams. Marble\'s workflow system gives you fine-grained control over how content moves from draft to published.</p>'
            . '<h2>How it works</h2><p>Define a Workflow with named steps (e.g. "Written → Review → Legal → Published"). Assign a workflow to any Blueprint. Items of that type now flow through those steps.</p>'
            . '<h2>Step permissions</h2><p>Restrict which user groups can advance an item from each step. Legal approvers only see the "Legal" step, editors only see their own steps.</p>'
            . '<h2>Notifications</h2><p>Configure per-step notifications — notify specific users, groups, or everyone via CMS bell or email. Never lose track of content waiting for approval.</p>'
            . '<h2>Reject with comment</h2><p>Reviewers can reject content back to any previous step with a comment explaining what needs to change. The full transition log is visible in the item sidebar.</p>'
        );

        $post5 = $this->item($blogPost, $blog->id, 4, null, false, 'draft', $stepWritten->id);
        $this->vals($post5, ['name' => 'Portal Users & Intranets', 'slug' => 'portal-users-intranets', 'author' => 'Carol Weber', 'publish_date' => '2024-11-18',
            'teaser' => 'Marble\'s portal user system lets you build frontend authentication for intranets, member portals, and customer areas — all within the same CMS.']);
        $this->html($post5, $blogPost, 'content',
            '<p>Not all content is public. Marble\'s portal user feature lets you create frontend-authenticated areas inside your website.</p>'
            . '<h2>Portal users</h2><p>A portal user is separate from a CMS admin user. They log in at <code>/portal/login</code> with their own credentials and can only see content you\'ve designated as accessible to them.</p>'
            . '<h2>Item linking</h2><p>Each portal user can be linked to an Item — for example a "Member" blueprint with profile fields. After login, <code>Marble::portalUser()->item</code> gives you the full profile.</p>'
            . '<h2>Protecting content</h2><p>Use the <code>marble.portal.auth</code> middleware on any route, or check <code>Marble::isPortalAuthenticated()</code> in your Blade templates to show/hide sections.</p>'
        );

        // ── Products ──────────────────────────────────────────────────────────
        $products = $this->item($productCategory, $startpage->id, 2);
        $this->vals($products, ['name' => 'Products', 'slug' => 'products', 'icon' => '🛍️',
            'description' => 'Everything you need to build, manage, and scale your content-driven applications.']);

        // Software category
        $software = $this->item($productCategory, $products->id, 0);
        $this->vals($software, ['name' => 'Software', 'slug' => 'software', 'icon' => '💻',
            'description' => 'Powerful CMS software products for developers and content teams.']);

        $marblepro = $this->item($product, $software->id, 0);
        $this->vals($marblepro, ['name' => 'Marble CMS Pro', 'slug' => 'marble-cms-pro',
            'tagline' => 'The full-featured CMS for serious projects', 'price' => '€ 0 / open source', 'badge' => '⭐ Most Popular',
            'features' => "Multi-language & multi-site\nContent workflows with approvals\nHeadless REST API\nMedia library with focal-point\nPortal users & intranet\nScheduled publishing\nRevision history"]);
        $this->html($marblepro, $product, 'description',
            '<p>Marble CMS Pro is the complete package. Built on Laravel 13, it gives development teams a structured, flexible CMS that can handle everything from simple corporate sites to complex multi-site portals.</p>'
            . '<p>The tree-based content model means there are no rigid page types — define exactly the structure your project needs.</p>'
        );

        $analytics = $this->item($product, $software->id, 1);
        $this->vals($analytics, ['name' => 'Marble Analytics', 'slug' => 'marble-analytics',
            'tagline' => 'Understand how your content performs', 'price' => 'Coming Soon', 'badge' => '🔜 Preview',
            'features' => "Page view tracking\nSearch term analytics\nContent performance scores\nForm conversion tracking\nExport to CSV / API"]);
        $this->html($analytics, $product, 'description',
            '<p>Marble Analytics brings first-party analytics directly into the admin UI. No third-party scripts, no privacy issues — all data stays in your own database.</p>'
            . '<p>See which content drives engagement, which search terms bring visitors, and which forms convert best.</p>'
        );

        $headless = $this->item($product, $software->id, 2);
        $this->vals($headless, ['name' => 'Marble Headless', 'slug' => 'marble-headless',
            'tagline' => 'Use Marble as a backend for any frontend', 'price' => 'Included in Pro', 'badge' => '🚀 Stable',
            'features' => "REST API with token auth\nJSON responses with all slugs\nWorkflow state in API\nChildren endpoint\nParent filter\nPublic & private blueprints"]);
        $this->html($headless, $product, 'description',
            '<p>Marble Headless exposes all your content via a clean REST API. Use it to power React, Vue, Next.js, mobile apps, or any other frontend — while keeping the familiar Marble admin for content editors.</p>'
        );

        // Services category
        $services = $this->item($productCategory, $products->id, 1);
        $this->vals($services, ['name' => 'Services', 'slug' => 'services', 'icon' => '🤝',
            'description' => 'Professional services to help you get the most out of Marble CMS.']);

        $implementation = $this->item($product, $services->id, 0);
        $this->vals($implementation, ['name' => 'Implementation', 'slug' => 'implementation',
            'tagline' => 'We set up Marble for your project', 'price' => 'From € 2,500', 'badge' => '',
            'features' => "Project setup & configuration\nBlueprint design\nFrontend integration\nTraining for editors\nHandover documentation"]);
        $this->html($implementation, $product, 'description',
            '<p>Our team sets up Marble CMS tailored to your project. We design the content architecture, create blueprints, integrate with your frontend, and train your editors.</p>'
        );

        $support = $this->item($product, $services->id, 1);
        $this->vals($support, ['name' => 'Support & Maintenance', 'slug' => 'support-maintenance',
            'tagline' => 'Keep your Marble installation running smoothly', 'price' => 'From € 350 / month', 'badge' => '🛡️ Recommended',
            'features' => "Security updates\nLaravel version upgrades\nPerformance monitoring\nPriority bug fixes\nMonthly health reports"]);
        $this->html($support, $product, 'description',
            '<p>Peace of mind for your production installation. We handle updates, monitor performance, and are on call for any issues that arise.</p>'
        );

        $training = $this->item($product, $services->id, 2);
        $this->vals($training, ['name' => 'Training', 'slug' => 'training',
            'tagline' => 'Get your team up to speed fast', 'price' => 'From € 990 / day', 'badge' => '',
            'features' => "Editor training (half day)\nDeveloper deep-dive (full day)\nBlueprint design workshop\nCustom curriculum available\nRemote or on-site"]);
        $this->html($training, $product, 'description',
            '<p>Whether you\'re onboarding new editors or training developers to extend Marble, our tailored training sessions get your team productive quickly.</p>'
        );

        // ── Contact ───────────────────────────────────────────────────────────
        $contactPage = $this->item($contactForm, $startpage->id, 3);
        $this->vals($contactPage, ['name' => 'Contact', 'slug' => 'contact', 'subject' => 'Get in Touch',
            'message' => 'Have a question about Marble CMS? Want to discuss a project? We\'d love to hear from you.']);

        // ── Intranet ──────────────────────────────────────────────────────────
        $intranet = $this->item($intranetPage, $startpage->id, 4);
        $this->vals($intranet, ['name' => 'Intranet', 'slug' => 'intranet']);
        $this->html($intranet, $intranetPage, 'content',
            '<p>Welcome to the Marble CMS Team Intranet. This area is for internal use only.</p>'
            . '<p>Browse internal news, access shared documents, and explore the team directory.</p>'
        );

        $internalNews = $this->item($intranetPage, $intranet->id, 0);
        $this->vals($internalNews, ['name' => 'Internal News', 'slug' => 'internal-news']);
        $this->html($internalNews, $intranetPage, 'content',
            '<p>Stay up to date with what\'s happening at Marble CMS.</p>'
        );

        $q1 = $this->item($intranetPage, $internalNews->id, 0);
        $this->vals($q1, ['name' => 'Q1 2025 Results', 'slug' => 'q1-2025-results']);
        $this->html($q1, $intranetPage, 'content',
            '<h2>Q1 2025 — Strong start</h2>'
            . '<p>We closed Q1 with 34 new client installations, up 60% from Q1 2024. The open-source launch in January drove significant inbound interest.</p>'
            . '<ul><li>Revenue: € 128,400 (+42% YoY)</li><li>New clients: 34</li><li>GitHub stars: 1,247</li><li>Team growth: +2 engineers</li></ul>'
            . '<p>Full financial report available in the Documents section.</p>'
        );

        $newOffice = $this->item($intranetPage, $internalNews->id, 1);
        $this->vals($newOffice, ['name' => 'New Vienna Office', 'slug' => 'new-vienna-office']);
        $this->html($newOffice, $intranetPage, 'content',
            '<h2>We\'re moving!</h2>'
            . '<p>Starting June 1st, the team will be based at our new office in Vienna\'s 7th district. The new space is twice the size of our current office and includes a dedicated meeting room and a proper kitchen.</p>'
            . '<p><strong>New address:</strong> Kirchengasse 42, 1070 Vienna</p>'
            . '<p>The office warming party is scheduled for June 7th — details to follow via email.</p>'
        );

        $documents = $this->item($intranetPage, $intranet->id, 1);
        $this->vals($documents, ['name' => 'Documents', 'slug' => 'documents']);
        $this->html($documents, $intranetPage, 'content',
            '<h2>Shared Documents</h2>'
            . '<ul>'
            . '<li>📄 Employee Handbook (v3.2) — <em>Updated March 2025</em></li>'
            . '<li>📊 Q1 2025 Financial Report</li>'
            . '<li>📋 Project Proposal Template</li>'
            . '<li>🔒 Security Policy</li>'
            . '<li>📅 2025 Holiday Calendar — Austria</li>'
            . '</ul>'
            . '<p><em>Contact HR to request edit access to any document.</em></p>'
        );

        $teamDir = $this->item($intranetPage, $intranet->id, 2);
        $this->vals($teamDir, ['name' => 'Team Directory', 'slug' => 'team-directory']);
        $this->html($teamDir, $intranetPage, 'content',
            '<h2>Team Directory</h2>'
            . '<table style="width:100%;border-collapse:collapse">'
            . '<thead><tr><th style="text-align:left;padding:8px;border-bottom:2px solid #eee">Name</th><th style="text-align:left;padding:8px;border-bottom:2px solid #eee">Role</th><th style="text-align:left;padding:8px;border-bottom:2px solid #eee">Email</th></tr></thead>'
            . '<tbody>'
            . '<tr><td style="padding:8px;border-bottom:1px solid #f0f0f0">Alice Schmidt</td><td style="padding:8px;border-bottom:1px solid #f0f0f0">Lead Developer</td><td style="padding:8px;border-bottom:1px solid #f0f0f0">alice@marble-cms.dev</td></tr>'
            . '<tr><td style="padding:8px;border-bottom:1px solid #f0f0f0">Bob Müller</td><td style="padding:8px;border-bottom:1px solid #f0f0f0">Frontend Engineer</td><td style="padding:8px;border-bottom:1px solid #f0f0f0">bob@marble-cms.dev</td></tr>'
            . '<tr><td style="padding:8px;border-bottom:1px solid #f0f0f0">Carol Weber</td><td style="padding:8px;border-bottom:1px solid #f0f0f0">Product & UX</td><td style="padding:8px;border-bottom:1px solid #f0f0f0">carol@marble-cms.dev</td></tr>'
            . '</tbody></table>'
        );

        // ════════════════════════════════════════════════════════════════════════
        // SITE & USERS
        // ════════════════════════════════════════════════════════════════════════

        $defaultSite = Site::firstOrCreate(
            ['is_default' => true],
            ['name' => 'Default', 'domain' => null, 'root_item_id' => $startpage->id, 'active' => true]
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

        // Demo portal user
        PortalUser::firstOrCreate(
            ['email' => 'demo@demo.com'],
            ['password' => Hash::make('demo'), 'enabled' => true]
        );
    }

    // ════════════════════════════════════════════════════════════════════════════
    // HELPERS
    // ════════════════════════════════════════════════════════════════════════════

    private function item(Blueprint $blueprint, ?int $parentId, int $sortOrder = 0, ?string $forcePath = null, bool $showInNav = true, string $status = 'published', ?int $workflowStepId = null): Item
    {
        $item = Item::create([
            'blueprint_id'              => $blueprint->id,
            'parent_id'                 => $parentId,
            'status'                    => $status,
            'sort_order'                => $sortOrder,
            'show_in_nav'               => $showInNav,
            'path'                      => $forcePath ?? '/tmp/',
            'current_workflow_step_id'  => $workflowStepId,
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

    private function vals(Item $item, array $fields): void
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

    private function html(Item $item, Blueprint $blueprint, string $identifier, string $html, ?FieldType $ft = null): void
    {
        $field = BlueprintField::where('blueprint_id', $blueprint->id)->where('identifier', $identifier)->first();
        if (!$field) return;

        ItemValue::firstOrCreate(
            ['item_id' => $item->id, 'blueprint_field_id' => $field->id, 'language_id' => $this->en->id],
            ['value' => $html]
        );
    }

    private function ensureField(Blueprint $blueprint, ?FieldType $fieldType, array $attrs): void
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

    private function ensureAllowAllChildren(int $blueprintId): void
    {
        if (!DB::table('blueprint_allowed_children')->where('blueprint_id', $blueprintId)->exists()) {
            DB::table('blueprint_allowed_children')->insert(['blueprint_id' => $blueprintId, 'child_blueprint_id' => null, 'allow_all' => true]);
        }
    }
}
