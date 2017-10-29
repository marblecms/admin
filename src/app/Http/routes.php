<?php

$groupConfigWithAuth = $groupConfig = [
    'middleware' => ['web'], 
    'prefix' => 'admin', 
    'namespace'  => 'Marble\Admin\App\Http\Controllers'
];

$groupConfigWithAuth["middleware"][] = "auth";

Route::group($groupConfig, function () {
    Route::get('login', 'Auth\LoginController@showLoginForm');
    Route::post('login', 'Auth\LoginController@login');
    Route::post('logout', 'Auth\LoginController@logout');
});

Route::group($groupConfig, function () {
    Route::get('dashboard', "DashboardController@view");

    Route::get('node/search.json', "NodeController@searchJSON");

    Route::get('node/edit/{id}', "NodeController@edit");
    Route::get('node/edit/{id}/{iframe}', "NodeController@edit");
    Route::get('node/addiframe', "NodeController@addIframe");
    Route::get('node/add/{id}', "NodeController@add");
    Route::get('node/delete/{id}', "NodeController@delete");
    Route::post('node/save/{id}', "NodeController@save");
    Route::post('node/save/{id}/{iframe}', "NodeController@save");
    Route::post('node/create', "NodeController@create");
    Route::post('node/create/{id}', "NodeController@create");
    Route::post('node/sort', "NodeController@sort");
    Route::post('node/ajaxattribute/{id}/{locale}', "NodeController@ajaxAttribute");
    Route::get('node/{id}/allowedchildclasses.json', "NodeController@allowedChildClassesJson");

    Route::get('nodeclass/all', "NodeClassController@all");
    Route::get('nodeclass/add', "NodeClassController@add");
    Route::get('nodeclass/edit/{id}', "NodeClassController@edit");
    Route::post('nodeclass/save/{id}', "NodeClassController@save");
    Route::get('nodeclass/delete/{id}', "NodeClassController@delete");

    Route::get('nodeclass/export/{id}', "NodeClass\ImportExportController@export");
    Route::post('nodeclass/import', "NodeClass\ImportExportController@import");

    Route::get('nodeclass/attributes/edit/{id}', "NodeClass\AttributesController@edit");
    Route::post('nodeclass/attributes/add/{id}', "NodeClass\AttributesController@add");
    Route::get('nodeclass/attributes/delete/{id}/{attribute_id}', "NodeClass\AttributesController@delete");
    Route::post('nodeclass/attributes/save/{id}', "NodeClass\AttributesController@save");

    Route::get('nodeclass/groups/add', "NodeClass\GroupsController@add");
    Route::get('nodeclass/groups/edit/{id}', "NodeClass\GroupsController@edit");
    Route::get('nodeclass/groups/delete/{id}', "NodeClass\GroupsController@delete");
    Route::post('nodeclass/groups/save/{id}', "NodeClass\GroupsController@save");

    Route::post('nodeclass/attributegroups/add/{id}', "NodeClass\AttributeGroupsController@add");
    Route::post('nodeclass/attributegroups/sort/{id}', "NodeClass\AttributeGroupsController@sort");
    Route::post('nodeclass/attributegroups/save/{id}', "NodeClass\AttributeGroupsController@save");
    Route::get('nodeclass/attributegroups/delete/{id}/{groupId}', "NodeClass\AttributeGroupsController@delete");

    Route::get('user/all', "UserController@all");
    Route::get('user/add', "UserController@add");
    Route::post('user/save/{id}', "UserController@save");
    Route::get('user/edit/{id}', "UserController@edit");
    Route::get('user/delete/{id}', "UserController@delete");

    Route::get('usergroup/all', "UserGroupController@all");
    Route::get('usergroup/add', "UserGroupController@add");
    Route::post('usergroup/save/{id}', "UserGroupController@save");
    Route::get('usergroup/edit/{id}', "UserGroupController@edit");
    Route::get('usergroup/delete/{id}', "UserGroupController@delete");
});