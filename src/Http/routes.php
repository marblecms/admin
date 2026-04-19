<?php

use Illuminate\Support\Facades\Route;

// Dashboard
Route::get('dashboard', [\Marble\Admin\Http\Controllers\DashboardController::class, 'view'])
    ->name('dashboard');

// Items
Route::prefix('item')->as('item.')->group(function () {
    Route::get('edit/{item}', [\Marble\Admin\Http\Controllers\ItemController::class, 'edit'])->name('edit');
    Route::post('save/{item}', [\Marble\Admin\Http\Controllers\ItemController::class, 'save'])->name('save');
    Route::post('schedule/{item}', [\Marble\Admin\Http\Controllers\ItemController::class, 'saveSchedule'])->name('schedule');
    Route::get('add/{parentItem}', [\Marble\Admin\Http\Controllers\ItemController::class, 'add'])->name('add');
    Route::post('create', [\Marble\Admin\Http\Controllers\ItemController::class, 'create'])->name('create');
    Route::delete('delete/{item}', [\Marble\Admin\Http\Controllers\ItemController::class, 'delete'])->name('delete');
    Route::post('duplicate/{item}', [\Marble\Admin\Http\Controllers\ItemController::class, 'duplicate'])->name('duplicate');
    Route::get('move/{item}', [\Marble\Admin\Http\Controllers\ItemMoveController::class, 'form'])->name('move-form');
    Route::post('move/{item}', [\Marble\Admin\Http\Controllers\ItemMoveController::class, 'move'])->name('move');
    Route::post('toggle-status/{item}', [\Marble\Admin\Http\Controllers\ItemController::class, 'toggleStatus'])->name('toggle-status');
    Route::post('toggle-nav/{item}', [\Marble\Admin\Http\Controllers\ItemController::class, 'toggleNav'])->name('toggle-nav');
    Route::post('revert/{item}/{revision}', [\Marble\Admin\Http\Controllers\ItemRevisionController::class, 'revert'])->name('revert');
    Route::get('diff/{item}/{revision}', [\Marble\Admin\Http\Controllers\ItemRevisionController::class, 'diff'])->name('diff');
    Route::post('sort', [\Marble\Admin\Http\Controllers\ItemSortController::class, 'sort'])->name('sort');
    Route::get('search.json', [\Marble\Admin\Http\Controllers\ItemController::class, 'searchJson'])->name('search');
    Route::post('ajax-field/{itemValue}/{language}', [\Marble\Admin\Http\Controllers\ItemController::class, 'ajaxField'])->name('ajax-field');
    // URL Aliases
    Route::post('aliases/{item}', [\Marble\Admin\Http\Controllers\ItemAliasController::class, 'save'])->name('aliases.save');
    // Content locking
    Route::post('lock/{item}', [\Marble\Admin\Http\Controllers\ItemLockController::class, 'acquire'])->name('lock');
    Route::delete('lock/{item}', [\Marble\Admin\Http\Controllers\ItemLockController::class, 'release'])->name('unlock');
    // Import / Export
    Route::get('export/{item}', [\Marble\Admin\Http\Controllers\ImportExportController::class, 'export'])->name('export');
    Route::get('import', [\Marble\Admin\Http\Controllers\ImportExportController::class, 'importForm'])->name('import-form');
    Route::post('import', [\Marble\Admin\Http\Controllers\ImportExportController::class, 'import'])->name('import');
    // Mount Points
    Route::post('mount/{item}', [\Marble\Admin\Http\Controllers\ItemMountController::class, 'store'])->name('mount.store');
    Route::delete('mount/{item}/{mount}', [\Marble\Admin\Http\Controllers\ItemMountController::class, 'destroy'])->name('mount.destroy');
    // Draft Preview
    Route::post('preview/{item}', [\Marble\Admin\Http\Controllers\PreviewController::class, 'generate'])->name('preview.generate');
    // Copy Language
    Route::post('copy-language/{item}', [\Marble\Admin\Http\Controllers\ItemLanguageCopyController::class, 'copy'])->name('copy-language');
    // Workflow state
    Route::post('workflow/advance/{item}', [\Marble\Admin\Http\Controllers\ItemWorkflowController::class, 'advance'])->name('workflow.advance');
    Route::post('workflow/retreat/{item}', [\Marble\Admin\Http\Controllers\ItemWorkflowController::class, 'retreat'])->name('workflow.retreat');
    Route::post('workflow/reject/{item}', [\Marble\Admin\Http\Controllers\ItemWorkflowController::class, 'reject'])->name('workflow.reject');
    // Subscriptions
    Route::post('subscribe/{item}', [\Marble\Admin\Http\Controllers\ItemSubscriptionController::class, 'toggle'])->name('subscribe');
    // Relations graph
    Route::get('graph/{item}', [\Marble\Admin\Http\Controllers\ItemRelationsController::class, 'show'])->name('graph');
    Route::get('graph-data/{item}', [\Marble\Admin\Http\Controllers\ItemRelationsController::class, 'data'])->name('graph-data');
    // Field history
    Route::get('field-history/{item}/{field}', [\Marble\Admin\Http\Controllers\FieldHistoryController::class, 'history'])->name('field-history');
    Route::post('field-restore/{item}/{field}', [\Marble\Admin\Http\Controllers\FieldHistoryController::class, 'restore'])->name('field-restore');
    // A/B Variants
    Route::post('variant/create/{item}',                  [\Marble\Admin\Http\Controllers\ItemVariantController::class, 'create'])->name('variant.create');
    Route::get('variant/edit/{item}/{variant}',           [\Marble\Admin\Http\Controllers\ItemVariantController::class, 'edit'])->name('variant.edit');
    Route::post('variant/save/{item}/{variant}',          [\Marble\Admin\Http\Controllers\ItemVariantController::class, 'save'])->name('variant.save');
    Route::post('variant/toggle/{item}/{variant}',        [\Marble\Admin\Http\Controllers\ItemVariantController::class, 'toggle'])->name('variant.toggle');
    Route::post('variant/split/{item}/{variant}',         [\Marble\Admin\Http\Controllers\ItemVariantController::class, 'updateSplit'])->name('variant.split');
    Route::delete('variant/delete/{item}/{variant}',      [\Marble\Admin\Http\Controllers\ItemVariantController::class, 'destroy'])->name('variant.delete');
    // Collaboration — Comments
    Route::post('comment/{item}',                         [\Marble\Admin\Http\Controllers\ItemCommentController::class, 'store'])->name('comment.store');
    Route::delete('comment/{comment}',                    [\Marble\Admin\Http\Controllers\ItemCommentController::class, 'destroy'])->name('comment.destroy');
    // Collaboration — Tasks
    Route::post('task/{item}',                            [\Marble\Admin\Http\Controllers\ItemTaskController::class, 'store'])->name('task.store');
    Route::post('task/{task}/toggle',                     [\Marble\Admin\Http\Controllers\ItemTaskController::class, 'toggle'])->name('task.toggle');
    Route::delete('task/{task}',                          [\Marble\Admin\Http\Controllers\ItemTaskController::class, 'destroy'])->name('task.destroy');
    // Traffic
    Route::get('traffic/{item}',                          [\Marble\Admin\Http\Controllers\ItemTrafficController::class, 'show'])->name('traffic');
    Route::get('traffic-data/{item}',                     [\Marble\Admin\Http\Controllers\ItemTrafficController::class, 'data'])->name('traffic-data');
});

