<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

date_default_timezone_set("Europe/Stockholm");

class NotificationController extends Controller
{
    protected $images_path = 'images/notifications/';
    protected $stamp_image_path = 'images/common/stamp.png';
    
    protected $image_extensions = array('jpeg', 'png', 'jpg');
    
	public function index() {
		$notifications = \App\Notification::with('user', 'group');
		
		if(request()->has('type'))
			$notifications->where('type', '=', request('type'));
			
        if(request()->has('contents'))
            $notifications->where('contents', 'like', '%'.request('contents').'%');
            
        if(request()->has('first_name')) {
            $notifications->whereHas('user', function($q) {
                $q->where('first_name', 'like', '%'.request('first_name').'%');
            });
        }
        
        if(request()->has('family_name')) {
            $notifications->whereHas('user', function($q) {
                $q->where('family_name', 'like', '%'.request('family_name').'%');
            });
        }
        
        if(request()->has('status'))
            $notifications->whereStatus(request('status'));
        
        if(request()->has('sortBy') && request()->has('order')) {
            if(request('sortBy') == 'status' || request('sortBy') == 'created_at')
                $notifications->orderBy(request('sortBy'), request('order'));
            else if(request('sortBy') == 'first_name' || request('sortBy') == 'family_name' || request('sortBy') == 'phone_number')
                $notifications->with(['user' => function ($q) {
                    $q->orderBy(request('sortBy'), request('order'));
                }]);
        }
        
		return $notifications->paginate(request('pageLength'));
	}
    
    private function stampImage($filename_x, $filename_result, $mergeType = 0) {
        /// Get dimensions for specified images
        list($width_x, $height_x, $image_x_type) = getimagesize($filename_x);
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
        
        $image = imagecreatetruecolor($width_x, $height_x);
        imagealphablending($image, true);
        imagesavealpha($image, true);

        $min_x = ($width_x > $height_x) ? $width_x : $height_x;
        $ratio = $min_x / 1024 * (120 / $width_y);
        $offset_x = 120 * $ratio;
        $offset_y = (120 + $height_y) * $ratio;
        
        $new_width = $width_y * $ratio;
        $new_height = $height_y * $ratio;
        $image_tmp = imagecreate($new_width, $new_height);
        imagealphablending($image_tmp, true);
        imagesavealpha($image_tmp, true);
        imagecopyresampled($image_tmp, $image_y, 0, 0, 0, 0, $new_width, $new_height, $width_y, $height_y);
        
        imagecopy($image, $image_x, 0, 0, 0, 0, $width_x, $height_x);
        imagecopy($image, $image_tmp, $offset_x, $height_x - $offset_y, 0, 0, $new_width, $new_height);
        
        $lowerFileName = strtolower($filename_result); 
        if (substr_count($lowerFileName, '.jpg') > 0 || substr_count($lowerFileName, '.jpeg') > 0) {
            imagejpeg($image, $filename_result);
        } else if (substr_count($lowerFileName, '.png') > 0) {
            imagepng($image, $filename_result);
        } else if( substr_count($lowerFileName, '.gif') > 0) {
            imagegif($image, $filename_result); 
        }
        
        // Clean up
        imagedestroy($image);
        imagedestroy($image_tmp);
        imagedestroy($image_x);
        imagedestroy($image_y);
    }

    private function sendPushNotificationHttpRequest($user_ids, $notification_id, $notification_name) {
        if (!$user_ids) return null;
        
        $url = 'https://exp.host/--/api/v2/push/send';
        $params = null;
        if (count($user_ids) == 1) {
            $user = \App\User::find($user_ids[0]);
            if ($user->push_token) {
                $params = array(
                    'to' => $user->push_token,
                    'title' => 'Safe Zone',
                    'sound' => 'default',
                    'body' => $notification_name,
                    'data' => array('notification_id' => $notification_id)
                );
            }
        } else {
            foreach($user_ids as $user_id) {
                $user = \App\User::find($user_id);
                if ($user->push_token) {
                    $user_params = array(
                        'to' => $user->push_token,
                        'title' => 'Safe Zone',
                        'sound' => 'default',
                        'body' => $notification_name,
                        'data' => array('notification_id' => $notification_id)
                    );
                    if ($params) {
                        array_push($params, $user_params);
                    } else {
                        $params = array($user_params);
                    }
                }
            }
        }
        
        if (count($user_ids)) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept: application/json", "Accept-Encoding: gzip, deflate", "Content-type: application/json"));
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);
            curl_setopt($ch, CURLOPT_TIMEOUT, 120);
            
