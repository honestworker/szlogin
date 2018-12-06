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
    Route::post('/login_backend','AuthController@login');
    Route::post('/logout','AuthController@logout');
    Route::post('/check','AuthController@check');
    //Route::post('/register','AuthController@register');
    Route::post('/signup_backend','AuthController@signupBackend');
    Route::post('/signup','AuthController@signup');
    Route::get('/activate/{token}','AuthController@activate');
    Route::post('/forget-password','AuthController@forgetPassword');
    Route::post('/validate-password-reset','AuthController@validatePasswordReset');
    Route::post('/reset-password','AuthController@resetPassword');
    Route::post('/social/token','SocialAuthController@getToken');
    
    // app only
    Route::post('/country/all','CountryController@all');
});

Route::group(['middleware' => ['jwt.auth']], function () {

    Route::get('/auth/user','AuthController@getAuthUser');
    Route::post('/user/assign','AuthController@assignGroup');

    ///// Group
    Route::get('/group','GroupController@index');
    Route::post('/group','GroupController@store');
    Route::post('/group/all','GroupController@all');
    Route::delete('/group/{id}','GroupController@destroy');
    Route::get('/group/{id}','GroupController@show');
    Route::patch('/group/{id}','GroupController@update');
    Route::post('/group/status','GroupController@toggleStatus');

    // User app only
    Route::post('/group/attach','GroupController@attachGroup');
    Route::post('/group/delete-attach','GroupController@deleteAttachGroup');
    Route::post('/group/attached','GroupController@getAttachedGroups');

    Route::post('/group/overview','GroupController@overview');
    
    ///// Country
    Route::post('/country','CountryController@store');
    Route::get('/country','CountryController@index');
    Route::delete('/country/{id}','CountryController@destroy');
    Route::get('/country/{id}','CountryController@show');
    Route::patch('/country/{id}','CountryController@update');
    Route::post('/country/status','CountryController@toggleStatus');
    Route::post('/country/all','CountryController@all');

    ///// User
    Route::get('/user','UserController@index');
    Route::post('/user/profile','UserController@getMyProfile');
    Route::post('/user/group','UserController@ownGroup');
    Route::post('/user/update-avatar','UserController@updateAvatar');
    Route::post('/user/change-password','UserController@changePasswordBackend');
    Route::post('/user/update-profile','UserController@updateProfile');
    Route::post('/user/{id}','UserController@getUser');
    Route::post('/user/remove-avatar','UserController@removeAvatar');
    Route::delete('/user/{id}','UserController@deleteAccountBackend');
    Route::get('/user/overview','UserController@overview');
    
    Route::post('/user/group/{id}','UserController@makeGroupManager');
    Route::delete('/user/group/{id}','UserController@disableGroupManager');
    Route::post('/user/admin/{id}','UserController@makeAdministrator');
    Route::delete('/user/admin/{id}','UserController@disableAdministrator');
    
    // User app only
    Route::post('/get-group-users','UserController@getGroupUsers');
    
    // User app only
    Route::post('/profile', 'UserController@profile');
    Route::post('/change-password','UserController@changePassword');
    Route::post('/change-avatar','UserController@updateAvatar');
    Route::delete('/delete-account','UserController@destroy');
    Route::delete('/user','UserController@deleteAccount');

    Route::post('/language','UserController@setLanguage');

    Route::post('/logout','AuthController@logout');

    // User Push Notification app only
    Route::post('/push_token','UserController@savePushToken');

    // User Push Notification Effect app only
    Route::post('/push_effect','UserController@setPushEffect');
    

    ///// Notification
    Route::get('/notification','NotificationController@index');
    Route::post('/notification/{id}','NotificationController@getNotification');
    Route::delete('/notification/{id}','NotificationController@deleteNotificationBackend');
    
    // Notification app only
    Route::post('/get-notification','NotificationController@getNotifications');
    Route::post('/create-notification','NotificationController@createNotification');
    Route::post('/get-notification-detail','NotificationController@getNotificationDetail');
    Route::patch('/update-notification','NotificationController@updateNotification');

    Route::post('/notification-delete','NotificationController@deleteNotification');
    Route::post('/notification-image-delete','NotificationController@deleteNotificationImage');
    Route::post('/comment-delete','NotificationController@deleteComment');
    Route::post('/comment-image-delete','NotificationController@deleteCommentImage');

    Route::post('/get-alarms','NotificationController@getAlarms');

    // Comment app only
    Route::post('/create-comment','NotificationController@createComment');

    ///// Notification Type
    Route::post('/noti_type','NotificationController@storeType');
    Route::get('/noti_type','NotificationController@indexType');
    Route::delete('/noti_type/{id}','NotificationController@destroyType');
    Route::get('/noti_type/{id}','NotificationController@showType');
    Route::patch('/noti_type/{id}','NotificationController@updateType');
    Route::post('/noti_type/status','NotificationController@toggleTypeStatus');
    Route::post('/noti_type/all','NotificationController@allType');
    
    ///// Advertisement
    Route::get('/advertisement','AdvertisementController@index'); // Get All
    Route::post('/advertisement','AdvertisementController@store'); // Create
    Route::delete('/advertisement/{id}','AdvertisementController@destroy');
    Route::get('/advertisement/{id}','AdvertisementController@show');
    Route::patch('/advertisement/{id}','AdvertisementController@update');
    Route::post('/advertisement/status','AdvertisementController@toggleStatus');
    Route::post('/advertisement/all','AdvertisementController@all');
    Route::post('/advertisement/infor','AdvertisementController@infor');
    Route::post('/advertisement/overview','AdvertisementController@overview');

    // Advertisement app only
    Route::post('/advertisement/get','AdvertisementController@get');
    Route::post('/advertisement/click','AdvertisementController@click');
});