// Traffic: site-wide overview
Route::get('traffic', [\Marble\Admin\Http\Controllers\ItemTrafficController::class, 'siteData'])->name('traffic.site-data');

// Content Bundles
Route::prefix('bundle')->as('bundle.')->group(function () {
    Route::get('/',                          [\Marble\Admin\Http\Controllers\ContentBundleController::class, 'index'])->name('index');
    Route::get('create',                     [\Marble\Admin\Http\Controllers\ContentBundleController::class, 'create'])->name('create');
    Route::post('store',                     [\Marble\Admin\Http\Controllers\ContentBundleController::class, 'store'])->name('store');
    Route::get('{bundle}',                   [\Marble\Admin\Http\Controllers\ContentBundleController::class, 'show'])->name('show');
    Route::post('{bundle}/add-item',         [\Marble\Admin\Http\Controllers\ContentBundleController::class, 'addItem'])->name('add-item');
    Route::delete('{bundle}/item/{item}',    [\Marble\Admin\Http\Controllers\ContentBundleController::class, 'removeItem'])->name('remove-item');
    Route::post('{bundle}/publish',          [\Marble\Admin\Http\Controllers\ContentBundleController::class, 'publish'])->name('publish');
    Route::post('{bundle}/rollback',         [\Marble\Admin\Http\Controllers\ContentBundleController::class, 'rollback'])->name('rollback');
    Route::delete('{bundle}',                [\Marble\Admin\Http\Controllers\ContentBundleController::class, 'destroy'])->name('destroy');
});

