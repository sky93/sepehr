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


$router->group(['middleware' => ['auth', 'empty_email']], function() {
    Route::get('/', 'HomeController@index');
    Route::post('/', 'HomeController@postindex');

    Route::get('/home', 'HomeController@index');
    Route::post('/home', 'HomeController@postindex');

    Route::get('/downloads', 'HomeController@downloads');
    Route::post('/downloads', 'HomeController@post_downloads');

    Route::get('/files', 'HomeController@files');
    Route::post('/files', 'HomeController@postfiles');

    Route::get('/public', 'HomeController@public_files');

    Route::get('/files/{id}', 'HomeController@download_id');
    Route::post('/files/{id}', 'HomeController@post_download_id');

    Route::get('downloads/dl', 'HomeController@dl');

    Route::get('user/{username}', 'UserController@user_info');
    Route::post('user/{username}', 'UserController@post_user_info');

    Route::get('user/{username}/password', 'UserController@password');
    Route::post('user/{username}/password', 'UserController@post_password');

    Route::get('user/{username}/credit/history', 'UserController@credit_history');

    Route::get('buy', 'PaymentController@credit_buy');
    Route::post('buy', 'PaymentController@post_credit_buy');

    Route::get('user/{username}/payments/history', 'PaymentController@payment_history');
});

$router->group(['middleware' => 'auth'], function() {
    Route::get('/logout', 'UserController@logout');
});



//Admin's routes
$router->group(['middleware' => ['auth', 'role:2', 'empty_email']], function() {
    Route::get('/tools/register', 'UserController@register');
    Route::post('/tools/register', 'UserController@postregister');

    Route::get('/tools/register-csv', 'UserController@register_csv');
    Route::post('/tools/register-csv', 'UserController@postregister_csv');

    Route::get('/tools/users', 'AdminController@users');
    Route::post('/tools/users', 'AdminController@post_users');

    Route::get('/tools/users/{username}', 'AdminController@user_details');
    Route::post('/tools/users/{username}', 'AdminController@postuser_details');

    Route::get('/tools/users/{username}/credits', 'AdminController@user_details_credits');
    Route::post('/tools/users/{username}/credits', 'AdminController@postuser_details_credits');

    Route::get('/tools/aria2console', 'AdminController@aria2console');
    Route::post('/tools/aria2console', 'AdminController@post_aria2console');

    Route::get('/tools/status', 'AdminController@stat');
    Route::post('/tools/status', 'AdminController@post_stat');

    Route::get('/tools/files', 'AdminController@files');
    Route::post('/tools/files', 'AdminController@post_files');

    Route::get('/tools/payments', 'AdminController@payments');
});


Route::controllers([
    'password' => 'Auth\PasswordController',
]);