            $result = curl_exec($ch);
            if(curl_errno($ch) !== 0) {
                error_log('cURL error when connecting to ' . $url . ': ' . curl_error($ch));
            }
            
            curl_close($ch);
            //print_r($result);
        }
        //print_r($params);
    }

	// Notification
    public function createNotification(Request $request){
        $user = JWTAuth::parseToken()->authenticate();
        $profile = $user->Profile;
        
        $validation = Validator::make($request->all(), [
            'type' => 'required',
            'contents' => 'required|min:1',
            'datetime' => 'required',
        ]);
        
        if($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first(), 'error_type' => 'no_fill'], 422);
            
        $group = \App\Group::find($profile->group_id);
        if(!$group)
            return response()->json(['status' => 'fail', 'message' => 'You must be any group memeber!', 'error_type' => 'no_member'], 422);
        
        if($request->hasfile('images')) {
            foreach($request->file('images') as $image)
            {
                $extension = $image->getClientOriginalExtension();
                if (!in_array($extension, $this->image_extensions)) {
                    return response()->json(['status' => 'fail', 'message' => 'Your images must be jpeg, png, jpg!', 'error_type' => 'image_type_error'], 422);
                }
            }
        }
        
        $notification_name = \App\NotificationType::where('id', '=', request('type'))->pluck('name');
        if (!$notification_name) {
            return response()->json(['status' => 'fail', 'message' => 'Your images must be jpeg, png, jpg!', 'error_type' => 'type_error'], 422);
        }
        
        $notification = new \App\Notification;
        $notification->type = request('type');
        $notification->contents = request('contents');
        $notification->user_id = $user->id;
        $notification->group_id = $group->id;
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
                        
                        if($this->stamp_image_path && \File::exists($this->stamp_image_path)) {
                            $file_tmp_name = $name . 'tmp.' . $extension;
                            $file = $image->move($this->images_path, $file_tmp_name);
                            $this->stampImage($this->images_path . $file_tmp_name, $this->images_path . $file_name);
                            \File::delete($this->images_path . $file_tmp_name);
                        } else {
                            $file = $image->move($this->images_path, $file_name);
                        }
                        
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
        
        $group_id = $group->id;
        // Signed Users
        $users = \App\User::with('profile');
        $users->whereHas('profile', function($q) use ($group_id) {
            $q->where('group_id', $group_id);
        });
        $users->whereNotNull('push_token')->where('push_token', '<>', '')->where('status', '=', 'activated')->where(function($q) {
            $q->whereIn('deactivated_at', [null, ''])
              ->orWhere('activated_at', '>', 'deactivated_at');
        })->where('id', '!=', $user->id);
        $group_users = $users->pluck('id')->toArray();
        
        $users = \App\User::with('groups');
        $users->whereHas('groups', function($q) use ($group_id) {
            $q->where('group_id', $group_id);
        });
        $users->where('status', '=', 'activated')->where(function($q) {
            $q->whereIn('deactivated_at', [null, ''])
              ->orWhere('activated_at', '>', 'deactivated_at');
        })->where('id', '!=', $user->id);
        $attached_users = $users->pluck('id')->toArray();
        
        $diff_users = array_diff($attached_users, $group_users);
        $push_users = array_merge($group_users, $diff_users);
        
        $this->sendPushNotificationHttpRequest($push_users, $notification->id, $notification_name[0]);
        
        // Signed Out Users
        $users = \App\User::with('profile');
        $users->whereHas('profile', function($q) use ($group_id) {
            $q->where('group_id', $group_id);
        });
        $users->where('status', '=', 'activated')->where('activated_at', '<=', 'deactivated_at')->where('id', '!=', $user->id);
        $group_users = $users->pluck('id')->toArray();
        
        $users = \App\User::with('groups');
        $users->whereHas('groups', function($q) use ($group_id) {
            $q->where('group_id', $group_id);
        });
        $users->where('status', '=', 'activated')->where('activated_at', '<=', 'deactivated_at')->where('id', '!=', $user->id);
        $attached_users = $users->pluck('id')->toArray();
        
        $diff_users = array_diff($attached_users, $group_users);
        $alarm_users = array_merge($group_users, $diff_users);
        
        $users = \App\User::whereIn('id', $alarm_users)->get();
        foreach($users as $user) {
            if ($user->alarms) {
                $user->alarms = $user->alarms . ',' . $notification->id;
            } else {
                $user->alarms = $notification->id;
            }
            $user->save();
        }
        
        return response()->json(['status' => 'success', 'message' => 'Notification has created succesfully!', 'notification_id' => $notification->id], 200); // , 'notification_name' => $notification_name
    }
    
    public function getAlarms() {
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['authenticated' => false], 422);
        }

        $user = JWTAuth::parseToken()->authenticate();
        $user_alarms = $user->alarms;
        $user->alarms = '';
        $user->save();

        $notifications = \App\Notification::whereIn('id', explode(',', $user_alarms))->pluck('id')->toArray();
        
        return response()->json(['status' => 'success', 'message' => 'Get Notification Alarms succesfully!', 'notifications' => $notifications], 200);
    }

    public function getNotification(Request $request) {
        $user = JWTAuth::parseToken()->authenticate();
        $profile = $user->Profile;
            
        $group = \App\Group::find($profile->group_id);
        if(!$group)
            return response()->json(['status' => 'fail', 'message' => 'You must be any group memeber!', 'error_type' => 'no_member'], 422);
        
        $user_groups = array_merge(array($profile->group_id), \App\UserGroups::where('user_id', '=', $user->id)->select('group_id')->get()->toArray());

        $notification = \App\Notification::with('user.profile');
        $notification->whereStatus(1);
        $notification->whereIn('group_id', $user_groups);
        $notification->orderBy('created_at', 'DESC');
        
        return response()->json(['status' => 'success', 'message' => 'Get Notification Data Successfully!', 'notifications' => $notification->get()], 200);
    }

    public function getNotificationDetail(Request $request) {
        $user = JWTAuth::parseToken()->authenticate();
        $profile = $user->Profile;
        
        $validation = Validator::make($request->all(), [
            'notification_id' => 'required',
        ]);
        if($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first(), 'error_type' => 'no_fill'], 422);
        
        $group = \App\Group::find($profile->group_id);
        if(!$group)
            return response()->json(['status' => 'fail', 'message' => 'You must be any group memeber!', 'error_type' => 'no_member'], 422);
        
        $notification = \App\Notification::with('user.profile', 'images', 'comments.images', 'comments.user.profile');
        $notification->where('id', '=', request('notification_id'));
        
        return response()->json(['status' => 'success', 'message' => 'Get Notification Detail Data Successfully!', 'notification' => $notification->get(), 'my_avatar' => $profile->avatar], 200);
    }

    public function updateNotification(Request $request) {
        $user = JWTAuth::parseToken()->authenticate();
        $profile = $user->Profile;
        
        $validation = Validator::make($request->all(), [
            'notification_id' => 'required',
            'contents' => 'required',
        ]);
        if($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first(), 'error_type' => 'no_fill'], 422);
                
        $notification = \App\Notification::find(request('notification_id'));
        if(!$notification)
            return response()->json(['status' => 'fail', 'message' => 'Could not find the notification.', 'error_type' => 'no_notification'], 422);
        $notification->contents = request('contents');
        $notification->save();
        
        return response()->json(['status' => 'success', 'message' => 'Update Notification Data Successfully!'], 200);
    }

    public function toggleStatus(Request $request){
        $notification = \App\Notification::find(request('id'));
        
        if(!$notification)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find notificaioin!'], 422);
            
        $notification->status = !$notification->status;
        $notification->save();
        
        return response()->json(['status' => 'success', 'message' => 'Notification updated!'], 200);
    }
    
    public function deleteNotification(Request $request){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
        $validation = Validator::make($request->all(),[
            'notification_id' => 'required',
        ]);
        if ($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first(), 'error_type' => 'no_fill'], 422);

        $notification = \App\Notification::find(request('notification_id'));
        if(!$notification)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find notificaioin!'], 422);
            
        $images = $notification->Images;
        if ($images) {
            foreach ($images as $image) {
                $image->delete();
            }
        }
        $comments = $notification->comments;
        if ($comments) {
            foreach ($comments as $comment) {
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
    
    public function deleteNotificationImage(Request $request){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
          
        $validation = Validator::make($request->all(),[
            'notification_id' => 'required',
            'url' => 'required',
        ]);
        if ($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first(), 'error_type' => 'no_fill'], 422);

        $notification = \App\Notification::find(request('notification_id'));
        if(!$notification)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find notificaioin!'], 422);
            
        $images = $notification->Images;
        if ($images) {
            foreach ($images as $image) {
                if ($image->url == request('url')) {
                    $image->delete();
                }
            }
        }
        
        return response()->json(['status' => 'success', 'message' => 'Notification Image deleted!'], 200);
    }

	// Comments
    public function createComment(Request $request){
        $user = JWTAuth::parseToken()->authenticate();
        $profile = $user->Profile;
        
        $validation = Validator::make($request->all(), [
            'notification_id' => 'required',
            'contents' => 'required',
            'datetime' => 'required',
        ]);        
        if($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first(), 'error_type' => 'no_fill'], 422);
            
        $group = \App\Group::find($profile->group_id);
        if(!$group)
            return response()->json(['status' => 'fail', 'message' => 'You must be any group memeber!', 'error_type' => 'no_member'], 422);
        
        if(request('notification_id') == 0) {
            return response()->json(['status' => 'fail', 'message' => 'You must specify the notificaion!', 'error_type' => 'no_notification'], 422);
        }
        
        $notification = \App\Notification::find(request('notification_id'));
        if(!$notification)
            return response()->json(['status' => 'fail', 'message' => 'You specify the empty notification!', 'error_type' => 'find_notification'], 422);
            
        
        if($request->hasfile('images')) {
            foreach($request->file('images') as $image)
            {
                $extension = $image->getClientOriginalExtension();
                if (!in_array($extension, $this->image_extensions)) {
                    return response()->json(['status' => 'fail', 'message' => 'Your images must be jpeg, png, jpg!', 'error_type' => 'image_type_error'], 422);
                }
            }
        }
        
        $comment = new \App\Comment;
        $comment->notification_id = $notification->id;
        $comment->contents = request('contents');
        $comment->user_id = $user->id;
        $comment->status = 1;
        $comment->created_at = request('datetime');
        $comment->save();
        
        if($request->hasfile('images')) {
            foreach($request->file('images') as $image)
            {
                $extension = $image->getClientOriginalExtension();
                $mt = explode(' ', microtime());
                $name = ((int)$mt[1]) * 1000000 + ((int)round($mt[0] * 1000000));
                $file_name = $name . '.' . $extension;
                
                if($this->stamp_image_path && \File::exists($this->stamp_image_path)) {
                    $file_tmp_name = $name . 'tmp.' . $extension;
                    $file = $image->move($this->images_path, $file_tmp_name);
                    $this->stampImage($this->images_path . $file_tmp_name, $this->images_path . $file_name);
                    \File::delete($this->images_path . $file_tmp_name);
                } else {
                    $file = $image->move($this->images_path, $file_name);
                }
                
                list($width, $height) = getimagesize($this->images_path . $file_name);
                $comment_image = new \App\Image;
                $comment_image->type = 'comment';
                $comment_image->width = $width;
                $comment_image->height = $height;
                $comment_image->parent_id = $comment->id;
                $comment_image->url = $file_name;
                $comment_image->save();
            }
        }
        
        $notification->save();
        
        return response()->json(['status' => 'success', 'message' => 'Comment has created succesfully!', 'comment_id' => $comment->id], 200);
    }

    public function toggleCommentStatus(Request $request){
        $comment = \App\Comment::find(request('id'));
        
        if(!$comment)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find comment!'], 422);
            
        $comment->status = !$comment->status;
        $comment->save();
        
        return response()->json(['status' => 'success', 'message' => 'Comment updated!'], 200);
    }
    
    public function deleteComment(Request $request){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
          
        $validation = Validator::make($request->all(),[
            'comment_id' => 'required',
        ]);
        if ($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first(), 'error_type' => 'no_fill'], 422);
            
        $comment = \App\Comment::find(request('comment_id'));
        if(!$comment)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find comment!'], 422);
            
        $images = $comment->Images;
        if ($images) {
            foreach ($images as $image) {
                $image->delete();
            }
        }
        $comment->delete();
        
        return response()->json(['status' => 'success', 'message' => 'Comment deleted!'], 200);
    }

    public function deleteCommentImage(Request $request){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
          
        $validation = Validator::make($request->all(),[
            'comment_id' => 'required',
            'url' => 'required',
        ]);
        if ($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first(), 'error_type' => 'no_fill'], 422);

        $comment = \App\Comment::find(request('comment_id'));
        if(!$comment)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find comment!'], 422);
            
        $images = $comment->Images;
        if ($images) {
            foreach ($images as $image) {
                if ($image->url == request('url')) {
                    $image->delete();
                }
            }
        }
        
        return response()->json(['status' => 'success', 'message' => 'Comment Image deleted!'], 200);
    }

    // Notification Type
	public function indexType(){
		$notification_type = \App\NotificationType::whereNotNull('id');
		
		if(request()->has('name'))
			$notification_type->where('name', 'like', '%'.request('name').'%');
		
        if(request()->has('status'))
            $notification_type->whereStatus(request('status'));
        
        if(request()->has('sortBy') && request()->has('order')) {
            if(request('sortBy') == 'name' || request('sortBy') == 'created_at')
                $notification_type->orderBy(request('sortBy'), request('order'));
        }
        
		return $notification_type->paginate(request('pageLength'));
	}

	public function allType(){
		$notification_type = \App\NotificationType::whereNotNull('id');
		
        $notification_type->whereStatus(1);
        $notification_type->orderBy('name', 'ASC');
        $countries = $notification_type->pluck('name');
        
		return response()->json(['status' => 'success', 'message' => 'Get Notification Type Data Successfully!', 'types' => $countries], 200);
	}

    public function storeType(Request $request){
        
        $validation = Validator::make($request->all(), [
            'name' => 'required|unique:notification_type',
        ]);
        
        if($validation->fails())
        	return response()->json(['status' => 'fail', 'message' => $validation->messages()->first()], 422);
        
        $notification_type = new \App\NotificationType;
        $notification_type->fill(request()->all());
        $notification_type->name = request('name');
        $notification_type->status = 1;
        $notification_type->save();
        
        return response()->json(['status' => 'success', 'message' => 'Notification Type added!', 'data' => $notification_type], 200);
    }

    public function destroyType(Request $request, $id){
        $notification_type = \App\NotificationType::find($id);
        
        if(!$notification_type)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find notification type!'], 422);
            
        $notification_type->delete();
        
        return response()->json(['status' => 'success', 'message' => 'Notification Type deleted!'], 200);
    }

    public function showType($idx){
        $notification_type = \App\NotificationType::whereIdx($idx)->first();
        
        if(!$notification_type)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find notification type!'], 422);
            
        return $notification_type;
    }

    public function updateType(Request $request, $id){
        
        $notification_type = \App\NotificationType::whereId($id)->first();
        
        if(!$notification_type)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find notification type!'], 422);
            
        $validation = Validator::make($request->all(), [
            'name' => 'required|unique:notification_type,name,'.$notification_type->id.',id',
        ]);
        
        if($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first()], 422);
            
        $notification_type->name = request('name');
        $notification_type->save();
        return response()->json(['status' => 'success', 'message' => 'Notification Type updated!', 'data' => $notification_type], 200);
    }

    public function toggleTypeStatus(Request $request){
        $notification_type = \App\NotificationType::find(request('id'));
        
        if(!$notification_type)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find notification type!'], 422);
            
        $notification_type->status = !$notification_type->status;
        $notification_type->save();
        
        return response()->json(['status' => 'success', 'message' => 'Notification Type updated!'], 200);
    }
    
}