// Calendar
Route::prefix('calendar')->as('calendar.')->group(function () {
    Route::get('/',                  [\Marble\Admin\Http\Controllers\CalendarController::class, 'index'])->name('index');
    Route::get('events',             [\Marble\Admin\Http\Controllers\CalendarController::class, 'events'])->name('events');
    Route::post('reschedule/{item}', [\Marble\Admin\Http\Controllers\CalendarController::class, 'reschedule'])->name('reschedule');
});

// Trash
Route::prefix('trash')->as('trash.')->group(function () {
    Route::get('/', [\Marble\Admin\Http\Controllers\TrashController::class, 'index'])->name('index');
    Route::post('restore/{id}', [\Marble\Admin\Http\Controllers\TrashController::class, 'restore'])->name('restore');
    Route::delete('force-delete/{id}', [\Marble\Admin\Http\Controllers\TrashController::class, 'forceDelete'])->name('force-delete');
    Route::post('empty', [\Marble\Admin\Http\Controllers\TrashController::class, 'empty'])->name('empty');
});

// Blueprints
Route::prefix('blueprint')->as('blueprint.')->group(function () {
    Route::get('all', [\Marble\Admin\Http\Controllers\BlueprintController::class, 'index'])->name('index');
    Route::get('add', [\Marble\Admin\Http\Controllers\BlueprintController::class, 'add'])->name('add');
    Route::post('duplicate/{blueprint}', [\Marble\Admin\Http\Controllers\BlueprintController::class, 'duplicate'])->name('duplicate');
    Route::get('edit/{blueprint}', [\Marble\Admin\Http\Controllers\BlueprintController::class, 'edit'])->name('edit');
    Route::post('save/{blueprint}', [\Marble\Admin\Http\Controllers\BlueprintController::class, 'save'])->name('save');
    Route::delete('delete/{blueprint}', [\Marble\Admin\Http\Controllers\BlueprintController::class, 'delete'])->name('delete');

    // Blueprint fields
    Route::prefix('{blueprint}/field')->as('field.')->group(function () {
        Route::get('edit', [\Marble\Admin\Http\Controllers\BlueprintFieldController::class, 'edit'])->name('edit');
        Route::post('add', [\Marble\Admin\Http\Controllers\BlueprintFieldController::class, 'add'])->name('add');
        Route::delete('delete/{field}', [\Marble\Admin\Http\Controllers\BlueprintFieldController::class, 'delete'])->name('delete');
        Route::post('save', [\Marble\Admin\Http\Controllers\BlueprintFieldController::class, 'save'])->name('save');
    });

    // Blueprint field groups
    Route::prefix('{blueprint}/field-group')->as('field-group.')->group(function () {
        Route::post('add', [\Marble\Admin\Http\Controllers\BlueprintFieldGroupController::class, 'add'])->name('add');
        Route::post('save', [\Marble\Admin\Http\Controllers\BlueprintFieldGroupController::class, 'save'])->name('save');
        Route::post('sort', [\Marble\Admin\Http\Controllers\BlueprintFieldGroupController::class, 'sort'])->name('sort');
        Route::delete('delete/{group}', [\Marble\Admin\Http\Controllers\BlueprintFieldGroupController::class, 'delete'])->name('delete');
    });

    // Blueprint groups
    Route::prefix('group')->as('group.')->group(function () {
        Route::get('add', [\Marble\Admin\Http\Controllers\BlueprintGroupController::class, 'add'])->name('add');
        Route::get('edit/{group}', [\Marble\Admin\Http\Controllers\BlueprintGroupController::class, 'edit'])->name('edit');
        Route::post('save/{group}', [\Marble\Admin\Http\Controllers\BlueprintGroupController::class, 'save'])->name('save');
        Route::delete('delete/{group}', [\Marble\Admin\Http\Controllers\BlueprintGroupController::class, 'delete'])->name('delete');
    });
});

