<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use LaravelEmojiOne;

date_default_timezone_set("Europe/Stockholm");

class NotificationController extends Controller
{
    protected $images_path = 'images/notifications/';
    protected $stamp_image_path = 'images/common/stamp.png';
    
    protected $image_extensions = array('jpeg', 'png', 'jpg', 'gif', 'bmp');
    
    protected $app_page_rows = 10;
    
    public function index(){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
        $notifications = \App\Notification::with('user.simple_profile', 'type', 'group', 'images');
        
        if(request()->has('group_name'))
            if (request('group_name'))
                $notifications->whereHas('group', function($q) {
                    $q->where('name', 'like', '%' . strtolower(request('group_name')) . '%');
                });
        
        $notifications->where('type', '!=', 5);

        if(request()->has('type'))
            if (request('type'))
                $notifications->whereHas('type', function($q) {
                    $q->where('name', 'like', '%' . strtolower(request('type')) . '%');
                });
        
        if(request()->has('full_name'))
            $notifications->whereHas('user.simple_profile', function($q) {
                $q->where('full_name', 'like', '%' . strtolower(request('full_name')) . '%');
            });

        if(request()->has('email'))
            $notifications->whereHas('user', function($q) {
                $q->where('email', 'like', '%' . strtolower(request('email')) . '%');
            });
        
        if(request()->has('contents'))
            $notifications->where('contents', 'like', '%'. request('contents').'%');
            
        if(request()->has('status'))
            $notifications->whereStatus(request('status'));
            
        if(request()->has('sortBy') && request()->has('order')) {
            if(request('sortBy') == 'contents' || request('sortBy') == 'created_at' || request('sortBy') == 'updated_at' || request('sortBy') == 'group_id')
                $notifications->select('id', 'contents', 'type', 'group_id', 'user_id', 'status', 'created_at', 'updated_at')->orderBy(request('sortBy'), request('order'));
            else if(request('sortBy') == 'type')
                $notifications->select('id', 'contents', 'type', 'group_id', 'user_id', 'status', 'created_at', 'updated_at', \DB::raw('(select ' . request('sortBy') . ' from notification_type where notifications.type = notification_type.id) as '. request('sortBy')))->orderBy(request('sortBy'), request('order'));
            else if(request('sortBy') == 'email')
                $notifications->select('id', 'contents', 'type', 'group_id', 'user_id', 'status', 'created_at', 'updated_at', \DB::raw('(select ' . request('sortBy') . ' from users where notifications.user_id = users.id) as '. request('sortBy')))->orderBy(request('sortBy'), request('order'));
        }
        
        $notifications_result = $notifications->paginate(request('pageLength'));
        if ($notifications_result) {
            foreach ($notifications_result as $notification) {
                $notification['contents'] = LaravelEmojiOne::toImage($notification['contents']);
            }
        }

        return $notifications_result;
    }
    
