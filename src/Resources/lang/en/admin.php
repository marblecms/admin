<?php

return [
    'title'               => 'Administration',
    'children'            => 'Children',
    'add_children'        => 'Add Child',
    'add_user'            => 'Add User',
    'add_usergroup'       => 'Add Usergroup',
    'manage_usergroups'   => 'Manage User Groups',
    'add_class'           => 'Add Blueprint',
    'import_class'        => 'Import class',
    'add_classgroup'      => 'Add Blueprint Group',
    'export'              => 'Export',
    'edit'                => 'Edit',
    'edit_attributes'     => 'Edit Attributes',
    'delete'              => 'Delete',
    'cancel'              => 'Cancel',
    'save'                => 'Save',
    'list'                => 'List',
    'create'              => 'Create',
    'import'              => 'Import',
    'import_json'         => 'Import from JSON',
    'import_success'      => 'Import successful.',
    'file'                => 'File',
    'meta_information'    => 'Meta Information',
    'dashboard'           => 'Dashboard',
    'last_edited'         => 'Last Edited',
    'no_items'            => 'No items yet.',
    'classes'             => 'Blueprints',
    'users'               => 'Users',
    'usergroups'          => 'Usergroups',
    'name'                => 'Name',
    'none'                => 'None',
    'are_you_sure'        => 'Are you sure?',
    'no_children'         => 'No children available...',
    'delete_node'         => 'Delete',
    'delete_permanent'    => 'Delete permanently',
    'search_placeholder'  => 'Search for contents...',
    'select_object'       => 'Select object...',
    'create_object'       => 'Create object...',
    'edit_image'          => 'Edit image...',
    'email'               => 'E-Mail',
    'new_password'        => 'New password?',
    'group'               => 'Group',
    'entrypoint'          => 'Entrypoint',
    'parent_item'         => 'Parent Item',
    'allowed_classes'     => 'Allowed Blueprints',
    'attributegroups'     => 'Attributegroups',
    'add_group'           => 'Add group',
    'edit_attributegroup' => 'Edit attributegroup',
    'template'            => 'Template',
    'identifier'          => 'Identifier',
    'translateable'       => 'Translateable',
    'locked'              => 'Locked',
    'show_name'           => 'Show name',
    'no_group'            => 'No Group',

    // Blueprint options
    'versionable'         => 'Versionable',
    'versionable_hint'    => 'Track revision history for items of this blueprint.',
    'schedulable'         => 'Scheduling',
    'schedulable_hint'    => 'Allow items to be published/expired on a scheduled date.',

    // Blueprint inheritance
    'inherits_from'       => 'Inherits from',
    'inherits_from_hint'  => 'Fields from the parent blueprint are prepended and shown as read-only.',
    'inherited'           => 'Inherited',

    // Status
    'published'           => 'Published',
    'draft'               => 'Draft',
    'set_draft'           => 'Set to Draft',
    'set_published'       => 'Publish',

    'show_in_nav'         => 'Show in Nav',
    'yes'                 => 'Yes',
    'no'                  => 'No',

    // Item actions
    'actions'             => 'Actions',
    'duplicate'           => 'Duplicate',
    'move'                => 'Move',
    'move_item'           => 'Move Item',
    'select_new_parent'   => 'Select new parent',
    'preview'             => 'Preview',
    'no_slug'             => 'No slug configured',

    // Revisions
    'versions'            => 'Versions',
    'restore'             => 'Restore',
    'no_versions'         => 'No versions yet',
    'version_by'          => 'by',

    // Content locking
    'lock_editing'        => 'is currently editing this item.',

    // Reverse relations
    'used_by'             => 'Used by',

    // Trash
    'trash'               => 'Trash',
    'trashed_items'       => 'Trashed Items',
    'trash_empty'         => 'Trash is empty.',
    'empty_trash'         => 'Empty Trash',
    'deleted_at'          => 'Deleted',

    // Multi-site
    'sites'               => 'Sites',
    'add_site'            => 'Add Site',
    'edit_site'           => 'Edit Site',
    'no_sites'            => 'No sites configured yet.',
    'domain'              => 'Domain',
    'domain_hint'         => 'Hostname without protocol, e.g. example.com',
    'root_item'           => 'Root Item',
    'root_item_hint'      => 'The top-level item for this site\'s content tree.',
    'default_language'    => 'Default Language',
    'language'            => 'Language',

    // Field validation
    'validation_rules'    => 'Validation rules',

    // Scheduling
    'scheduling'          => 'Scheduling',
    'publish_at'          => 'Publish at',
    'expires_at'          => 'Expires at',
    'save_schedule'       => 'Save schedule',

    // Webhooks
    'webhooks'            => 'Webhooks',
    'add_webhook'         => 'Add Webhook',
    'edit_webhook'        => 'Edit Webhook',
    'no_webhooks'         => 'No webhooks configured yet.',
    'webhook_events'      => 'Events',
    'webhook_secret'      => 'Secret',
    'webhook_secret_hint' => 'If set, requests are signed with X-Marble-Signature (HMAC SHA256).',
    'active'              => 'Active',
    'inactive'            => 'Inactive',
    'optional'            => 'optional',
    'status'              => 'Status',

    // Activity log
    'activity_log'        => 'Activity Log',
    'no_activity'         => 'No activity recorded yet.',
    'date'                => 'Date',
    'action'              => 'Action',

    // Media library
    'media_library'       => 'Media',
    'upload'              => 'Upload',
    'upload_file'         => 'Upload file',
    'no_media'            => 'No files uploaded yet.',
    'from_library'        => 'From Library',
    'select_from_library' => 'Select from Library',
    'folders'             => 'Folders',
    'all_files'           => 'All Files',
    'new_folder'          => 'New Folder',
    'no_folder'           => 'No folder',
    'no_subfolders'       => 'No subfolders here.',

    // Revision diff
    'diff'                => 'Diff',
    'revision_diff'       => 'Revision Diff',
    'diff_no_changes'     => 'No changes detected.',
    'diff_before'         => 'Before',
    'diff_after'          => 'After',
    'diff_compared_to'    => 'Compared to',
    'diff_first_revision' => 'first revision',

    // Form builder
    'is_form'             => 'Form',
    'is_form_hint'             => 'Enables a frontend form submission handler for this blueprint.',
    'hide_system_fields'       => 'Hide name & slug',
    'hide_system_fields_hint'  => 'Hides the name and slug fields in the item editor (useful for settings or singleton items).',
    'settings_blueprint'       => 'Settings Blueprint',
    'settings_blueprint_hint'  => 'Blueprint used to create the site settings item. One item per site will be auto-created.',
    'form_recipients'     => 'Recipients (e-mail)',
    'form_recipients_hint'=> 'Comma-separated list of e-mail addresses to notify on submission.',
    'form_success_message'=> 'Success message',
    'form_submissions'         => 'Submissions',
    'form_submissions_hint'    => 'This item is a form. Field editing is disabled — submissions are collected from the frontend.',
    'no_submissions'      => 'No submissions yet.',
    'submitted_at'        => 'Submitted at',
    'mark_read'           => 'Mark read',
    'unread'              => 'Unread',

    // Repeater
    'add_row'             => 'Add Row',
    'repeater_fields'     => 'Repeater sub-fields',
    'no_repeater_fields'  => 'No sub-fields configured yet.',

    // Auth
    'password'            => 'Password',
    'remember_me'         => 'Remember me',
    'login'               => 'Login',

    // Form redirect
    'form_success_redirect'      => 'Success Redirect',
    'form_success_redirect_hint' => 'Redirect the user to this page after successful form submission.',
];