// Users
Route::prefix('user')->as('user.')->group(function () {
    Route::get('all', [\Marble\Admin\Http\Controllers\UserController::class, 'index'])->name('index');
    Route::get('add', [\Marble\Admin\Http\Controllers\UserController::class, 'add'])->name('add');
    Route::post('create', [\Marble\Admin\Http\Controllers\UserController::class, 'create'])->name('create');
    Route::get('edit/{user}', [\Marble\Admin\Http\Controllers\UserController::class, 'edit'])->name('edit');
    Route::post('save/{user}', [\Marble\Admin\Http\Controllers\UserController::class, 'save'])->name('save');
    Route::delete('delete/{user}', [\Marble\Admin\Http\Controllers\UserController::class, 'delete'])->name('delete');
    Route::post('set-language', [\Marble\Admin\Http\Controllers\UserController::class, 'setLanguage'])->name('set-language');
});

// User Groups
Route::prefix('user-group')->as('user-group.')->group(function () {
    Route::get('all', [\Marble\Admin\Http\Controllers\UserGroupController::class, 'index'])->name('index');
    Route::get('add', [\Marble\Admin\Http\Controllers\UserGroupController::class, 'add'])->name('add');
    Route::get('edit/{group}', [\Marble\Admin\Http\Controllers\UserGroupController::class, 'edit'])->name('edit');
    Route::post('save/{group}', [\Marble\Admin\Http\Controllers\UserGroupController::class, 'save'])->name('save');
    Route::delete('delete/{group}', [\Marble\Admin\Http\Controllers\UserGroupController::class, 'delete'])->name('delete');
});

// Sites
Route::prefix('site')->as('site.')->group(function () {
    Route::get('/', [\Marble\Admin\Http\Controllers\SiteController::class, 'index'])->name('index');
    Route::get('create', [\Marble\Admin\Http\Controllers\SiteController::class, 'create'])->name('create');
    Route::post('store', [\Marble\Admin\Http\Controllers\SiteController::class, 'save'])->name('store');
    Route::get('edit/{site}', [\Marble\Admin\Http\Controllers\SiteController::class, 'edit'])->name('edit');
    Route::post('update/{site}', [\Marble\Admin\Http\Controllers\SiteController::class, 'save'])->name('update');
    Route::delete('delete/{site}', [\Marble\Admin\Http\Controllers\SiteController::class, 'delete'])->name('delete');
});

// Webhooks
Route::prefix('webhook')->as('webhook.')->group(function () {
    Route::get('/', [\Marble\Admin\Http\Controllers\WebhookController::class, 'index'])->name('index');
    Route::get('create', [\Marble\Admin\Http\Controllers\WebhookController::class, 'create'])->name('create');
    Route::post('store', [\Marble\Admin\Http\Controllers\WebhookController::class, 'save'])->name('store');
    Route::get('edit/{webhook}', [\Marble\Admin\Http\Controllers\WebhookController::class, 'edit'])->name('edit');
    Route::post('update/{webhook}', [\Marble\Admin\Http\Controllers\WebhookController::class, 'save'])->name('update');
    Route::delete('delete/{webhook}', [\Marble\Admin\Http\Controllers\WebhookController::class, 'delete'])->name('delete');
});

// Activity Log
Route::prefix('activity-log')->as('activity-log.')->group(function () {
    Route::get('/', [\Marble\Admin\Http\Controllers\ActivityLogController::class, 'index'])->name('index');
});


// Form Submissions (admin)
Route::prefix('form')->as('form.')->group(function () {
    Route::get('{item}/submissions', [\Marble\Admin\Http\Controllers\FormSubmissionController::class, 'index'])->name('index');
    Route::get('{item}/submissions/{submission}', [\Marble\Admin\Http\Controllers\FormSubmissionController::class, 'show'])->name('show');
    Route::post('{item}/submissions/{submission}/mark-read', [\Marble\Admin\Http\Controllers\FormSubmissionController::class, 'markRead'])->name('mark-read');
    Route::delete('{item}/submissions/{submission}', [\Marble\Admin\Http\Controllers\FormSubmissionController::class, 'destroy'])->name('destroy');
});

