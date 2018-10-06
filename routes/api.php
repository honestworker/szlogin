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
    Route::post('/advertisement','AdvertisementController@store');
    
    Route::post('/login','AuthController@authenticate');
    Route::post('/login_backend','AuthController@login');
    Route::post('/logout','AuthController@logout');
    Route::post('/check','AuthController@check');
    Route::post('/register','AuthController@register');
    Route::post('/signup_backend','AuthController@signupBackend');
    Route::post('/signup','AuthController@signup');
    Route::get('/activate/{token}','AuthController@activate');
    Route::post('/forget-password','AuthController@forgetPassword');
    Route::post('/validate-password-reset','AuthController@validatePasswordReset');
    Route::post('/reset-password','AuthController@resetPassword');
    Route::post('/social/token','SocialAuthController@getToken');
    
    //app only
    Route::post('/country/all','CountryController@all');
});

Route::group(['middleware' => ['jwt.auth']], function () {

    Route::get('/auth/user','AuthController@getAuthUser');
    Route::post('/user/assign','AuthController@assignGroup');
    Route::post('/user/role','AuthController@changeRole');

    // Group
    Route::get('/group','GroupController@index');
    Route::post('/group','GroupController@store');
    Route::post('/group/all','GroupController@all');
    Route::delete('/group/{id}','GroupController@destroy');
    Route::get('/group/{id}','GroupController@show');
    Route::patch('/group/{id}','GroupController@update');
    Route::post('/group/status','GroupController@toggleStatus');

    // Country
    Route::post('/country','CountryController@store');
    Route::get('/country','CountryController@index');
    Route::delete('/country/{id}','CountryController@destroy');
    Route::get('/country/{id}','CountryController@show');
    Route::patch('/country/{id}','CountryController@update');
    Route::post('/country/status','CountryController@toggleStatus');
    Route::post('/country/all','CountryController@all');

    // User
    Route::get('/user','UserController@index');
    Route::post('/user/{id}','UserController@getUser');
    Route::post('/user/change-password','AuthController@changePassword');
    Route::post('/user/update-profile','UserController@updateProfile');
    Route::post('/user/update-avatar','UserController@updateAvatar');
    Route::post('/user/remove-avatar','UserController@removeAvatar');
    Route::delete('/user/{id}','UserController@destroy');
    Route::get('/user/dashboard','UserController@dashboard');
    
    //app only
    Route::post('/profile', 'UserController@profile');
    Route::post('/change-password','UserController@changePassword');
    Route::post('/change-avatar','UserController@updateAvatar');
    Route::post('/delete-account','UserController@deleteAccount');
    
    Route::post('/logout','AuthController@logout');

    // Notification
    Route::get('/notification','NotificationController@index');
    
    // Notification app only
    Route::post('/get-notification','NotificationController@getNotification');
    Route::post('/create-notification','NotificationController@createNotification');

    // Notification Type
    Route::post('/noti_type','NotificationController@storeType');
    Route::get('/noti_type','NotificationController@indexType');
    Route::delete('/noti_type/{id}','NotificationController@destroyType');
    Route::get('/noti_type/{id}','NotificationController@showType');
    Route::patch('/noti_type/{id}','NotificationController@updateType');
    Route::post('/noti_type/status','NotificationController@toggleTypeStatus');
    Route::post('/noti_type/all','NotificationController@allType');
    
    // Advertisement
    Route::get('/advertisement','AdvertisementController@index'); // Get All
    Route::post('/advertisement','AdvertisementController@store'); // Create
    Route::delete('/advertisement/{id}','AdvertisementController@destroy');
    Route::get('/advertisement/{id}','AdvertisementController@show');
    Route::patch('/advertisement/{id}','AdvertisementController@update');
    Route::post('/advertisement/status','AdvertisementController@toggleStatus');
    Route::post('/advertisement/all','AdvertisementController@all');
    
    Route::get('/configuration/fetch','ConfigurationController@index');
    Route::post('/configuration','ConfigurationController@store');

    Route::post('todo','TodoController@store');
    Route::get('/todo','TodoController@index');
    Route::delete('/todo/{id}','TodoController@destroy');
    Route::post('/todo/status','TodoController@toggleStatus');
});