    private function stampImage($filename_x, $filename_result, $mergeType = 0){
        if ( !file_exists( $filename_x ) || !file_exists( $this->stamp_image_path ) ) {
            return false;
        }
        /// Get dimensions for specified images
        try {
            list($width_x, $height_x, $image_x_type) = getimagesize($filename_x);
        } catch (Exception $e) {
            return false;
        }
        list($width_y, $height_y, $image_y_type) = getimagesize($this->stamp_image_path);
        
        //$image_x_type = image_type_to_extension($filename_x);
        if ($image_x_type == 2) {
            $image_x = imagecreatefromjpeg($filename_x);
        } else if ($image_x_type == 3) {
            $image_x = imagecreatefrompng($filename_x); 
        } else if ($image_x_type == 1) {
            $image_x = imagecreatefromgif($filename_x);
        }
        
        //$image_y_type = image_type_to_extension($this->stamp_image_path);
        if ($image_y_type == 2) {
            $image_y = imagecreatefromjpeg($this->stamp_image_path);
        } else if ($image_y_type == 3) {
            $image_y = imagecreatefrompng($this->stamp_image_path);
        } else if ($image_y_type == 1) {
            $image_y = imagecreatefromgif($this->stamp_image_path);
        }
        
        $max_x = ($width_x > $height_x) ? $width_x : $height_x;
        if ( $max_x > 512 ) {
            $new_width_x = $width_x * 512 / $max_x;
            $new_height_x = $height_x * 512 / $max_x;
        }
        $image = imagecreatetruecolor($new_width_x, $new_height_x);
        
        $min_x = ($new_width_x < $new_height_x) ? $new_width_x : $new_height_x;
        $ratio = $min_x / 512 * (60 / $width_y);
        $offset_x = 60 * $ratio;
        $offset_y = (60 + $height_y) * $ratio;
        
        $new_width = $width_y * $ratio;
        $new_height = $height_y * $ratio;
        $image_tmp = imagecreate($new_width, $new_height);
        imagecopyresampled($image_tmp, $image_y, 0, 0, 0, 0, $new_width, $new_height, $width_y, $height_y);
        imagealphablending($image_tmp, true);
        imagesavealpha($image_tmp, true);
        
        $image_xx = imagecreatetruecolor($new_width_x, $new_height_x);
        imagecopyresampled($image_xx, $image_x, 0, 0, 0, 0, $new_width_x, $new_height_x, $width_x, $height_x);
        
        imagecopy($image, $image_xx, 0, 0, 0, 0, $new_width_x, $new_height_x);
        imagecopy($image, $image_tmp, $offset_x, $new_height_x - $offset_y, 0, 0, $new_width, $new_height);
        
        $lowerFileName = strtolower($filename_result); 
        if (substr_count($lowerFileName, '.jpg') > 0 || substr_count($lowerFileName, '.jpeg') > 0) {
            imagejpeg($image, $filename_result, 100);
        } else if (substr_count($lowerFileName, '.png') > 0) {
            imagepng($image, $filename_result, 100);
        } else if( substr_count($lowerFileName, '.gif') > 0) {
            imagegif($image, $filename_result, 100); 
        }
        
        // Clean up
        imagedestroy($image);
        imagedestroy($image_tmp);
        imagedestroy($image_x);
        imagedestroy($image_y);
        
        return true;
    }

    private function sendPushNotificationHttpRequest($user_ids, $notification_id, $notification_type){
        if (!$user_ids) return null;
        
        $notification_names = array();
        $notification_names['swedish'] = \App\NotificationType::where('id', '=', $notification_type)->pluck('trans_name')[0];
        $notification_names['else'] = \App\NotificationType::where('id', '=', $notification_type)->pluck('name')[0];
        
        $requests = $responses = [];
        if ( is_array( $user_ids ) ) {
            foreach( $user_ids as $user_id ) {
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
                            'data' => array('notification_id' => $notification_id, 'group_id' => 0, 'group_name' => '', 'user_id' => 0, 'status' => 'notification')
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
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type: application/json", "Authorization: Basic " . SZ_PUSHNOTI_AUTH));
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
            }
        }
        return array('request' => $requests, 'response' => $responses);
    }

    public function getNotification(Request $request, $id){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
        $user = JWTAuth::parseToken()->authenticate();
            
        $notification = \App\Notification::find($id);
        if(!$notification)
            return response()->json(['status' => 'fail', 'message' => 'Could not find the notification!', 'error_type' => 'no_notification'], 422);
            
        $notification = \App\Notification::with('user.profile', 'images',  'group', 'type');
        $notification->where('id', '=', $id);
        
        $result = $notification->get();
        if (count($result)) {
            $notification_result = $result[0];
        } else {
            $notification_result = null;
        }
        
        return response()->json(['status' => 'success', 'message' => 'Get Notification Detail Data Successfully!', 'notification' => $notification_result], 200);
    }
    
    public function updateNotification(Request $request){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
        $user = JWTAuth::parseToken()->authenticate();
        $validation = Validator::make($request->all(), [
            'notification_id' => 'required',
            'contents' => 'required',
        ]);
        if($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first(), 'error_type' => 'no_fill'], 422);
            
        if($request->hasfile('images')) {
            foreach($request->file('images') as $image)
            {
                $extension = $image->getClientOriginalExtension();
                if (!in_array(strtolower($extension), $this->image_extensions)) {
                    return response()->json(['status' => 'fail', 'message' => 'Your images must be jpeg, png, jpg, gif, bmp!', 'error_type' => 'image_type_error'], 422);
                }
            }
        }
        
        $notification = \App\Notification::find(request('notification_id'));
        if(!$notification)
            return response()->json(['status' => 'fail', 'message' => 'Could not find the notification.', 'error_type' => 'no_notification'], 422);
            
        if(!request()->has('country')) {
            return response()->json(['status' => 'fail', 'message' => 'Could not find the notification.', 'error_type' => 'no_notification'], 422);
        }
        