// Media Library
Route::prefix('media')->as('media.')->group(function () {
    Route::get('/', [\Marble\Admin\Http\Controllers\MediaController::class, 'index'])->name('index');
    Route::post('upload', [\Marble\Admin\Http\Controllers\MediaController::class, 'upload'])->name('upload');
    Route::post('folder/create', [\Marble\Admin\Http\Controllers\MediaController::class, 'createFolder'])->name('folder.create');
    Route::patch('folder/{folder}/rename', [\Marble\Admin\Http\Controllers\MediaController::class, 'renameFolder'])->name('folder.rename');
    Route::delete('folder/{folder}', [\Marble\Admin\Http\Controllers\MediaController::class, 'deleteFolder'])->name('folder.delete');
    Route::delete('{media}', [\Marble\Admin\Http\Controllers\MediaController::class, 'delete'])->name('delete');
    Route::get('json', [\Marble\Admin\Http\Controllers\MediaController::class, 'json'])->name('json');
    Route::get('picker-json', [\Marble\Admin\Http\Controllers\MediaController::class, 'pickerJson'])->name('picker-json');
    Route::get('transform/{media}', \Marble\Admin\Http\Controllers\MediaTransformController::class)->name('transform');
    Route::post('{media}/focal-point', [\Marble\Admin\Http\Controllers\MediaController::class, 'saveFocalPoint'])->name('focal-point');
    Route::post('ckeditor-upload', [\Marble\Admin\Http\Controllers\MediaController::class, 'ckeditorUpload'])->name('ckeditor-upload');
});

// API Token management (admin)
Route::prefix('api-tokens')->as('api-token.')->group(function () {
    Route::get('/', [\Marble\Admin\Http\Controllers\ApiTokenController::class, 'index'])->name('index');
    Route::post('create', [\Marble\Admin\Http\Controllers\ApiTokenController::class, 'create'])->name('create');
    Route::delete('delete/{token}', [\Marble\Admin\Http\Controllers\ApiTokenController::class, 'delete'])->name('delete');
});

// Marble Package Export/Import
Route::prefix('package')->as('package.')->group(function () {
    Route::get('/',      [\Marble\Admin\Http\Controllers\MarblePackageController::class, 'index'])->name('index');
    Route::get('export', [\Marble\Admin\Http\Controllers\MarblePackageController::class, 'exportForm'])->name('export');
    Route::post('export', [\Marble\Admin\Http\Controllers\MarblePackageController::class, 'export'])->name('export.do');
    Route::get('import', [\Marble\Admin\Http\Controllers\MarblePackageController::class, 'importForm'])->name('import');
    Route::post('import', [\Marble\Admin\Http\Controllers\MarblePackageController::class, 'import'])->name('import.do');
});


// Workflows
Route::prefix('workflow')->as('workflow.')->group(function () {
    Route::get('/', [\Marble\Admin\Http\Controllers\WorkflowController::class, 'index'])->name('index');
    Route::post('create', [\Marble\Admin\Http\Controllers\WorkflowController::class, 'create'])->name('create');
    Route::get('edit/{workflow}', [\Marble\Admin\Http\Controllers\WorkflowController::class, 'edit'])->name('edit');
    Route::post('save/{workflow}', [\Marble\Admin\Http\Controllers\WorkflowController::class, 'save'])->name('save');
    Route::delete('delete/{workflow}', [\Marble\Admin\Http\Controllers\WorkflowController::class, 'delete'])->name('delete');
});

// Notifications
Route::prefix('notifications')->as('notification.')->group(function () {
    Route::get('count', [\Marble\Admin\Http\Controllers\NotificationController::class, 'count'])->name('count');
    Route::get('recent', [\Marble\Admin\Http\Controllers\NotificationController::class, 'recent'])->name('recent');
    Route::post('mark-all-read', [\Marble\Admin\Http\Controllers\NotificationController::class, 'markAllRead'])->name('mark-all-read');
    Route::post('{notification}/mark-read', [\Marble\Admin\Http\Controllers\NotificationController::class, 'markRead'])->name('mark-read');
});

// Configuration
// AI Assistant
Route::post('ai/generate', [\Marble\Admin\Http\Controllers\AiAssistantController::class, 'generate'])
    ->name('ai.generate');

