<?php


$app->get('/', function () use ($app) {
    return $app->version();
});

$api = app('Dingo\Api\Routing\Router');

// v1 version API
// choose version add this in header    Accept:application/vnd.lumen.v1+json
$api->version('v1', ['namespace' => 'App\Http\Controllers\Api\V1'], function ($api) {

    // Auth
    // login
    $api->post('authorizations', [
        'as' => 'authorizations.store',
        'uses' => 'AuthController@store',
    ]);

    // User
    $api->post('users', [
        'as' => 'users.store',
        'uses' => 'UserController@store',
    ]);
    // user list
    $api->get('users', [
        'as' => 'users.index',
        'uses' => 'UserController@index',
    ]);
    // user detail
    $api->get('users/{id}', [
        'as' => 'users.show',
        'uses' => 'UserController@show',
    ]);
    // AUTH
    // refresh jwt token
    $api->put('authorizations', [
        'as' => 'authorizations.update',
        'uses' => 'AuthController@update',
    ]);
   //rabc
    $api->get('role', [
        'as' => 'role.index',
        'uses' => 'RoleController@index',
    ]);
    $api->post('role', [
        'as' => 'role.store',
        'uses' => 'RoleController@store',
    ]);
    $api->post('role/edit', [
        'as' => 'role/edit.edit',
        'uses' => 'RoleController@edit',
    ]);
    $api->post('role/delete', [
        'as' => 'role/delete.delete',
        'uses' => 'RoleController@delete',
    ]);
    $api->post('role/keep', [
        'as' => 'role/keep.keep',
        'uses' => 'RoleController@keep',
    ]);
    // need authentication
    $api->group(['middleware' => 'api.auth'], function ($api) {

        // USER
        // my detail
        $api->get('user', [
            'as' => 'user.show',
            'uses' => 'UserController@userShow',
        ]);

        // update part of me
        $api->patch('user', [
            'as' => 'user.update',
            'uses' => 'UserController@patch',
        ]);
        // update my password
        $api->put('user/password', [
            'as' => 'user.password.update',
            'uses' => 'UserController@editPassword',
        ]);
    });
});

