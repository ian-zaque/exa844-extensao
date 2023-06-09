<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => 'api', 'prefix' => 'auth'], function ($router) {
    Route::resource('/auth', 'AuthController');
    Route::post('login', 'AuthController@login');
    Route::post('register', 'AuthController@register');
    Route::patch('update', 'AuthController@update');
    Route::post('create', 'AuthController@create');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::get('me', 'AuthController@me');
});

Route::group(['middleware' => 'api', 'prefix' => 'auth'], function ($router) {
    Route::resource('/user', 'UserController');
});

Route::group(['middleware' => 'api', 'prefix' => 'auth'], function ($router) {
    Route::resource('/role', 'RoleController');
});
