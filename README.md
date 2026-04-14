# Marble CMS

<table>
<tr>
  <td width="33%"><img src="Screenshot1.png" alt="Dashboard"></td>
  <td width="33%"><img src="Screenshot2.png" alt="Item edit"></td>
  <td width="33%"><img src="Screenshot3.png" alt="Blueprint editor"></td>
</tr>
<tr>
  <td width="33%"><img src="Screenshot4.png" alt="Media library"></td>
  <td width="33%"><img src="Screenshot5.png" alt="Tree navigation"></td>
  <td width="33%"><img src="Screenshot6.png" alt="Field types"></td>
</tr>
</table>

A Laravel CMS package built around a flexible Blueprint + Field Type system. Define your content types visually, manage a hierarchical content tree, and deliver content via Blade templates or a headless JSON API.

**[→ Full Documentation](https://github.com/marblecms/admin/wiki)**

## Requirements

- PHP 8.2+
- Laravel 11+
- MySQL 8+

## Installation

Use the [demo repository](https://github.com/marblecms/demo) as your starting point — it comes pre-configured with Docker, routing, and Blade templates.

```bash
git clone --recurse-submodules https://github.com/marblecms/demo
cd demo
cp .env.example .env
docker compose up -d
docker compose exec app php artisan marble:install
```

`marble:install` runs migrations, seeds the initial content tree, registers built-in field types, and publishes admin assets.

Default login after install:

```
Email:    admin@admin
Password: admin
```

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag=marble-config
```

`config/marble.php`:

| Key                  | Default   | Description                                                          |
|----------------------|-----------|----------------------------------------------------------------------|
| `route_prefix`       | `admin`   | URL prefix for the admin panel                                       |
| `guard`              | `marble`  | Auth guard used by the admin                                         |
| `primary_locale`     | `en`      | Language code used for non-translatable fields                       |
| `locale`             | `en`      | Default locale for the current request                               |
| `uri_locale_prefix`  | `false`   | Prefix frontend URLs with language code (`/en/about`, `/de/ueber-uns`) |
| `entry_item_id`      | `1`       | Fallback root item ID for the tree (overridden by user group setting) |
| `frontend_url`       | `''`      | Base URL used for preview links (set via `MARBLE_FRONTEND_URL`)      |
| `auto_routing`       | `false`   | Register a catch-all frontend route automatically                    |
| `cache_ttl`          | `3600`    | Item cache lifetime in seconds. `0` disables caching                 |
| `lock_ttl`           | `300`     | Seconds a content lock stays valid before expiring                   |
| `autosave`           | `false`   | Enable autosave on the item edit form                                |
| `autosave_interval`  | `30`      | Seconds after last change before autosave fires                      |
| `storage_disk`       | `public`  | Laravel storage disk for media uploads                               |
| `portal_registration`| `false`   | Enable self-registration for portal users at `/portal/register`      |
| `portal_home`        | `'/'`     | Redirect URL after portal user login                                 |

Many of these can also be changed at runtime via **Dashboard → Configuration** in the admin.

## Content Model

### Items

Items are nodes in a hierarchical content tree stored with materialized paths (`/1/3/7/`). Every item has:

- A **Blueprint** (content type definition)
- A **status** (`published` / `draft`)
- Optional **scheduling** (`publish_at`, `expires_at`)
- An optional **slug** per language
- A **show in navigation** flag

### Blueprints

Blueprints define content types. Each blueprint has:

| Option              | Description                                                                 |
|---------------------|-----------------------------------------------------------------------------|
| **Fields**          | Visual field builder with drag-and-drop ordering and group assignment       |
| **Allow Children**  | Whether items of this type can have child items                             |
| **List Children**   | Show child items as a sortable table in the item editor                     |
| **Inline Children** | Edit child items as accordion panels directly in the parent item editor     |
| **Show in Tree**    | Whether this blueprint appears in the sidebar tree                          |
| **Versionable**     | Record a full revision on every save; diff any two revisions                |
| **Schedulable**     | Enable `publish_at` / `expires_at` per item                                 |
| **Is Form**         | Treat items of this type as contact forms; collect submissions from frontend |
| **Hide System Fields** | Hide the built-in name/slug fields in the editor (useful for settings items) |
| **Locked**          | Prevent field edits in the admin (content is read-only)                     |
| **Inherits From**   | Prepend all fields from a parent blueprint (read-only in the editor)        |
| **Icon**            | Choose from 997 Famfamfam Silk icons via searchable picker                  |
| **Allowed Child Blueprints** | Restrict which blueprints can be added as children              |
| **Workflow**        | Attach a multi-step editorial workflow to this content type                 |

### Field Types

Built-in field types:

| Identifier           | Description                                           |
|----------------------|-------------------------------------------------------|
| `textfield`          | Single-line text                                      |
| `textblock`          | Multi-line textarea                                   |
| `htmlblock`          | WYSIWYG rich text (CKEditor)                          |
| `image`              | Single image upload with focal point                  |
| `images`             | Multiple images                                       |
| `file`               | Single file attachment                                |
| `files`              | Multiple file attachments with drag-to-reorder        |
| `checkbox`           | Boolean toggle                                        |
| `selectbox`          | Dropdown with configurable options                    |
| `date`               | Date picker                                           |
| `datetime`           | Date + time picker                                    |
| `time`               | Time picker                                           |
| `object_relation`    | Link to another Item (with configurable on-delete behavior) |
| `object_relation_list` | Multiple Item references                            |
| `keyvalue_store`     | Arbitrary key/value pairs                             |
| `repeater`           | Repeatable rows of named sub-fields                   |

Each field can be marked **Translatable** (separate value per language), **Locked** (read-only in the editor), and given optional **Validation Rules** (Laravel validation syntax, e.g. `required|max:255`).

### Custom Field Types

Implement `FieldTypeInterface` and register in a service provider:

```php
use Marble\Admin\Facades\Marble;

Marble::registerFieldType(new MyCustomFieldType());
```

## The Marble Facade

```php
use Marble\Admin\Facades\Marble;

// Resolve a URL path to a published Item (returns null if not found)
$item = Marble::resolve('/about/team');
$item = Marble::resolveOrFail('/about/team'); // aborts with 404

// Read field values
$item->value('title');           // current locale
$item->value('title', 'de');     // specific locale
$item->value('title', 2);        // by language ID

// URL generation
Marble::url($item);              // full URL including frontend_url base
Marble::url($item, 'de');        // URL in a specific locale

// Tree navigation
Marble::navigation();            // full tree from the current site root
Marble::navigation(5);           // tree from item 5
Marble::navigation(5, 2);        // max 2 levels deep
Marble::breadcrumb($item);       // ancestor chain (Collection of Items)
Marble::children($item);         // published direct children
Marble::children($item, 'article'); // filtered by blueprint identifier

// Site settings
Marble::settings();              // returns the settings Item for the current site
Marble::settings()->value('site_name');

// Language
Marble::currentLanguageId();     // ID of the active language
Marble::primaryLanguageId();     // ID of the primary/fallback language

// Portal auth
Marble::isPortalAuthenticated(); // bool
Marble::portalUser();            // PortalUser|null
```

## Frontend Routing

### Manual (recommended)

In `routes/web.php`:

```php
Marble::routes(function (\Marble\Admin\Models\Item $item) {
    return view(Marble::viewFor($item), ['item' => $item]);
});
```

`Marble::viewFor($item)` looks for `resources/views/marble-pages/{blueprint_identifier}.blade.php` and falls back to `resources/views/marble-pages/default.blade.php`.

### Automatic

Set `auto_routing = true` in config (or `MARBLE_AUTO_ROUTING=true` in `.env`) and Marble registers the catch-all route itself.

### Locale Prefix

Set `uri_locale_prefix = true` to prefix all frontend URLs with the language code:

```
/en/about-us
/de/ueber-uns
```

`Marble::url($item)` and `Marble::resolve($path)` both handle the prefix automatically.

### Translation Fallback

When a field has no value for the current language, Marble automatically falls back to the primary language value. This means pages will always render with content even when partially translated.

## Sites & Multi-site

Configure multiple sites under **System → Sites**. Each site has:

- **Domain** — hostname without protocol (e.g. `example.com`)
- **Root Item** — the top-level content node for this site's tree
- **Settings Item** — an Item of any blueprint used as site-wide settings. Accessible via `Marble::settings()`.
- **Default Language** — fallback locale for this site
- **Is Default** — used when no domain matches (localhost, IP access)

```blade
{{ Marble::settings()->value('site_name') }}
{{ Marble::settings()->value('email') }}
```

## Scheduled Publishing

When **Schedulable** is enabled on a blueprint, each item gains **Publish At** and **Expires At** fields. `Item::isPublished()` respects these automatically. Process them via the scheduler:

```php
// routes/console.php
Schedule::command('marble:schedule-publish')->everyMinute();
Schedule::command('marble:workflow-deadlines')->daily();
Schedule::command('marble:prune')->weekly();
```

## Maintenance Commands

| Command | Description |
|---------|-------------|
| `marble:install` | Full install: migrations, seed, publish assets |
| `marble:doctor` | Health check (DB, sites, blueprints, workflows, field types) |
| `marble:schedule-publish` | Publish/expire items by scheduled date |
| `marble:workflow-deadlines` | Notify owners of overdue workflow items |
| `marble:prune` | Delete old activity log and read notifications |
| `marble:make-blueprint` | Interactive blueprint + Blade view generator |
| `marble:sync-field-types` | Sync registered field types to DB |
| `marble:clear-icon-cache` | Clear the icon picker cache |

## Inline Children

When **Inline Children** is enabled on a blueprint, child items are displayed as collapsible accordion panels directly inside the parent item's edit view.

## Revision History

When **Versionable** is enabled on a blueprint, a full snapshot is saved on every save. Revisions can be diffed side-by-side or restored.

## Content Workflows

Attach a multi-step approval workflow to any blueprint under **System → Workflows**. Each step can have:

- **Allowed Groups** — only members of these groups can advance
- **Deadline (days)** — triggers an overdue notification after N days
- **Reject** — send the item back to a specific step with a comment
- **Notifications** — notify specific users/groups via in-app or email when an item enters the step

The item sidebar shows a visual step timeline with advance/retreat/reject buttons and full transition history.

## Content Locking

When a user opens an item for editing, Marble acquires a lock for `lock_ttl` seconds. If another user opens the same item, a warning banner is shown. Locks expire automatically.

## Trash & Soft Delete

Deleted items are soft-deleted and appear in **System → Trash**. From there items can be restored or permanently deleted.

## URL Aliases

Any item can have additional URL aliases per language. Aliases are validated against other aliases and existing slugs to prevent conflicts. URL resolution enforces site root boundaries — an alias can only resolve if the target item is within the current site's content tree.

## Mount Points

An item can appear at multiple positions in the tree simultaneously. Each mount context has its own URL, derived by walking up the mount parent chain instead of the canonical parent. All URLs (canonical + mounts) are accessible via `$item->allSlugs()` and shown in the **Reachable via** sidebar box.

## A/B Testing

Create a Variant B for any item via the **A/B Test** sidebar box. Configure the traffic split (e.g. 20% → Variant B), edit the variant fields (each showing the original value as reference), and track A/B impressions in the sidebar widget. The variant is assigned via a 30-day cookie and applied transparently — templates require no changes.

## Item Subscriptions

Click **Watch** in the item sidebar to subscribe to an item. Subscribers receive in-app notifications on save, status change, and workflow transitions. Watched items appear on the dashboard for quick access.

## Content Bundles

Group multiple items under **Content → Bundles** for coordinated publishing. Publishing a bundle publishes all items atomically and records pre-publish state for rollback. If something goes wrong post-publish, **Roll Back Bundle** restores all items to their previous status and content snapshot.

## Draft Preview

Generate a shareable preview URL for any unpublished item via **Preview** in the sidebar. The link resolves via a one-time token — no login required for the viewer.

## Content Relations Graph

Click **Relations Graph** in the item sidebar for a D3.js force-directed view of the item's connections: parent, children, outgoing `object_relation` / `object_relation_list` fields, incoming relations from other items, and mount points. Drag, zoom, pan, double-click to navigate.

## Activity Log

Every content action (create, save, publish, draft, duplicate, move, delete, restore) is recorded with the user and timestamp. Browse the full log under **Dashboard → Activity Log** or see a paginated feed on the dashboard.

## Media Library

Upload files and images via **System → Media**. Features:

- **Folders** — organise files into a folder tree
- **Focal Point** — click to set the crop anchor
- **Smart Crops** — define named crop presets (e.g. `hero`, `thumbnail`); `$media->crop('hero')` returns a focal-point-aware URL
- **Media Blueprints** — assign a Blueprint to a MIME type to attach structured field metadata (captions, alt text, credits) to uploaded files
- **On-the-fly resizing** via the built-in image controller:

```
/image/{filename}                   ← original
/image/{width}/{height}/{filename}  ← resized / cover-cropped to focal point
```

## Contact Forms

Set **Is Form** on a blueprint to enable form submission collection. The `<x-marble::marble-form>` component renders the form on the frontend and stores submissions in the database. Configure per item:

- **Recipients** — comma-separated email addresses for notifications
- **Success Message** — shown after submit
- **Success Redirect** — redirect to another item after submit

## Webhooks

Register webhooks under **System → Webhooks**. Marble fires HTTP POST requests on:

- `item.created`, `item.saved`, `item.published`, `item.draft`
- `item.deleted`, `item.duplicated`, `item.moved`, `item.reverted`

Payload includes: item ID/name, blueprint, workflow step, parent ID, all slugs, and changed field values. Optionally set a **Secret** for HMAC SHA256 signatures via `X-Marble-Signature`.

## Redirect Manager

Manage 301/302 redirects under **System → Redirects**. The `HandleMarbleRedirects` middleware intercepts 404 responses and checks the redirect table. Hit counts are tracked automatically.

## Headless JSON API

Create API tokens under **Dashboard → API Tokens**. Pass as a Bearer token:

```
GET /api/marble/items/{blueprint}
GET /api/marble/items/{blueprint}?parent_id=5&status=published
GET /api/marble/item/{id}
GET /api/marble/item/{id}/children
GET /api/marble/resolve?path=/about/team
```

Blueprints must have **Allow Public API** enabled to be accessible. Response includes: `name`, `workflow_step`, `parent_id`, `all_slugs`.

## Portal Users

Frontend authentication for site visitors (e.g. intranet, member areas). Portal users are separate from admin users.

```php
// Protect a frontend route
Route::get('/intranet', ...)->middleware('marble.portal.auth');

// In templates
@if(Marble::isPortalAuthenticated())
    Welcome, {{ Marble::portalUser()->name }}
@endif
```

Admin UI under **System → Portal Users** for creating, editing, enabling/disabling portal accounts.

## Marble Packages

Export and import complete blueprint definitions (including custom field types) as `.zip` packages via **Dashboard → Packages**. Useful for moving content schemas between projects or distributing reusable content types.

## User Groups & Permissions

Users belong to a **User Group**. Groups control:

- **Permissions** — granular create/edit/delete/list flags for users, blueprints, and groups
- **Allowed Blueprints** — per-blueprint CRUD matrix (Create / Read / Update / Delete independently)
- **Root Node** — restrict the visible content tree to a specific subtree

## Admin Themes

Each user can choose their admin theme in their user profile. Available themes:

- **XP** (default) — clean, modern look
- **Windows 98** — full retro Win98 aesthetic with beveled borders, teal desktop, navy title bars, and Windows 98 system icons

## Marble Debugbar

Set `MARBLE_DEBUGBAR=true` in `.env` to enable a floating debug panel on frontend pages (admin users only). Shows: item ID, blueprint, status, workflow step, language, site, all URLs, cache key, and an **Edit in Marble** link.

## License

MIT
