<?php

use Illuminate\Http\Request;

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

Route::group(['prefix' => 'auth'], function () {
    Route::post('/login','AuthController@authenticate');
    Route::post('/logout','AuthController@logout');
    Route::post('/check','AuthController@check');
    Route::post('/register','AuthController@register');
    Route::post('/signup','AuthController@signup');
    Route::get('/activate/{token}','AuthController@activate');
    Route::post('/forget-password','AuthController@forgetPassword');
    Route::post('/validate-password-reset','AuthController@validatePasswordReset');
    Route::post('/reset-password','AuthController@resetPassword');
    Route::post('/social/token','SocialAuthController@getToken');
});

Route::group(['middleware' => ['jwt.auth']], function () {

  Route::get('/auth/user','AuthController@getAuthUser');
  Route::post('/auth/user/assign','AuthController@assign');

  Route::post('/group','GroupController@store');
  Route::get('/group','GroupController@index');
  Route::delete('/group/{id}','GroupController@destroy');
  Route::get('/group/{id}','GroupController@show');
  Route::patch('/group/{id}','GroupController@update');
  Route::post('/group/status','GroupController@toggleStatus');

    Route::get('/configuration/fetch','ConfigurationController@index');
    Route::post('/configuration','ConfigurationController@store');

    Route::get('/user','UserController@index');
    Route::post('/user/change-password','AuthController@changePassword');
    Route::post('/user/update-profile','UserController@updateProfile');
    Route::post('/user/update-avatar','UserController@updateAvatar');
    Route::post('/user/remove-avatar','UserController@removeAvatar');
    Route::delete('/user/{id}','UserController@destroy');
    Route::get('/user/dashboard','UserController@dashboard');

    Route::post('todo','TodoController@store');
    Route::get('/todo','TodoController@index');
    Route::delete('/todo/{id}','TodoController@destroy');
    Route::post('/todo/status','TodoController@toggleStatus');
});
