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

Route::get('/rrr', function(){

    $url = 'http://mirrors.hust.edu.cn/qtproject/archive/qt/5.4/5.4.1/qt-opensource-windows-x86-msvc2013_64-5.4.1.exe';

    if (!$fp = fopen($url, 'r')) {
        trigger_error("Unable to open URL ($url)", E_USER_ERROR);
    }

    $meta = stream_get_meta_data($fp);

    print_r($meta);

    fclose($fp);
});

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
});

Route::controllers([
	'auth' => 'Auth\AuthController',
	'password' => 'Auth\PasswordController',
]);
