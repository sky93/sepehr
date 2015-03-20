<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/login', 'UserController@login');
Route::post('/login', 'UserController@postLogin');


$router->group(['middleware' => 'auth'], function() {
    Route::get('/logout', 'UserController@logout');

    Route::get('/', 'HomeController@index');
    Route::post('/', 'HomeController@postindex');

    Route::get('/downloads', 'HomeController@downloads');

    Route::get('/myfiles', 'HomeController@files');
    Route::post('/myfiles', 'HomeController@postfiles');

    Route::get('/public', 'HomeController@public_files');
});

$router->group(['middleware' => 'auth', 'role' => '2'], function() {
    Route::get('/tools/register', 'UserController@register');
    Route::post('/tools/register', 'UserController@postregister');

    Route::get('/tools/users', 'AdminController@users');
    Route::post('/tools/users', 'AdminController@users');

    Route::get('/tools/users/{username}', 'AdminController@user_details');
    Route::post('/tools/users/{username}', 'AdminController@postuser_details');
});

Route::controllers([
	'auth' => 'Auth\AuthController',
	'password' => 'Auth\PasswordController',
]);
