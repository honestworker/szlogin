<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

date_default_timezone_set("Europe/Stockholm");

class GroupController extends Controller
{
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
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        $groups = \App\Group::whereNotNull('id');
        
        if(request()->has('name')) {
            $groups->where('name', 'like', '%' . request('name') . '%');
        }

        if(request()->has('country')) {
            $groups->where('country', 'like', '%' . request('country') . '%');
        }

        if(request()->has('postal_code')) {
            $groups->where('postal_code', 'like', '%' . request('postal_code') . '%');
        }
        
        if(request()->has('status'))
            $groups->whereStatus(request('status'));

        if (request()->has('sortBy') && request()->has('order') )
            $groups->orderBy(request('sortBy'), request('order'));
        
        
        $groups_result = $groups->paginate(request('pageLength'));
        if ($groups_result) {
            foreach ($groups_result as $group) {
                $all_member_count = count(\App\GroupUser::where('group_id', $group->id)->get());
                $all_manager_active_count = count(\App\GroupUser::where('group_id', $group->id)->where('admin', 1)->where('status', 'activated')->get());
                $all_manager_pending_count = count(\App\GroupUser::where('group_id', $group->id)->where('admin', 1)->where('status', 'pending')->get());
                $all_user_active_count = count(\App\GroupUser::where('group_id', $group->id)->where('admin', 0)->where('status', 'activated')->get());
                $all_user_pending_count = count(\App\GroupUser::where('group_id', $group->id)->where('admin', 0)->where('status', 'pending')->get());
                $group['members'] = array('all'=> $all_member_count, 'manager_active'=> $all_manager_active_count, 'manager_deactive'=> $all_manager_pending_count, 'user_active'=> $all_user_active_count, 'user_deactive'=> $all_user_pending_count);
            }
        }
        return $groups_result;
    }

    public function all(){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        $groups = \App\Group::whereNotNull('id');
        
        $groups->whereStatus(1);
        $groups->orderBy('name', 'ASC');
        
        return response()->json(['status' => 'success', 'message' => 'Get Group Data Successfully!', 'groups' => $groups->get()], 200);
    }
        
    public function getByCountry($country){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
        $groups = \App\Group::whereNotNull('id');
        if ($country && $country != 'All')
            $groups->where('country', $country);
        
        return response()->json(['status' => 'success', 'message' => 'Get Group Data successfully!', 'groups' => $groups->get()]);
    }

    public function toggleStatus(Request $request){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        $group = \App\Group::find($request->input('id'));
        if(!$group)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find group!'], 422);
            
        $group->status = !$group->status;
        $group->save();
        
        return response()->json(['status' => 'success', 'message' => 'Group updated!']);
    }    

    public function show($id){
        $group = \App\Group::whereId($id)->first();
        
        if(!$group)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find group!'], 422);
            
        return $group;
    }

    public function store(Request $request){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
            
        $validation = Validator::make($request->all(), [
            'country' => 'required',
            'name' => 'required',
            'postal_code' => 'required',
        ]);
        
        if($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first()], 422);
            
        $group = new \App\Group;
        $country->fill(request()->all());
        $group->save();
        
        return response()->json(['status' => 'success', 'message' => 'Group created!', 'data' => $group], 200);
    }

    public function update(Request $request, $id){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        $group = \App\Group::whereId($id)->first();
        if(!$group)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find group!']);
            
        $validation = Validator::make($request->all(), [
            'country' => 'required',
            'name' => 'required',
            'postal_code' => 'required',
        ]);
        
        if($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first()], 422);
        
        $group->country = request('country');
        $group->name = request('name');
        $group->postal_code = request('postal_code');
        $group->save();
        
        return response()->json(['status' => 'success', 'message' => 'Group updated!', 'data' => $group], 200);
    }
    
    public function destroy(Request $request, $id){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        $group = \App\Group::find($id);
        if(!$group)
            return response()->json(['message' => 'Couldnot find group!'], 422);
        
        $notifications = \App\Notification::where('group_id', $group->id)->get();
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
        $group_users = \App\GroupUser::where('group_id', $id)->get();
        foreach ($group_users as $group_user) {
            $notifications = \App\Notification::where('group_id', $group->id)->get();
            if($notifications) {
                foreach ($notifications as $notification) {
                    $notification_unreads = \App\NotificationUnread::where('notification_id', $notification->id)->where('user_id', $group_user->user_id)->get();
                    if ($notification_unreads) {
                        foreach ($notification_unreads as $notification_unread) {
                            $notification_unread->delete();
                        }
                    }
                    $comments = $notification->Comments;
                    if ($comments) {
                        foreach ($comments as $comment) {
                            $comment_unreads = \App\CommentUnread::where('comment_id', $comment->id)->where('user_id', $group_user->user_id)->get();
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
                'user_id' => $group_user->user_id,
                'status' => 'delete'
            );

            $this->sendPushNotificationHttpRequest($group_user->user_id, $notification_names, $params_data);

            $group_user->delete();
        }

        $group->delete();

        return response()->json(['status' => 'success', 'message' => 'Group deleted!'], 200);
    }
        
    public function overview(Request $request){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        $total = \App\Group::count();
        
        $infor = array();
        
        $now_date = date("Y-m-d");
        $year = date('Y', strtotime($now_date));
        $month = date('m', strtotime($now_date));
        for ($month_index = 1; $month_index <= $month; $month_index++) {
            $groups = \App\Group::whereNotNull('id');
            $infor[] = count($groups->whereYear('created_at', '=',  $year)->whereMonth('created_at', '=',   $month_index )->get());
        }
        
        return response()->json(['status' => 'success', 'message' => 'Group overview!', 'data' => compact('total', 'infor', 'year')]);
    }
}