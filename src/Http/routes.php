<?php

use Illuminate\Support\Facades\Route;

// Dashboard
Route::get('dashboard', [\Marble\Admin\Http\Controllers\DashboardController::class, 'view'])
    ->name('dashboard');

// Items
Route::prefix('item')->as('item.')->group(function () {
    Route::get('edit/{item}', [\Marble\Admin\Http\Controllers\ItemController::class, 'edit'])->name('edit');
    Route::post('save/{item}', [\Marble\Admin\Http\Controllers\ItemController::class, 'save'])->name('save');
    Route::get('add/{parentItem}', [\Marble\Admin\Http\Controllers\ItemController::class, 'add'])->name('add');
    Route::post('create', [\Marble\Admin\Http\Controllers\ItemController::class, 'create'])->name('create');
    Route::delete('delete/{item}', [\Marble\Admin\Http\Controllers\ItemController::class, 'delete'])->name('delete');
    Route::post('duplicate/{item}', [\Marble\Admin\Http\Controllers\ItemController::class, 'duplicate'])->name('duplicate');
    Route::get('move/{item}', [\Marble\Admin\Http\Controllers\ItemController::class, 'moveForm'])->name('move-form');
    Route::post('move/{item}', [\Marble\Admin\Http\Controllers\ItemController::class, 'move'])->name('move');
    Route::post('toggle-status/{item}', [\Marble\Admin\Http\Controllers\ItemController::class, 'toggleStatus'])->name('toggle-status');
    Route::post('toggle-nav/{item}', [\Marble\Admin\Http\Controllers\ItemController::class, 'toggleNav'])->name('toggle-nav');
    Route::post('revert/{item}/{revision}', [\Marble\Admin\Http\Controllers\ItemController::class, 'revert'])->name('revert');
    Route::get('diff/{item}/{revision}', [\Marble\Admin\Http\Controllers\ItemController::class, 'diff'])->name('diff');
    Route::post('sort', [\Marble\Admin\Http\Controllers\ItemController::class, 'sort'])->name('sort');
    Route::get('search.json', [\Marble\Admin\Http\Controllers\ItemController::class, 'searchJson'])->name('search');
    Route::post('ajax-field/{itemValue}/{language}', [\Marble\Admin\Http\Controllers\ItemController::class, 'ajaxField'])->name('ajax-field');
    // URL Aliases
    Route::post('aliases/{item}', [\Marble\Admin\Http\Controllers\ItemController::class, 'saveAliases'])->name('aliases.save');
    // Content locking
    Route::post('lock/{item}', [\Marble\Admin\Http\Controllers\ItemController::class, 'acquireLock'])->name('lock');
    Route::delete('lock/{item}', [\Marble\Admin\Http\Controllers\ItemController::class, 'releaseLock'])->name('unlock');
    // Import / Export
    Route::get('export/{item}', [\Marble\Admin\Http\Controllers\ImportExportController::class, 'export'])->name('export');
    Route::get('import', [\Marble\Admin\Http\Controllers\ImportExportController::class, 'importForm'])->name('import-form');
    Route::post('import', [\Marble\Admin\Http\Controllers\ImportExportController::class, 'import'])->name('import');
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
    Route::get('export', [\Marble\Admin\Http\Controllers\MarblePackageController::class, 'exportForm'])->name('export');
    Route::post('export', [\Marble\Admin\Http\Controllers\MarblePackageController::class, 'export'])->name('export.do');
    Route::get('import', [\Marble\Admin\Http\Controllers\MarblePackageController::class, 'importForm'])->name('import');
    Route::post('import', [\Marble\Admin\Http\Controllers\MarblePackageController::class, 'import'])->name('import.do');
});


// Redirect Manager
Route::prefix('redirects')->as('redirect.')->group(function () {
    Route::get('/', [\Marble\Admin\Http\Controllers\RedirectController::class, 'index'])->name('index');
    Route::post('store', [\Marble\Admin\Http\Controllers\RedirectController::class, 'store'])->name('store');
    Route::post('toggle/{redirect}', [\Marble\Admin\Http\Controllers\RedirectController::class, 'toggle'])->name('toggle');
    Route::delete('delete/{redirect}', [\Marble\Admin\Http\Controllers\RedirectController::class, 'destroy'])->name('destroy');
});