        $images = $notification->Images;
        if ($images) {
            foreach ($images as $image) {
                $image->delete();
            }
        }
        if (request('country')) {
            $notification->country = request('country');
        } else {
            $notification->country = '';
        }
        if (request('group_id') != '') {
            $notification->group_id = request('group_id');
        } else {
            $notification->group_id = 0;
        }
        $notification->contents = request('contents');
        $notification->save();
        
        if($request->hasfile('images')) {
            if (is_array($request->file('images'))) {
                if (count($request->file('images'))) {
                    foreach($request->file('images') as $image)
                    {
                        $extension = $image->getClientOriginalExtension();
                        $mt = explode(' ', microtime());
                        $name = ((int)$mt[1]) * 1000000 + ((int)round($mt[0] * 1000000));
                        $file_name = $name . '.' . $extension;
                        $image_flag = true;
                        if( $this->stamp_image_path && \File::exists($this->stamp_image_path) ) {
                            $file_tmp_name = $name . 'tmp.' . $extension;
                            $file = $image->move($this->images_path, $file_tmp_name);
                            if ( ( $image_flag = $this->stampImage($this->images_path . $file_tmp_name, $this->images_path . $file_name) ) ) {
                                \File::delete($this->images_path . $file_tmp_name);
                            }
                        } else {
                            $file = $image->move($this->images_path, $file_name);
                        }
                        
                        if ( $image_flag ) {
                            list($width, $height) = getimagesize($this->images_path . $file_name);
                            $notificaion_image = new \App\Image;
                            $notificaion_image->type = 'notification';
                            $notificaion_image->width = $width;
                            $notificaion_image->height = $height;
                            $notificaion_image->parent_id = $notification->id;
                            $notificaion_image->url = $file_name;
                            $notificaion_image->save();
                        }
                    }
                }
            }
        }
        return response()->json(['status' => 'success', 'message' => 'Update Notification Data Successfully!'], 200);
    }

    public function toggleStatus(Request $request){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
        $user = JWTAuth::parseToken()->authenticate();
        if (!$user->backend)
            return response()->json(['status' => 'fail', 'message' => 'You must be an administrator.', 'error_type' => 'no_permissioin'], 422);
            
        $notification = \App\Notification::find(request('id'));
        if(!$notification)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find notificaioin!', 'error_type' => 'no_notification'], 422);
            
        $notification->status = !$notification->status;
        $notification->save();
        
        return response()->json(['status' => 'success', 'message' => 'Notification updated!'], 200);
    }
    
    public function deleteNotification(Request $request, $id){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
        $user = JWTAuth::parseToken()->authenticate();
        if (!$user->backend)
            return response()->json(['status' => 'fail', 'message' => 'You must be an administrator.', 'error_type' => 'no_permissioin'], 422);
            
        $notification = \App\Notification::find($id);
        if(!$notification)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find notificaioin!', 'error_type' => 'no_notification'], 422);
        
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
        
        return response()->json(['status' => 'success', 'message' => 'Notification deleted!'], 200);
    }
    
    // System Notification
    public function indexSys(){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
        $notifications = \App\Notification::with('user', 'type', 'images')->where('type', '=', 5);
        
        if(request()->has('country'))
            $notifications->where('country', '=', request('country'));
            
        if(request()->has('group_id')) {
            $groups = \App\Group::where('group_id', 'like', '%'. request('group_id').'%')->get();
            $group_ids = array();
            if ($groups) {
                foreach($groups as $group) {
                    array_push($group_ids, $group->id);
                }
            }
            $notifications->whereIn('group_id', $group_ids);
        }
            
        if(request()->has('full_name'))
            $notifications->whereHas('user.simple_profile', function($q) {
                $q->where('full_name', 'like', '%' . strtolower(request('full_name')) . '%');
            });

        if(request()->has('email'))
            $notifications->whereHas('user', function($q) {
                $q->where('email', 'like', '%' . strtolower(request('email')) . '%');
            });
            
        if(request()->has('contents'))
            $notifications->where('contents', 'like', '%'. request('contents').'%');
            
        if(request()->has('status'))
            $notifications->whereStatus(request('status'));
            
        if(request()->has('sortBy') && request()->has('order')) {
            if(request('sortBy') == 'contents' || request('sortBy') == 'created_at' || request('sortBy') == 'updated_at' || request('sortBy') == 'country')
                $notifications->select('id', 'contents', 'type', 'group_id', 'user_id', 'status', 'country', 'created_at', 'updated_at')->orderBy(request('sortBy'), request('order'));
            else if(request('sortBy') == 'email')
                $notifications->select('id', 'contents', 'type', 'group_id', 'user_id', 'status', 'country', 'created_at', 'updated_at', \DB::raw('(select ' . request('sortBy') . ' from users where notifications.user_id = users.id) as '. request('sortBy')))->orderBy(request('sortBy'), request('order'));
        }
        
        return $notifications->paginate(request('pageLength'));
    }

    public function createSysNotification(Request $request){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }

        $user = JWTAuth::parseToken()->authenticate();
        if (!$user->backend) {
            return response()->json(['status' => 'fail', 'message' => 'You must be an administrator!', 'error_type' => 'no_permission'], 422);
        }
        
        $validation = Validator::make($request->all(), [
            'country' => '',
            'group_id' => '',
            'contents' => 'required|min:1',
            'datetime' => 'date_format:"Y-m-d H:i:s"|required',
        ]);        
        if($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first(), 'error_type' => 'no_fill'], 422);
            
        if($request->hasfile('images')) {
            foreach($request->file('images') as $image)
            {
                $extension = $image->getClientOriginalExtension();
                if (!in_array(strtolower($extension), $this->image_extensions)) {
                    return response()->json(['status' => 'fail', 'message' => 'Your images must be jpeg, png, jpg, gif, bmp!', 'error_type' => 'image_type_error'], 422);
                }
            }
        }
        
        $country = "";
        if (request('country') != '')
            $country = request('country');
        
        $group_id = 0;
        if (request('group_id') != '')
            $group_id = request('group_id');

        $notification = new \App\Notification;
        $notification->type = 5;
        $notification->contents = request('contents');
        $notification->user_id = $user->id;
        $notification->country = $country;
        $notification->group_id = $group_id;
        $notification->status = 1;
        $notification->created_at = request('datetime');
        $notification->save();
        
        if($request->hasfile('images')) {
            if (is_array($request->file('images'))) {
                if (count($request->file('images'))) {
                    foreach($request->file('images') as $image)
                    {
                        $extension = $image->getClientOriginalExtension();
                        $mt = explode(' ', microtime());
                        $name = ((int)$mt[1]) * 1000000 + ((int)round($mt[0] * 1000000));
                        $file_name = $name . '.' . $extension;
                        $image_flag = true;
                        if( $this->stamp_image_path && \File::exists($this->stamp_image_path) ) {
                            $file_tmp_name = $name . 'tmp.' . $extension;
                            $file = $image->move($this->images_path, $file_tmp_name);
                            if ( ( $image_flag = $this->stampImage($this->images_path . $file_tmp_name, $this->images_path . $file_name) ) ) {
                                \File::delete($this->images_path . $file_tmp_name);
                            }
                        } else {
                            $file = $image->move($this->images_path, $file_name);
                        }
                        
                        if ( $image_flag ) {
                            list($width, $height) = getimagesize($this->images_path . $file_name);
                            $notificaion_image = new \App\Image;
                            $notificaion_image->type = 'notification';
                            $notificaion_image->width = $width;
                            $notificaion_image->height = $height;
                            $notificaion_image->parent_id = $notification->id;
                            $notificaion_image->url = $file_name;
                            $notificaion_image->save();
                        }
                    }
                }
            }
        }
        
        // Signed Users
        $users = \App\User::with('profile', 'groups')->where('backend', 0)->where('status', '=', 'activated');
        if ($country)
            $users->whereHas('profile',function($q) use($country) {
                $q->where('country', $country);
            });
        if ($group_id)
            $users->whereHas('groups',function($q) use($group_id) {
                $q->where('group_id', $group_id);
            });
        $users->whereHas('profile',function($q) {
            $q->whereNotNull('push_token')->where('push_token', '<>', '');
        });
        $users->where(function($q) {
            $q->whereNull('deactivated_at')->orWhere('deactivated_at', '')->orWhereRaw('users.activated_at > users.deactivated_at');
        });
        $push_users = $users->pluck('id')->toArray();
        
        // Unread Notification
        if ($push_users) {
            foreach ($push_users as $push_user) {
                $notification_unread = new \App\NotificationUnread;
                $notification_unread->user_id = $push_user;
                $notification_unread->notification_id = $notification->id;
                $notification_unread->save();
            }
        }
        $push_result = $this->sendPushNotificationHttpRequest($push_users, $notification->id, 5);
        
        return response()->json(['status' => 'success', 'message' => 'System Notification has created succesfully!', 'notification_id' => $notification->id], 200); // , 'notification_name' => $notification_name, 'push_result' => $push_result
    }
    
    // Comments
    public function indexComments(){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }

        $notification = null;
        if(request()->has('id'))
            $notification = \App\Notification::find(request('id'));
            
        if(!$notification)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find notificaioin!', 'error_type' => 'no_notification'], 422);
        
        $comments = \App\Comment::with('images', 'user.simple_profile')->where('notification_id', '=', request('id'));
        
        if(request()->has('full_name'))
            $comments->whereHas('user.simple_profile', function($q) {
                $q->where('full_name', 'like', '%' . strtolower(request('full_name')) . '%');
            });
        
        if(request()->has('email'))
            $comments->whereHas('user', function($q) {
                $q->where('email', 'like', '%' . strtolower(request('email')) . '%');
            });
            
        if(request()->has('contents'))
            $comments->where('contents', 'like', '%'. request('contents').'%');
            
        if(request()->has('status'))
            $comments->whereStatus(request('status'));
            
        if(request()->has('sortBy') && request()->has('order')) {
            if(request('sortBy') == 'contents' || request('sortBy') == 'created_at' || request('sortBy') == 'updated_at')
                $comments->select('id', 'contents', 'notification_id', 'user_id', 'status', 'created_at', 'updated_at')->orderBy(request('sortBy'), request('order'));
            else if(request('sortBy') == 'email')
                $comments->select('id', 'contents', 'notification_id', 'user_id', 'status', 'created_at', 'updated_at', \DB::raw('(select ' . request('sortBy') . ' from users where comments.user_id = users.id) as '. request('sortBy')))->orderBy(request('sortBy'), request('order'));
        }
        
        return $comments->paginate(request('pageLength'));
    }

    public function toggleCommentStatus(Request $request){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
        $user = JWTAuth::parseToken()->authenticate();
        if (!$user->backend)
            return response()->json(['status' => 'fail', 'message' => 'You must be an administrator.', 'error_type' => 'no_permissioin'], 422);
            
        $comment = \App\Comment::find(request('id'));
        if(!$comment)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find comment!', 'error_type' => 'no_comment'], 422);
            
        $comment->status = !$comment->status;
        $comment->save();
        
        return response()->json(['status' => 'success', 'message' => 'Comment updated!'], 200);
    }
    
    public function deleteComment(Request $request, $id){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
        $user = JWTAuth::parseToken()->authenticate();
        if (!$user->backend)
            return response()->json(['status' => 'fail', 'message' => 'You must be an administrator.', 'error_type' => 'no_permissioin'], 422);
        
        $comment = \App\Comment::find($id);
        if(!$comment)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find comment!', 'error_type' => 'no_comment'], 422);
            
        $user = JWTAuth::parseToken()->authenticate();
        
        $notification = \App\Notification::where('id', $comment->notification_id)->get();
        if($notification)
            $notification->save();
        
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
        
        return response()->json(['status' => 'success', 'message' => 'Comment deleted!'], 200);
    }
    
    // Notification Type
    public function indexType(){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
        $user = JWTAuth::parseToken()->authenticate();
        if (!$user->backend)
            return response()->json(['status' => 'fail', 'message' => 'You must be an administrator.', 'error_type' => 'no_permissioin'], 422);
            
        $notification_type = \App\NotificationType::whereNotNull('id');
        
        if(request()->has('name'))
            $notification_type->where('name', 'like', '%'.request('name').'%');
            
        if(request()->has('trans_name'))
                $notification_type->where('trans_name', 'like', '%'.request('trans_name').'%');
        
        if(request()->has('status'))
            $notification_type->whereStatus(request('status'));
        
        if(request()->has('sortBy') && request()->has('order')) {
            if(request('sortBy') == 'name' || request('sortBy') == 'created_at')
                $notification_type->orderBy(request('sortBy'), request('order'));
        }
        
        return $notification_type->paginate(request('pageLength'));
    }

    public function allType(){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
        $user = JWTAuth::parseToken()->authenticate();
        if (!$user->backend)
            return response()->json(['status' => 'fail', 'message' => 'You must be an administrator.', 'error_type' => 'no_permissioin'], 422);
            
        $notification_type = \App\NotificationType::whereNotNull('id');
        
        $notification_type->whereStatus(1);
        $notification_type->orderBy('name', 'ASC');
        $notification_type = $notification_type->pluck('name');

        return response()->json(['status' => 'success', 'message' => 'Get Notification Type Data Successfully!', 'types' => $notification_type], 200);
    }

    public function allCommonType(){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
        $user = JWTAuth::parseToken()->authenticate();
        if (!$user->backend)
            return response()->json(['status' => 'fail', 'message' => 'You must be an administrator.', 'error_type' => 'no_permissioin'], 422);
            
        $notification_type = \App\NotificationType::whereNotNull('id');
        
        $notification_type->whereStatus(1);
        $notification_type->where('id', '!=', 5);
        $notification_type = $notification_type->pluck('name');

        return response()->json(['status' => 'success', 'message' => 'Get Notification Type Data Successfully!', 'types' => $notification_type], 200);
    }

    public function storeType(Request $request){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
        $validation = Validator::make($request->all(), [
            'name' => 'required|unique:notification_type',
        ]);
        if($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first()], 422);
        
        $user = JWTAuth::parseToken()->authenticate();
        if (!$user->backend)
            return response()->json(['status' => 'fail', 'message' => 'You must be an administrator.', 'error_type' => 'no_permissioin'], 422);
            
        $notification_type = new \App\NotificationType;
        $notification_type->fill(request()->all());
        $notification_type->name = request('name');
        if(request()->has('trans_name'))
            $notification_type->trans_name = request('trans_name');
        else
            $notification_type->trans_name = "";            
        $notification_type->status = 1;
        $notification_type->save();
        
        return response()->json(['status' => 'success', 'message' => 'Notification Type added!', 'data' => $notification_type], 200);
    }

    public function destroyType(Request $request, $id){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
        $notification_type = \App\NotificationType::find($id);
        if(!$notification_type)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find notification type!', 'error_type' => 'no_type'], 422);
            
        $user = JWTAuth::parseToken()->authenticate();
        if (!$user->backend)
            return response()->json(['status' => 'fail', 'message' => 'You must be an administrator.', 'error_type' => 'no_permissioin'], 422);
            
        $notification_type->delete();
        
        return response()->json(['status' => 'success', 'message' => 'Notification Type deleted!'], 200);
    }

    public function showType($idx){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
        $notification_type = \App\NotificationType::whereIdx($idx)->first();
        if(!$notification_type)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find notification type!', 'error_type' => 'no_type'], 422);
            
        $user = JWTAuth::parseToken()->authenticate();
        if (!$user->backend)
            return response()->json(['status' => 'fail', 'message' => 'You must be an administrator.', 'error_type' => 'no_permissioin'], 422);
            
        return $notification_type;
    }

    public function updateType(Request $request, $id){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
        $notification_type = \App\NotificationType::whereId($id)->first();
        if(!$notification_type)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find notification type!', 'error_type' => 'no_type'], 422);
            
        $validation = Validator::make($request->all(), [
            'name' => 'required|unique:notification_type,name,'.$notification_type->id.',id',
        ]);
        if($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first()], 422);
            
        $user = JWTAuth::parseToken()->authenticate();
        if (!$user->backend)
            return response()->json(['status' => 'fail', 'message' => 'You must be an administrator.', 'error_type' => 'no_permissioin'], 422);
            
        $notification_type->name = request('name');
        if(request()->has('trans_name'))
            $notification_type->trans_name = request('trans_name');
        $notification_type->save();
        return response()->json(['status' => 'success', 'message' => 'Notification Type updated!', 'data' => $notification_type], 200);
    }

    public function toggleTypeStatus(Request $request){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
        $notification_type = \App\NotificationType::find(request('id'));
        if(!$notification_type)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find notification type!', 'error_type' => 'no_type'], 422);
            
        $user = JWTAuth::parseToken()->authenticate();
        if (!$user->backend)
            return response()->json(['status' => 'fail', 'message' => 'You must be an administrator.', 'error_type' => 'no_permissioin'], 422);
            
        $notification_type->status = !$notification_type->status;
        $notification_type->save();
        
        return response()->json(['status' => 'success', 'message' => 'Notification Type updated!'], 200);
    }    
}