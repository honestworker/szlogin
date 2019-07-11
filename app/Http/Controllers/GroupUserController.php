<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use JWTAuth;

use App\Notifications\GroupManager;
use App\Notifications\Administrator;

date_default_timezone_set("Europe/Stockholm");

class GroupUserController extends Controller
{
    protected $avatar_path = 'images/users/';
    
    private function sendPushNotificationHttpRequest($user_id, $notification_names, $params_data = array()){
        $requests = $responses = [];

        $user = \App\User::find($user_id);
        $profile = $user->Sound_Profile;
        if ($profile) {
            if ( $profile->push_token != '' ) {
                $notification_name = '';
                if (strtolower($profile->language) == 'swedish') {
                    $notification_name = $notification_names['swedish'];
                } else {
                    $notification_name = $notification_names['else'];
                }
                $params = array(
                    'app_id' => SZ_PUSHNOTI_APP_ID,
                    'include_player_ids' => [ $profile->push_token ],
                    'headings' => array('en' => 'Safety Zone'),
                    'contents' => array('en' => $notification_name),
                    'data' => $params_data
                );
                if ( $profile->os_type == 'android' ) {
                    if ( $profile->sound == "sound1" && $profile->vibration == 1 ) {
                        $params['android_channel_id'] = SZ_PUSHNOTI_ANDROID_CHANNEL_1;
                    } else if ( $profile->sound == "sound1" && $profile->vibration == 0 ) {
                        $params['android_channel_id'] = SZ_PUSHNOTI_ANDROID_CHANNEL_2;
                    } else if ( $profile->sound == "sound2" && $profile->vibration == 1 ) {
                        $params['android_channel_id'] = SZ_PUSHNOTI_ANDROID_CHANNEL_3;
                    } else if ( $profile->sound == "sound2" && $profile->vibration == 0 ) {
                        $params['android_channel_id'] = SZ_PUSHNOTI_ANDROID_CHANNEL_4;
                    } else if ( $profile->sound == "no_sound" && $profile->vibration == 1 ) {
                        $params['android_channel_id'] = SZ_PUSHNOTI_ANDROID_CHANNEL_5;
                    } else if ( $profile->sound == "no_sound" && $profile->vibration == 0 ) {
                        $params['android_channel_id'] = SZ_PUSHNOTI_ANDROID_CHANNEL_6;
                    }
                } else if ( $profile->os_type == 'ios' ) {
                    if ( $profile->vibration == 0 && $profile->sound == 'no_sound' ) {
                        $params['ios_sound'] = "nil";
                    } else {
                        $params['ios_sound'] = $profile->sound . ".wav";
                    }
                }
                $requests[] = $params;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, SZ_PUSHNOTI_URL);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type: application/json", "Authorization: Basic ". SZ_PUSHNOTI_AUTH));
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);
                curl_setopt($ch, CURLOPT_TIMEOUT, 120);
                
                $responses[] = curl_exec($ch);
                if(curl_errno($ch) !== 0) {
                    error_log('cURL error when connecting to ' . SZ_PUSHNOTI_URL . ': ' . curl_error($ch));
                }
                
                curl_close($ch);
            }
        }
        return array('request' => $requests, 'response' => $responses);
    }

    public function index(){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['authenticated' => false], 422);
        }
        
        $user = JWTAuth::parseToken()->authenticate();
        if (!$user->backend)
            return response()->json(['status' => 'fail', 'message' => 'You do not have the backend permission!', 'data' => null, 'error_type' => 'no_permission'], 422);
        
        $group_users = \App\GroupUser::with('group');
        
        if(request()->has('user_id'))
            $group_users->where('user_id',request('user_id'));

        if(request()->has('group_id'))
            $group_users->where('group_id',request('group_id'));
            
        if(request()->has('name'))
            if (request('name'))
                $group_users->whereHas('group',function($q) {
                    $q->where('name','like', '%'. request('name') . '%');
                });
        
        if(request()->has('postal_code'))
            if (request('postal_code'))
                $group_users->whereHas('group',function($q) {
                    $q->where('postal_code','like', '%'. request('postal_code') . '%');
                });
        
        if(request()->has('admin'))
            if (request('admin') >= 0)
                $group_users->where('admin',request('admin'));
        
        if(request()->has('status'))
            if (request('status') >= 0)
                $group_users->whereStatus(request('status'));
                
        if(request()->has('sortBy') && request()->has('order')) {
            if(request('sortBy') == 'status' || request('sortBy') == 'created_at' || request('sortBy') == 'admin')
                $group_users->select('id', 'user_id', 'group_id', 'admin', 'status', 'created_at')->orderBy(request('sortBy'), request('order'));
            else if(request('sortBy') == 'name' || request('sortBy') == 'postal_code')
                $group_users->select('id', 'user_id', 'group_id', 'admin', 'status', 'created_at', \DB::raw('(select ' . request('sortBy') . ' from groups where groups.id = group_users.group_id) as '. request('sortBy')))->orderBy(request('sortBy'), request('order'));
        }
        
        return $group_users->paginate(request('pageLength'));
    }

    public function getUsers(){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['authenticated' => false], 422);
        }
        
        $user = JWTAuth::parseToken()->authenticate();
        
        $group_users = \App\GroupUser::whereNotNull('id');
        
        if(request()->has('group_id'))
            $group_users->where('group_id', request('group_id'));
        
        $group_users = $group_users->get();
        if ($group_users) {
            $user_ids = array();
            foreach($group_users as $group_user) {
                array_push($user_ids, $group_user->user_id);
            }
            
            if(request()->has('group_id')) {
                $users = \App\User::with('profile')->whereIn('id', $user_ids)->select('id', 'email');
            } else {
                $users = \App\User::with('profile', 'groups')->whereIn('id', $user_ids)->select('id', 'email');
            }

            if(request()->has('email'))
                if (request('email'))
                    $users->where('email','like', '%'. request('email') . '%');

            if(request()->has('full_name'))
                if (request('full_name'))
                    $users->whereHas('profile',function($q) {
                        $q->where('full_name','like', '%'. request('full_name') . '%');
                    });
        
            if(request()->has('street_address'))
                if (request('street_address'))
                    $users->whereHas('profile',function($q) {
                        $q->where('street_address','like', '%'. request('full_name') . '%');
                    });

            if(request()->has('postal_code'))
                if (request('postal_code'))
                    $users->whereHas('profile',function($q) {
                        $q->where('postal_code','like', '%'. request('postal_code') . '%');
                    });
            
        }
        
        $user_result = $users->paginate(request('pageLength'));
        if(request()->has('group_id')) {
            if ($user_result) {
                foreach ($user_result as $user) {
                    $group_user = \App\GroupUser::where('user_id', $user->id)->where('group_id', request('group_id'))->first();
                    $user['group'] = array('admin'=> $group_user->admin, 'status'=> $group_user->status, 'created_at'=>  $group_user->created_at->format('Y-m-d H:i:s'));
                }
            }
        }
        return $user_result;
    }

    public function activeUser(Request $request, $user_id, $usergroup_id){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['authenticated' => false], 422);
        }

        $user = JWTAuth::parseToken()->authenticate();
        if (!$user->backend)
            return response()->json(['status' => 'fail', 'message' => 'You do not have the backend permission!', 'data' => null, 'error_type' => 'no_permission'], 422);
        
        $group_user = \App\GroupUser::find($usergroup_id);
        if (!$group_user)
            return response()->json(['status' => 'fail', 'message' => 'The user is not a group member!', 'data' => null, 'error_type' => 'no_member'], 422);

        $group = \App\Group::find($group_user->group_id);
        if (!$group)
            return response()->json(['status' => 'fail', 'message' => 'Could not find this group!', 'error_type' => 'no_group'], 422);
        
        if ($group_user->status != 'activated') {
            $notification_names = array();
            $notification_names['swedish'] = 'Din kontostatus har ändrats.';
            $notification_names['else'] = 'Your account status has changed.';
            $params_data = array(
                'notification_id' => 0,
                'group_id' => $group->id,
                'group_name' => $group->name,
                'user_id' => 0,
                'status' => 'active'
            );
            $this->sendPushNotificationHttpRequest($user_id, $notification_names, $params_data);
        }

        
        $group_user->status = 'activated';
        $group_user->save();

        return response()->json(['status' => 'success', 'message' => 'Active User`s Group Member Successfully!', 'data' => compact('profile', 'email')], 200);
    }

    public function deactiveUser(Request $request, $user_id, $usergroup_id){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['authenticated' => false], 422);
        }

        $user = JWTAuth::parseToken()->authenticate();
        if (!$user->backend)
            return response()->json(['status' => 'fail', 'message' => 'You do not have the backend permission!', 'data' => null, 'error_type' => 'no_permission'], 422);
        
        $group_user = \App\GroupUser::find($usergroup_id);
        if (!$group_user)
            return response()->json(['status' => 'fail', 'message' => 'The user is not a group member!', 'data' => null, 'error_type' => 'no_member'], 422);

        $group = \App\Group::find($group_user->group_id);
        if (!$group)
            return response()->json(['status' => 'fail', 'message' => 'Could not find this group!', 'error_type' => 'no_group'], 422);

        if ($group_user->status != 'pending') {
            $notification_names = array();
            $notification_names['swedish'] = 'Din kontostatus har ändrats.';
            $notification_names['else'] = 'Your account status has changed.';
            $params_data = array(
                'notification_id' => 0,
                'group_id' => $group->id,
                'group_name' => $group->name,
                'user_id' => 0,
                'status' => 'deactive'
            );
            $this->sendPushNotificationHttpRequest($user_id, $notification_names, $params_data);
        }

        $group_user->status = 'pending';
        $group_user->save();

        return response()->json(['status' => 'success', 'message' => 'Deactive User`s Group Member Successfully!', 'data' => compact('profile', 'email')], 200);
    }

    public function makeManger(Request $request, $user_id, $usergroup_id){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['authenticated' => false], 422);
        }

        $user = JWTAuth::parseToken()->authenticate();
        if (!$user->backend)
            return response()->json(['status' => 'fail', 'message' => 'You do not have the backend permission!', 'data' => null, 'error_type' => 'no_permission'], 422);
        
        $group_user = \App\GroupUser::find($usergroup_id);
        if (!$group_user)
            return response()->json(['status' => 'fail', 'message' => 'The user is not a group member!', 'data' => null, 'error_type' => 'no_member'], 422);

        $group = \App\Group::find($group_user->group_id);
        if (!$group)
            return response()->json(['status' => 'fail', 'message' => 'Could not find this group!', 'error_type' => 'no_group'], 422);

        if ($group_user->admin != 1) {
            $notification_names = array();
            $notification_names['swedish'] = 'Din kontostatus har ändrats.';
            $notification_names['else'] = 'Your account status has changed.';
            $params_data = array(
                'notification_id' => 0,
                'group_id' => $group->id,
                'group_name' => $group->name,
                'user_id' => 0,
                'status' => 'admin'
            );
            $this->sendPushNotificationHttpRequest($user_id, $notification_names, $params_data);
        }

        $group_user->admin = 1;
        $group_user->save();

        return response()->json(['status' => 'success', 'message' => 'Make User as Group Manager Successfully!', 'data' => compact('profile', 'email')], 200);
    }

    public function makeUser(Request $request, $user_id, $usergroup_id){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['authenticated' => false], 422);
        }

        $user = JWTAuth::parseToken()->authenticate();
        if (!$user->backend)
            return response()->json(['status' => 'fail', 'message' => 'You do not have the backend permission!', 'data' => null, 'error_type' => 'no_permission'], 422);
        
        $group_user = \App\GroupUser::find($usergroup_id);
        if (!$group_user)
            return response()->json(['status' => 'fail', 'message' => 'The user is not a group member!', 'data' => null, 'error_type' => 'no_member'], 422);

        $group = \App\Group::find($group_user->group_id);
        if (!$group)
            return response()->json(['status' => 'fail', 'message' => 'Could not find this group!', 'error_type' => 'no_group'], 422);
        
        if ($group_user->admin != 0) {
            $notification_names = array();
            $notification_names['swedish'] = 'Din kontostatus har ändrats.';
            $notification_names['else'] = 'Your account status has changed.';
            $params_data = array(
                'notification_id' => 0,
                'group_id' => $group->id,
                'group_name' => $group->name,
                'user_id' => 0,
                'status' => 'user'
            );
            $this->sendPushNotificationHttpRequest($user_id, $notification_names, $params_data);
        }

        $group_user->admin = 0;
        $group_user->save();

        return response()->json(['status' => 'success', 'message' => 'Disable User as Group Manager Successfully!', 'data' => compact('profile', 'email')], 200);
    }
    
    public function deleteUser(Request $request, $user_id, $usergroup_id){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['authenticated' => false], 422);
        }

        $user = JWTAuth::parseToken()->authenticate();
        if (!$user->backend)
            return response()->json(['status' => 'fail', 'message' => 'You do not have the backend permission!', 'data' => null, 'error_type' => 'no_permission'], 422);
        
        $group_user = \App\GroupUser::find($usergroup_id);
        if (!$group_user)
            return response()->json(['status' => 'fail', 'message' => 'The user is not a group member!', 'data' => null, 'error_type' => 'no_member'], 422);
          
        $group = \App\Group::find($group_user->group_id);
        if (!$group)
            return response()->json(['status' => 'fail', 'message' => 'Could not find this group!', 'error_type' => 'no_group'], 422);
        
        $group_id = $group->id;
        $notifications = \App\Notification::where('group_id', $group_id)->where('user_id', $user_id)->get();
        if($notifications) {
            foreach ($notifications as $notification) {
                $notification_unreads = \App\NotificationUnread::where('notification_id', $notification->id)->get();
                if ($notification_unreads) {
                    foreach ($notification_unreads as $notification_unread) {
                        $notification_unread->delete();
                    }
                }
                $images = $notification->Images;
                if ($images) {
                    foreach ($images as $image) {
                        $image->delete();
                    }
                }
                $comments = $notification->Comments;
                if ($comments) {
                    foreach ($comments as $comment) {
                        $comment_unreads = \App\CommentUnread::where('comment_id', $comment->id)->get();
                        if ($comment_unreads) {
                            foreach ($comment_unreads as $comment_unread) {
                                $comment_unread->delete();
                            }
                        }
                        $images = $comment->Images;
                        if ($images) {
                            foreach ($images as $image) {
                                $image->delete();
                            }
                        }
                        $comment->delete();
                    }
                }
                $notification->delete();
            }
        }
        $notifications = \App\Notification::where('group_id', $group_id)->where('user_id', '!=', $user_id)->get();
        if($notifications) {
            foreach ($notifications as $notification) {
                $notification_unreads = \App\NotificationUnread::where('notification_id', $notification->id)->where('user_id', $user_id)->get();
                if ($notification_unreads) {
                    foreach ($notification_unreads as $notification_unread) {
                        $notification_unread->delete();
                    }
                }
                $comments = $notification->Comments;
                if ($comments) {
                    foreach ($comments as $comment) {
                        $comment_unreads = \App\CommentUnread::where('comment_id', $comment->id)->where('user_id', $user_id)->get();
                        if ($comment_unreads) {
                            foreach ($comment_unreads as $comment_unread) {
                                $comment_unread->delete();
                            }
                        }
                    }
                }
            }
        }
        $group_user->delete();

        $notification_names = array();
        $notification_names['swedish'] = 'Du har blivit borttagen från gruppen ' . $group->name;
        $notification_names['else'] = 'You have been removed from the group ' . $group->name;
        $params_data = array(
            'notification_id' => 0,
            'group_id' => $group->id,
            'group_name' => $group->name,
            'user_id' => $user_id,
            'status' => 'delete'
        );
        $this->sendPushNotificationHttpRequest($user_id, $notification_names, $params_data);

        return response()->json(['status' => 'success', 'message' => 'Delete User as Group Manager Successfully!', 'data' => compact('profile', 'email')], 200);
    }
}