// Plugin Marketplace
Route::prefix('plugins')->as('plugin.')->group(function () {
    Route::get('/',                    [\Marble\Admin\Http\Controllers\PluginController::class, 'index'])->name('index');
    Route::get('{vendor}/{package}',   [\Marble\Admin\Http\Controllers\PluginController::class, 'show'])->name('show');
});

// Two-Factor Auth setup (authenticated)
Route::prefix('two-factor')->as('two-factor.')->group(function () {
    Route::get('generate-secret/{user}', [\Marble\Admin\Http\Controllers\Auth\TwoFactorController::class, 'generateSecret'])->name('generate-secret');
    Route::post('enable/{user}', [\Marble\Admin\Http\Controllers\Auth\TwoFactorController::class, 'enable'])->name('enable');
    Route::post('disable/{user}', [\Marble\Admin\Http\Controllers\Auth\TwoFactorController::class, 'disable'])->name('disable');
});

Route::prefix('configuration')->as('configuration.')->group(function () {
    Route::get('/', [\Marble\Admin\Http\Controllers\ConfigurationController::class, 'index'])->name('index');
    Route::post('settings', [\Marble\Admin\Http\Controllers\ConfigurationController::class, 'saveSettings'])->name('settings.save');
    Route::post('ai-settings', [\Marble\Admin\Http\Controllers\ConfigurationController::class, 'saveAiSettings'])->name('ai-settings.save');
    Route::post('languages', [\Marble\Admin\Http\Controllers\ConfigurationController::class, 'saveLanguages'])->name('languages.save');
    Route::post('languages/add', [\Marble\Admin\Http\Controllers\ConfigurationController::class, 'addLanguage'])->name('languages.add');
    Route::delete('languages/{language}', [\Marble\Admin\Http\Controllers\ConfigurationController::class, 'deleteLanguage'])->name('languages.delete');
    Route::post('media-blueprints', [\Marble\Admin\Http\Controllers\MediaBlueprintController::class, 'saveRules'])->name('media-blueprints.save');
    Route::post('crop-presets', [\Marble\Admin\Http\Controllers\ConfigurationController::class, 'saveCropPresets'])->name('crop-presets.save');
});

// Media field values
Route::prefix('media')->as('media.')->group(function () {
    Route::get('{media}/fields', [\Marble\Admin\Http\Controllers\MediaFieldController::class, 'edit'])->name('fields.edit');
    Route::post('{media}/fields', [\Marble\Admin\Http\Controllers\MediaFieldController::class, 'save'])->name('fields.save');
});

// Portal Users (admin management)
Route::prefix('portal-users')->as('portal-user.')->group(function () {
    Route::get('/', [\Marble\Admin\Http\Controllers\PortalUserController::class, 'index'])->name('index');
    Route::get('create', [\Marble\Admin\Http\Controllers\PortalUserController::class, 'create'])->name('create');
    Route::post('store', [\Marble\Admin\Http\Controllers\PortalUserController::class, 'store'])->name('store');
    Route::get('edit/{portalUser}', [\Marble\Admin\Http\Controllers\PortalUserController::class, 'edit'])->name('edit');
    Route::post('update/{portalUser}', [\Marble\Admin\Http\Controllers\PortalUserController::class, 'update'])->name('update');
    Route::delete('delete/{portalUser}', [\Marble\Admin\Http\Controllers\PortalUserController::class, 'delete'])->name('delete');
    Route::post('toggle/{portalUser}', [\Marble\Admin\Http\Controllers\PortalUserController::class, 'toggle'])->name('toggle');
});

// Redirect Manager
Route::prefix('redirects')->as('redirect.')->group(function () {
    Route::get('/', [\Marble\Admin\Http\Controllers\RedirectController::class, 'index'])->name('index');
    Route::post('store', [\Marble\Admin\Http\Controllers\RedirectController::class, 'store'])->name('store');
    Route::post('toggle/{redirect}', [\Marble\Admin\Http\Controllers\RedirectController::class, 'toggle'])->name('toggle');
    Route::delete('delete/{redirect}', [\Marble\Admin\Http\Controllers\RedirectController::class, 'destroy'])->name('destroy');
});
