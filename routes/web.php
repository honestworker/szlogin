<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/auth/social/{provider}', 'SocialAuthController@providerRedirect');
Route::get('/auth/{provider}/callback', 'SocialAuthController@providerRedirectCallback');

//Route::post('/social/token','SocialAuthController@getToken');

////// Admin //////
Route::group(['prefix' => 'auth'], function () {
    Route::post('/signup','AuthController@signup');
    Route::post('/login','AuthController@login');
    Route::post('/logout','AuthController@logout');
    
    Route::post('/check','AuthController@check');
    Route::get('/activate/{token}','AuthController@activate');
    
    Route::post('/forgot-password','AuthController@forgotPassword');
    Route::post('/validate-password-reset','AuthController@validatePasswordReset');
    Route::post('/reset','AuthController@resetPassword');
});

Route::group(['middleware' => ['jwt.auth'], 'prefix' => 'admin'], function () {
    Route::get('/auth/user','AuthController@getAuthUser');
    
    ///// Overview
    Route::get('/group/overview','GroupController@overview');
    Route::get('/user/overview','UserController@overview');
    Route::get('/advertisement/overview','AdvertisementController@overview');

    ///// Group
    Route::get('/groups','GroupController@all');
    Route::get('/group','GroupController@index');
    Route::post('/group','GroupController@store');
    Route::delete('/group/{id}','GroupController@destroy');
    Route::get('/group/{id}','GroupController@show');
    Route::patch('/group/{id}','GroupController@update');
    Route::post('/group/status','GroupController@toggleStatus');
    Route::get('/group/country/{country}','GroupController@getByCountry');

    ///// User
    Route::get('/user','UserController@index');
    Route::get('/user/profile','UserController@getMyProfile');
    Route::post('/user/update-avatar','UserController@updateAvatar');
    Route::post('/user/change-password','UserController@changePassword');
    Route::post('/user/update-profile','UserController@updateProfile');
    Route::get('/user/{id}','UserController@getUser');
    Route::post('/user/remove-avatar','UserController@removeAvatar');
    Route::delete('/user/{id}','UserController@deleteAccount');

    Route::patch('/user/admin/{id}','UserController@makeAdministrator');
    Route::delete('/user/admin/{id}','UserController@disableAdministrator');

    ///// UserGroup
    Route::get('/usergroup','GroupUserController@index');
    Route::get('/usergroup/user','GroupUserController@getUsers');
    Route::post('/usergroup/active/{user_id}/{usergroup_id}','GroupUserController@activeUser');
    Route::post('/usergroup/deactive/{user_id}/{usergroup_id}','GroupUserController@deactiveUser');
    Route::post('/usergroup/manager/{user_id}/{usergroup_id}','GroupUserController@makeManger');
    Route::post('/usergroup/user/{user_id}/{usergroup_id}','GroupUserController@makeUser');
    Route::delete('/usergroup/{user_id}/{usergroup_id}','GroupUserController@deleteUser');
    
    ///// Country
    Route::get('/country','CountryController@index');
    Route::post('/country','CountryController@store');
    Route::get('/countries','CountryController@all');
    Route::delete('/country/{id}','CountryController@destroy');
    Route::get('/country/{id}','CountryController@show');
    Route::patch('/country/{id}','CountryController@update');
    Route::post('/country/status','CountryController@toggleStatus');
    
    ///// Notification
    Route::get('/notification','NotificationController@index');
    Route::post('/notification/status','NotificationController@toggleStatus');
    Route::post('/notification/{id}','NotificationController@getNotification');
    Route::get('/noti_comment','NotificationController@indexComments');
    Route::post('/noti_comment/status','NotificationController@toggleCommentStatus');
    Route::delete('/noti_comment/{id}','NotificationController@deleteComment');
    Route::delete('/notification/{id}','NotificationController@deleteNotification');
    Route::post('/update-notification','NotificationController@updateNotification');
    
    ///// System Notification
    Route::get('/sysnoti','NotificationController@indexSys');
    Route::post('/create-sysnoti','NotificationController@createSysNotification');
    
    ///// Notification Type
    //Route::post('/noti_type','NotificationController@storeType');
    //Route::get('/noti_type','NotificationController@indexType');
    //Route::delete('/noti_type/{id}','NotificationController@destroyType');
    //Route::get('/noti_type/{id}','NotificationController@showType');
    //Route::patch('/noti_type/{id}','NotificationController@updateType');
    //Route::post('/noti_type/status','NotificationController@toggleTypeStatus');
    //Route::get('/noti_type/all','NotificationController@allType');
    Route::get('/noti_type/commons','NotificationController@allCommonType');
    
    ///// Advertisement
    Route::post('/advertisement','AdvertisementController@store'); // Create
    Route::delete('/advertisement/{id}','AdvertisementController@destroy');
    Route::get('/advertisement/{id}','AdvertisementController@show');
    Route::patch('/advertisement/status','AdvertisementController@toggleStatus');
    Route::patch('/advertisement/{id}','AdvertisementController@update');
    Route::get('/advertisement/all','AdvertisementController@all');
    Route::get('/advertisement/infor','AdvertisementController@infor');
    Route::get('/advertisement','AdvertisementController@index'); // Get All
});

Route::get('/{vue?}', function () {
    return view('home');
})->where('vue', '[\/\w\.-]*')->name('home');