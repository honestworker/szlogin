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
    Route::get('/countries','ApiController@allCountry');

    Route::post('/signup','ApiController@signup');
    Route::post('/login','ApiController@login');
    Route::post('/logout','ApiController@logout');

    Route::post('/forgot-password','ApiController@forgotPassword');
    Route::post('/validate-password-reset','ApiController@validatePasswordReset');
    Route::post('/reset-password','ApiController@resetPassword');
});

Route::group(['middleware' => ['jwt.auth']], function () {
    // Unreads Of "Activity Logs" & "Group Members"
    Route::get('/unreadpending', 'ApiController@unReadPending');

    // Notification all admins of all pending groups
    Route::post('/notify/joingroups','ApiController@nofifyJoinGroups');
    
    // User
    Route::get('/profile', 'ApiController@profile');
    Route::post('/change-password','ApiController@changePassword');
    Route::post('/update-avatar','ApiController@updateAvatar'); //
    Route::delete('/delete-account','ApiController@destroyAccount'); //
    Route::delete('/user','ApiController@deleteAccount'); //
    Route::post('/language','ApiController@setLanguage');
    
    // Group
    Route::post('/group/users','ApiController@getGroupUsers');
    Route::post('/group','ApiController@storeGroup');
    
    // UserGroup
    Route::get('/allgroups','ApiController@allGroups');
    Route::get('/owngroups','ApiController@getOwnGroups');
    Route::get('/usergroup','ApiController@getUserGroups');
    Route::post('/usergroup/join','ApiController@joinGroupUser');
    Route::post('/usergroup/active','ApiController@activeGroupUser');
    Route::post('/usergroup/deactive','ApiController@deactiveGroupUser');
    Route::post('/usergroup/admin','ApiController@makeGroupAdmin');
    Route::post('/usergroup/user','ApiController@makeGroupUser');
    Route::delete('/usergroup','ApiController@deleteGroupUser');
    
    // Notification
    Route::post('/create-notification','ApiController@createNotification');
    Route::post('/get-group-notification','ApiController@getGroupNotifications');
    Route::post('/get-notification-detail','ApiController@getNotificationDetail');
    Route::patch('/update-notification','ApiController@updateNotification');
    
    Route::delete('/notification','ApiController@deleteNotification');
    Route::delete('/notification-image','ApiController@deleteNotificationImage');

    // Comment
    Route::post('/create-comment','ApiController@createComment');
    Route::delete('/comment','ApiController@deleteComment');
    Route::delete('/comment-image','ApiController@deleteCommentImage');

    // Advertisement
    Route::get('/advertisement/get','ApiController@getAdvertisement');
    Route::post('/advertisement/click','ApiController@clickAdvertisement');

    // Push Notification
    Route::post('/push-token','ApiController@savePushToken');
    Route::post('/push-effect','ApiController@setPushEffect');
});
