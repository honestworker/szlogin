<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Validator;

use App\Notifications\Activation;
use App\Notifications\Activated;
use App\Notifications\Assign;
use App\Notifications\Administrator;

use App\Notifications\PasswordReset;
use App\Notifications\PasswordResetted;

date_default_timezone_set("Europe/Stockholm");

class ApiController extends Controller
{
    protected $avatar_path = 'images/users/';
    protected $noti_images_path = 'images/notifications/';
    protected $images_base_path = 'images/advertisements/base.png';
    protected $stamp_image_path = 'images/common/stamp.png';
    protected $image_extensions = array('jpeg', 'png', 'jpg', 'gif', 'bmp');
    protected $app_page_rows = 10;

    private function sendPushNotificationHttpRequest($user_id, $notification_names, $params_data = array(), $urgent = 1){
        $result = array(
            'request' => null,
            'response' => null,
        );
        
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
                    if ($urgent) {
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
                    } else {
                        $params['android_channel_id'] = SZ_PUSHNOTI_ANDROID_CHANNEL_6;
                    }
                } else if ( $profile->os_type == 'ios' ) {
                    if ($urgent) {
                        if ( $profile->vibration == 0 && $profile->sound == 'no_sound' ) {
                            $params['ios_sound'] = "nil";
                        } else {
                            $params['ios_sound'] = $profile->sound . ".wav";
                        }
                    } else {
                        $params['ios_sound'] = "nil";
                    }
                }
                $result['request'] = $params;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, SZ_PUSHNOTI_URL);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type: application/json", "Authorization: Basic " . SZ_PUSHNOTI_AUTH));
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);
                curl_setopt($ch, CURLOPT_TIMEOUT, 120);
                
                $result['response'] = curl_exec($ch);
                if(curl_errno($ch) !== 0) {
                    error_log('cURL error when connecting to ' . SZ_PUSHNOTI_URL . ': ' . curl_error($ch));
                }
                
                curl_close($ch);
            }
        }
        return $result;
    }

    // Country    
    public function allCountry(){
        $country = \App\Country::whereNotNull('id');
        $country->whereStatus(1);
        $country->orderBy('name', 'ASC');
        $countries = $country->pluck('name')->toArray();
        
        $result = array();
        if (in_array('Sweden', $countries) || in_array('sweden', $countries)) {
            $result[] = 'Sweden';
            foreach ($countries as $country) {
                if (strtolower($country) != 'sweden') {
                    $result[] = $country; 
                }
            }
        } else {
            $result = $countries;
        }
        
        return response()->json(['status' => 'success', 'message' => 'Get Country Data Successfully!', 'countries' => $result], 200);
    }
    
    // Auth
    private function generateUuid($count){
        $result_uuid6 = '';
        for ($no = 0; $no < $count; $no++) {
            $result_uuid6 = $result_uuid6 . mt_rand(0, 9);
        }
        return $result_uuid6;
    }

    private function calculateVisitor($user){
        $now_date = date("Y-m-d");
        $year = date('Y', strtotime($now_date));
        $month = date('m', strtotime($now_date));
        $created_year = date('Y', strtotime($user->activated_at));
        $created_month = date('m', strtotime($user->activated_at));
        if ($created_year != $year || $created_month != $month) {
            $visitor = \App\Visitor::where('year', '=', $year)->where('month', '=', $month)->first();
            if ($visitor) {
                $visitor->value = $visitor->value + 1;
                $visitor->save();
            } else {
                $visitor = \App\Visitor::create([
                    'year' => $year,
                    'month' => $month,
                    'value' => 1,
                ]);
            }
        }
        $user->activated_at = date('Y-m-d H:i:s');
        $user->save();
    }

    public function signup(Request $request){
        $validation = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6',
            'full_name' => 'required',
            'street_address' => 'required',
            'postal_code' => 'required',
            'country' => 'required'
            // 'password_confirmation' => 'required|same:password'
        ]);
        
        if($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first(), 'error_type' => 'no_fill'], 422);
        
        $email = strtolower(trim(request('email')));
        $user = \App\User::whereEmail($email)->where('backend', '=', 0)->first();
        if ($user) {
            return response()->json(['status' => 'fail', 'message' => 'Your email already have registed! Please try with other email again.', 'error_type' => 'email_exist'], 422);
        }
        
        $user = \App\User::create([
            'email' => $email,
            'status' => 'activated',
            'password' => bcrypt(request('password'))
        ]);
        
        $profile = new \App\Profile;
        $profile->full_name = request('full_name');
        $profile->street_address = request('street_address');
        $profile->postal_code = request('postal_code');
        $profile->country = request('country');
        
        if(request()->file('avatar')) {
            $extension = $request->file('avatar')->getClientOriginalExtension();
            $filename = time('Ymdhis');
            $file = $request->file('avatar')->move($this->avatar_path, $filename . "." . $extension);
            $img = \Image::make($this->avatar_path.$filename . "." . $extension);
            $img->save($this->avatar_path.$filename . "." . $extension);
            $profile->avatar = $filename . "." . $extension;
        }
        $user->profile()->save($profile);
        
        return response()->json(['status' => 'success', 'message' => 'You have signed up successfully.']);
    }

    public function login(Request $request){
        $credentials = $request->only('email', 'password');
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['status' => 'fail', 'message' => 'Email or Password is incorrect! Please try again.', 'error_type' => 'incorrect'], 422);
            }
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'message' => 'This is something wrong. Please try again!', 'error_type' => 'token_error'], 500);
        }

        $email = strtolower(trim(request('email')));
        $user = \App\User::with('profile')->whereEmail($email)->where('backend', '=', 0)->first();
        if (!$user)
            return response()->json(['status' => 'fail', 'message' => 'You have not registed. Please sign up and try to login again.', 'error_type' => 'no_user'], 422);
        if($user->status == 'pending')
            return response()->json(['status' => 'fail', 'message' => 'Your account hasn\'t been activated. Please check your email & activate account.', 'error_type' => 'no_groupid'], 422);
        if($user->status == 'banned')
            return response()->json(['status' => 'fail', 'message' => 'Your account is banned. Please contact system administrator.', 'error_type' => 'banned'], 422);
        if($user->status != 'activated' && $user->status != 'pending_activated')
            return response()->json(['status' => 'fail', 'message' => 'There is something wrong with your account. Please contact system administrator.', 'error_type' => 'no_signup'], 422);
        
        $this->calculateVisitor($user);
        
        $user = \App\User::with('profile')->whereEmail($email)->where('backend', '=', 0)->select('id', 'email')->first();
        
        $groups = \App\Group::where('country', $user->Profile->country)->where('status', 1)->select('id', 'name', 'postal_code', 'country')->get();
        foreach($groups as $group) {
            $group->own = 0;
            $group->own_status = 'pending';
            $group->admin = 0;
            $group_user = \App\GroupUser::where('user_id', $user->id)->where('group_id', $group->id)->first();
            if ($group_user) {
                $group->own = 1;
                if($group_user->admin) {
                    $group->admin = 1;
                }
                $group->own_status = $group_user->status;
            }

            $unreads = 0;
            $notifications = \App\Notification::where('group_id', $group->id)->where('status', 1)->get();
            foreach ($notifications as $notification) {
                $notification_unreads = \App\NotificationUnread::where('notification_id', $notification->id)->where('user_id', $user->id)->first();
                if ($notification_unreads)
                    $unreads = $unreads + 1;
                
                // $comment_ids = \App\Comment::where('notification_id', $notification->id)->pluck('id')->toArray();
                // $comment_unreads = \App\CommentUnread::whereIn('comment_id', $comment_ids)->where('user_id', $user->id)->get();
                // if ($comment_unreads)
                //     $unreads += count($comment_unreads);
            }
            
            $group->unreads = $unreads;
        }
        return response()->json(['status' => 'success', 'message' => 'You are successfully logged in!', 'token' => $token, 'user' => $user, 'groups' => $groups], 200);
    }

    public function logout(){
        try {
            $token = JWTAuth::getToken();
            
            if ($token) {
                $user = JWTAuth::parseToken()->authenticate();
                if ($user) {
                    $user->deactivated_at = date('Y-m-d H:i:s');
                    $profile = $user->Profile;
                    $profile->push_token = "";
                    $user->save();
                }
                JWTAuth::invalidate($token);
            }
            
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'message' => $e->getMessage(), 'error_type' => 'token_error'], 500);
        }
        
        return response()->json(['status' => 'success', 'message' => 'You are successfully logged out!'], 200);
    }

    public function forgotPassword(Request $request){
        $validation = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);
        if($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first(), 'error_type' => 'no_fill'], 422);
        
        $email = strtolower(trim(request('email')));
        $user = \App\User::whereEmail($email)->first();
        if(!$user)
            return response()->json(['status' => 'fail', 'message' => 'We couldn\'t found any user with this email.\n Please try again!', 'error_type' => 'no_user'], 422);
        if($user->status != 'activated')
            return response()->json(['status' => 'fail', 'message' => 'We couldn\'t found any user with this email.\n Please try again!', 'error_type' => 'no_activated'], 422);
        
        $code = $this->generateUuid(6);
        $password_reset = \DB::table('password_resets')->where('email', '=', $email)->first();
        if($password_reset) {
            \DB::table('password_resets')->where('email','=',$email)->update(array('code' => $code, 'created_at' => date("Y-m-d H:i:s")));
        } else {
            \DB::table('password_resets')->insert([
                'email' => $email,
                'code' => $code,
                'created_at' => date("Y-m-d H:i:s"),
            ]);
        }
        
        $user->notify(new PasswordReset($user, $code, $user->Profile->country, $user->Profile->full_name));
        
        return response()->json(['status' => 'success', 'message' => 'We have sent the verification code to your email.\n Please check your inbox!']);
    }

    public function validatePasswordReset(Request $request){
        $validation = Validator::make($request->all(), [
            'email' => 'required|email',
            'code' => 'required',
        ]);
        if($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first(), 'error_type' => 'no_fill'], 422);

        $email = strtolower(trim(request('email')));
        $validate_password_request = \DB::table('password_resets')->where('email','=', $email)->first();
        if(!$validate_password_request)
            return response()->json(['status' => 'fail', 'message' => 'Invalid email!', 'error_type' => 'no_user'], 422);
            
        $validate_password_request = \DB::table('password_resets')->where('email','=', $email)->where('code','=', request('code'))->first();
        if(!$validate_password_request)
            return response()->json(['status' => 'fail', 'message' => 'Invalid password reset code!', 'error_type' => 'invalid_code'], 422);
        
        if(date("Y-m-d H:i:s", strtotime($validate_password_request->created_at . "+30 minutes")) < date('Y-m-d H:i:s'))
            return response()->json(['status' => 'fail', 'message' => 'Password reset code is expired.\n Please request reset password again!', 'error_type' => 'code_expired'], 422);
            
        return response()->json(['status' => 'success', 'message' => 'Please reset the password!']);
    }

    public function resetPassword(Request $request){
        $validation = Validator::make($request->all(), [
            'email' => 'required|email',
            'code' => 'required',
            'password' => 'required|min:6',
        ]);
        if($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first(), 'error_type' => 'no_fill'], 422);
        
        $email = strtolower(trim(request('email')));
        $user = \App\User::whereEmail($email)->first();
        if(!$user)
            return response()->json(['status' => 'fail', 'message' => 'We couldn\'t found any user with this email. Please try again!', 'error_type' => 'no_user'], 422);
            
        $validate_password_request = \DB::table('password_resets')->where('email', '=', $email)->where('code', '=', request('code'))->first();
        if(!$validate_password_request)
            return response()->json(['status' => 'fail', 'message' => 'Invalid password reset code!', 'error_type' => 'invalid_code'], 422);
            
        if(date("Y-m-d H:i:s", strtotime($validate_password_request->created_at . "+30 minutes")) < date('Y-m-d H:i:s'))
            return response()->json(['status' => 'fail', 'message' => 'Password reset code is expired.\n Please request reset password again!', 'error_type' => 'code_expired'], 422);
            
        $user->password = bcrypt(request('password'));
        $user->save();
        
        $user->notify(new PasswordResetted($user, $user->Profile->country, $user->Profile->full_name));
        
        return response()->json(['status' => 'success', 'message' => 'Your password has been changed successfully!.\n Please login again!']);
    }

    // Unreads Of "Activity Logs" & "Group Members"
    public function unReadPending(Request $request) {
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
        $user = JWTAuth::parseToken()->authenticate();
        if (!$user)
            return response()->json(['status' => 'fail', 'message' => 'Your token is invaild!', 'error_type' => 'no_user']);
        
        $activity_logs = 0;
        $group_members = 0;

        $user_groups = \App\GroupUser::where('user_id', $user->id)->where('status', 'activated')->get();
        if ($user_groups) {
            foreach ($user_groups as $user_group) {
                $group_id = $user_group->group_id;
                $group = \App\Group::find($group_id);
                $group_country = $group->country;
                $notification_ids = \App\Notification::whereStatus(1);
                $notification_ids = $notification_ids->where(function($q) use($group_id, $group_country) {
                    $q->where('group_id', '=', $group_id)->orWhere(function($qq) use($group_id, $group_country) {
                        $qq->where('type', 5)->where(function($qqq) use($group_id, $group_country) {
                            $qqq->where(function($qqqq) {
                                $qqqq->where('group_id', 0)->where('country', '');
                            })->orWhere(function($qqqq) use($group_country) {
                                $qqqq->where('group_id', 0)->where('country', $group_country);
                            })->orWhere(function($qqqq) use($group_country)  {
                                $qqqq->where('group_id', request('id'))->where('country', $group_country);
                            });
                        });
                    });
                })->where('created_at', '>=', $user_group->created_at)->pluck('id')->toArray();
                
                $notifications = \App\Notification::whereIn('id', $notification_ids)->whereStatus(1)->get();
                if ($notifications) {
                    foreach ($notifications as $notification) {
                        $notification_unreads = \App\NotificationUnread::where('notification_id', $notification->id)->where('user_id', $user->id)->first();
                        if ($notification_unreads)
                            $activity_logs += 1;
                        
                        $comment_ids = \App\Comment::where('notification_id', $notification->id)->whereStatus(1)->pluck('id')->toArray();
                        $comment_unreads = \App\CommentUnread::whereIn('comment_id', $comment_ids)->where('user_id', $user->id)->get();
                        if ($comment_unreads)
                            $activity_logs += count($comment_unreads);
                    }
                }

                if ($user_group->admin) {
                    $group_pending_users = \App\GroupUser::where('group_id', $user_group->group_id)->where('status', 'pending')->get();
                    if ($group_pending_users) {
                        $group_members += count($group_pending_users);
                    }
                }
            }
        }

        return response()->json(['status' => 'success', 'message' => 'Get Unread Data successfully!', 'activity_logs' => $activity_logs, 'group_members' => $group_members], 200);
    }

    // Notification all admins of all pending groups
    public function nofifyJoinGroups(Request $request) {
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
        $user = JWTAuth::parseToken()->authenticate();
        if (!$user)
            return response()->json(['status' => 'fail', 'message' => 'Your token is invaild!', 'error_type' => 'no_user']);
        
        $sent_message = 0;
        $now_date = date("Y-m-d");
        $day_before = date("Y-m-d H:i:s", strtotime("$now_date  -1 day"));
        $notification_names = array();
        $user_groups = \App\GroupUser::where('user_id', $user->id)->where('status', 'pending')->where('updated_at', '<', $day_before)->get();
        if ($user_groups) {
            foreach ($user_groups as $user_group) {
                $group = \App\Group::find($user_group->group_id);
                if ($group) {
                    $notification_names['swedish'] = 'En ny användare ansökte om att gå med i gruppen "' . $group->name . '"';
                    $notification_names['else'] = 'A new user applied to join the group "' . $group->name . '"';
                    $sent_message = 1;
                    $group_admins = \App\GroupUser::where('group_id', $group->id)->where('admin', 1)->where('status', 'activated')->get();
                    if ($group_admins) {
                        foreach ($group_admins as $group_admin) {
                            $params_data = array(
                                'notification_id' => 0,
                                'group_id' => $group->id,
                                'group_name' => $group->name,
                                'user_id' => $user->id,
                                'status' => 'join'
                            );
                            $this->sendPushNotificationHttpRequest($group_admin->user_id, $notification_names, $params_data, 1);
                        }
                    }
                }
                $user_group->updated_at = date("Y-m-d H:i:s");
                $user_group->save();
            }
        }

        return response()->json(['status' => 'success', 'message' => 'Sent notification!', 'data' => $sent_message]);
    }
    
    // User
    public function changePassword(Request $request){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
        $validation = Validator::make($request->all(),[
            'password' => 'required|min:6',
        ]);
        if($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first(), 'error_type' => 'no_fill'], 422);
            
        $user = JWTAuth::parseToken()->authenticate();
        if (!$user)
            return response()->json(['status' => 'fail', 'message' => 'Your token is invaild!', 'error_type' => 'no_user']);
        
        $user->password = bcrypt(request('password'));
        $user->save();
        
        return response()->json(['status' => 'success', 'message' => 'Your password has been updated!'], 200);
    }

    public function profile(){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
        $user = JWTAuth::parseToken()->authenticate();
        if (!$user)
            return response()->json(['status' => 'fail', 'message' => 'Your token is invaild!', 'error_type' => 'token_error']);
        $profile = $user->Profile;
        if (!$profile)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find user profile!', 'data' => null, 'error_type' => 'no_profile'], 422);

        $user_avatar = "";
        if ($profile->avatar) {
            $user_avatar = url('/') . '/images/users/' . $profile->avatar;
        }
        return response()->json(['status' => 'success', 'full_name' => $profile->full_name, 'email' => $user->email, 'country' => $profile->country, 'postal_code' => $profile->postal_code, 'language' => $profile->language, 'avatar' => $user_avatar, 'avatar' => $user_avatar]);
    }

    public function updateAvatar(Request $request){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }

        $validation = Validator::make($request->all(), [
            'avatar' => 'required|image'
        ]);
        if ($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first(), 'error_type' => 'no_fill'], 422);
            
        $user = JWTAuth::parseToken()->authenticate();
        $profile = $user->Profile;
        if (!$profile)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find user profile!', 'data' => null, 'error_type' => 'no_profile'], 422);
        if($profile->avatar && \File::exists($this->avatar_path . $profile->avatar))
            \File::delete($this->avatar_path . $profile->avatar);
            
        $extension = $request->file('avatar')->getClientOriginalExtension();
        $filename = uniqid();
        $file = $request->file('avatar')->move($this->avatar_path, $filename . "." . $extension);
        $img = \Image::make($this->avatar_path . $filename . "." . $extension);
        $img->save($this->avatar_path . $filename . "." . $extension);
        $profile->avatar = $filename . "." . $extension;
        $profile->save();
        
        return response()->json(['status' => 'success', 'message' => 'Avatar updated!', 'profile' => $profile]);
    }

    public function savePushToken(Request $request){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
        $validation = Validator::make($request->all(),[
            'push_token' => 'required',
            'os_type' => 'required',
        ]);
        if ($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first(), 'error_type' => 'no_fill'], 422);
        
        $users_profile = \App\Profile::where('push_token', '=', request('push_token'))->get();
        if (count($users_profile)) {
            foreach ($users_profile as $profile) {
                $profile->push_token = '';
                $profile->save();
            }
        }
        
        $user = JWTAuth::parseToken()->authenticate();
        $profile = $user->Profile;
        if (!$profile)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find user profile!', 'data' => null, 'error_type' => 'no_profile'], 422);
        
        $profile->push_token = request('push_token');
        $profile->os_type = request('os_type');
        $profile->save();
        
        return response()->json(['status' => 'success', 'message' => 'Your push token is saved successfully.', 'user' => $user]);
    }

    public function setPushEffect(Request $request){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
        $validation = Validator::make($request->all(),[
            'sound' => 'required',
            'vibration' => 'required',
        ]);
        if ($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first(), 'error_type' => 'no_fill'], 422);
        
        $user = JWTAuth::parseToken()->authenticate();
        $profile = $user->Profile;
        if (!$profile)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find user profile!', 'data' => null, 'error_type' => 'no_profile'], 422);
        
        $profile->sound = request('sound');
        $profile->vibration = request('vibration');
        $profile->save();
        
        return response()->json(['status' => 'success', 'message' => 'Your push notification effect is saved successfully.', 'user' => $user]);
    }

    public function setLanguage(Request $request){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
        $validation = Validator::make($request->all(),[
            'language' => 'required',
        ]);
        if ($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first(), 'error_type' => 'no_fill'], 422);
        
        $user = JWTAuth::parseToken()->authenticate();
        $profile = $user->Profile;
        if (!$profile)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find user profile!', 'data' => null, 'error_type' => 'no_profile'], 422);
        
        $profile->language = request('language');
        $profile->save();
        
        return response()->json(['status' => 'success', 'message' => 'Your language is updated successfully.']);
    }

    public function destroyAccount(Request $request){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
        $user = JWTAuth::parseToken()->authenticate();
        
        if($user->avatar && \File::exists($this->avatar_path.$user->avatar))
            \File::delete($this->avatar_path.$user->avatar);
        
        $profile = $user->Profile;
        if ($profile)
            $profile->delete();
        
        $notifications = $user->Notification;
        if ($notifications) {
            foreach ($notifications as $notification) {
                $images = $notification->Images;
                if ($images) {
                    foreach ($images as $image) {
                        $image->delete();
                    }
                    $notification->delete();
                }
            }
        }
        
        $comments = $user->Comments;
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
        
        $user->delete();
        
        return response()->json(['status' => 'success', 'message' => 'The account has deleted successfully!'], 200);
    }
    
    public function deleteAccount(Request $request){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
          
        $validation = Validator::make($request->all(),[
            'group_id' => 'required',
            'user_id' => 'required',
        ]);
        if ($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first(), 'error_type' => 'no_fill'], 422);
            
        $user = JWTAuth::parseToken()->authenticate();
        if(!$user->Profile)
            return response()->json(['status' => 'fail', 'message' => 'Could not find user!', 'error_type' => 'no_profile'], 422);
            
        $own_group = \App\GroupUser::where('group_id', request('group_id'))->where('user_id', $user->id)->first();
        if (!$own_group)
            return response()->json(['status' => 'fail', 'message' => 'You are not this group member.', 'error_type' => 'no_member'], 422);
        if (!$own_group->admin)
            return response()->json(['status' => 'fail', 'message' => 'You do not have a manager permission.', 'error_type' => 'no_manager'], 422);
        if ($own_group->status == 'pending')
            return response()->json(['status' => 'fail', 'message' => 'You are pending on this group.', 'error_type' => 'pending'], 422);
        
        $group_users = \App\GroupUser::where('group_id', request('group_id'))->where('user_id', request('user_id'))->first();
        if (!$group_users) {
            return response()->json(['status' => 'fail', 'message' => 'The user is not this group member.', 'error_type' => 'no_user_member'], 422);
        }

        $del_user = \App\User::find(request('user_id'));
        if(!$del_user)
            return response()->json(['status' => 'fail', 'message' => 'Could not find user!', 'error_type' => 'no_user'], 422);
        
        $notifications = \App\Notification::where('user_id', $del_user->id)->where('group_id', $group_users->group_id)->get();
        if ($notifications) {
            foreach ($notifications as $notification) {
                // Delete Unread Notifications
                $notification_unreads = \App\NotificationUnread::where('notification_id', $notification->id)->get();
                if ($notification_unreads) {
                    foreach ($notification_unreads as $notification_unread) {
                        $notification_unread->delete();
                    }
                }
                // Delete Comments
                $comments = \App\Notification::where('user_id', $del_user->id)->where('notification_id', $notification->id)->get();
                if ($comments) {
                    foreach ($comments as $comment) {
                        // Delete Unread Comments
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
                // Delete Images
                $images = $notification->Images;
                if ($images) {
                    foreach ($images as $image) {
                        $image->delete();
                    }
                    $notification->delete();
                }
            }
        }
        
        // Delete Unread Notifications
        $notification_unreads = \App\NotificationUnread::where('user_id', $del_user->id)->get();
        if ($notification_unreads) {
            foreach ($notification_unreads as $notification_unread) {
                $notification_unread->delete();
            }
        }
        // Delete Unread Comments
        $comment_unreads = \App\CommentUnread::where('user_id', $del_user->id)->get();
        if ($comment_unreads) {
            foreach ($comment_unreads as $comment_unread) {
                $comment_unread->delete();
            }
        }

        return response()->json(['status' => 'success', 'message' => 'User deleted!'], 200);
    }

    // Group
    public function getGroupUsers(Request $request){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
        $validation = Validator::make($request->all(),[
            'id' => 'required',
        ]);
        if ($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first(), 'error_type' => 'no_fill'], 422);
        
        $user = JWTAuth::parseToken()->authenticate();

        $group = \App\Group::find(request('id'));
        if (!$group) {
            return response()->json(['status' => 'fail', 'message' => 'Could not find the your group.', 'error_type' => 'no_group'], 422);
        }
        
        $group_users = \App\GroupUser::with('user.profile')->select('user_id', 'admin', 'status');
        $group_users->where('group_id', request('id'));

        // $group_users_tmp = $group_users->where('user_id', '!=', $user->id);
        // $total_counts = count($group_users_tmp->get());
        
        // $page_end = false;
        // $result = [];
        // if($request->has('page')) {
        //     $page_id = request('page');
        //     if (($page_id + 1) * $this->app_page_rows >= $total_counts) {
        //         $page_end = true;
        //     }
        //     if ($page_id * $this->app_page_rows <= $total_counts) {
        //         $result = $group_users->offset($page_id * $this->app_page_rows)->limit($this->app_page_rows)->get();
        //     }
        // } else {
        //     $result = $group_users->get();
        // }
        //return response()->json(['status' => 'success', 'message' => 'Get Group User Data successfully!', 'users' => $result, 'end' => $page_end], 200);
        
        $group_admins = \App\GroupUser::with('user.profile')->select('user_id', 'admin', 'status')->where('group_id', request('id'))->where('admin', 1)->where('status', 'activated')->get();
        $group_pending_users = \App\GroupUser::with('user.profile')->select('user_id', 'admin', 'status')->where('group_id', request('id'))->where('status', 'pending')->get();
        $group_activated_users = \App\GroupUser::with('user.profile')->select('user_id', 'admin', 'status')->where('group_id', request('id'))->where('admin', 0)->where('status', 'activated')->get();
        $result = array(
            'admins' => $group_admins,
            'pending' => $group_pending_users,
            'activated' => $group_activated_users,
        );
        
        $own_group = \App\GroupUser::where('user_id', $user->id)->where('group_id', request('id'))->first();
        $own_info = array(
            'own' => 0,
            'status' => 'pending',
            'admin' => 0
        );
        if ($own_group) {
            $own_info['own'] = 1;
            $own_info['status'] = $own_group->status;
            $own_info['admin'] = $own_group->admin;
        }
        
        return response()->json(['status' => 'success', 'message' => 'Get Group User Data successfully!', 'users' => $result, 'own_info' => $own_info], 200);
    }

    public function storeGroup(Request $request){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }

        $validation = Validator::make($request->all(), [
            'name' => 'required|unique:groups',
            'postal_code' => 'required',
            'country' => 'required',
        ]);
        if($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first(), 'error_type' => 'no_fill'], 422);
            
        $user = \JWTAuth::parseToken()->authenticate();
        $group = new \App\Group;
        $group->fill(request()->all());
        $group->status = 1;
        $group->save();
        
        $group_user = new \App\GroupUser;
        $group_user->user_id = $user->id;
        $group_user->group_id = $group->id;
        $group_user->admin = 1;
        $group_user->status = 'activated';
        $group_user->save();
        
        return response()->json(['status' => 'success', 'message' => 'Group added!', 'data' => $group]);
    }

    /* Group User */
    public function allGroups(Request $request){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }

        $user = JWTAuth::parseToken()->authenticate();
        
        $groups = \App\Group::where('status', 1)->get();
        foreach($groups as $group) {
            $group->own = 0;
            $group->own_status = 'pending';
            $group->admin = 0;
            $group_user = \App\GroupUser::where('user_id', $user->id)->where('group_id', $group->id)->first();
            if ($group_user) {
                $group->own = 1;
                if($group_user->admin) {
                    $group->admin = 1;
                }
                $group->own_status = $group_user->status;
            }

            $unreads = 0;
            $notifications = \App\Notification::where('group_id', $group->id)->where('status', 1)->get();
            foreach ($notifications as $notification) {
                $notification_unreads = \App\NotificationUnread::where('notification_id', $notification->id)->where('user_id', $user->id)->first();
                if ($notification_unreads)
                    $unreads = $unreads + 1;
                
                $comment_ids = \App\Comment::where('notification_id', $notification->id)->pluck('id')->toArray();
                $comment_unreads = \App\CommentUnread::whereIn('comment_id', $comment_ids)->where('user_id', $user->id)->get();
                if ($comment_unreads)
                    $unreads += count($comment_unreads);
            }
            
            $group->unreads = $unreads;
        }
        
        return response()->json(['status' => 'success', 'message' => 'Get as Group Data Successfully!', 'data' => $groups], 200);
    }

    public function getOwnGroups(Request $request){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }

        $user = JWTAuth::parseToken()->authenticate();
        
        $own_group_ids = \App\GroupUser::where('user_id', $user->id)->pluck('group_id')->toArray();
        $groups = \App\Group::whereIn('id', $own_group_ids)->get();
        foreach($groups as $group) {
            $group->own = 0;
            $group->own_status = 'pending';
            $group->admin = 0;
            $group_user = \App\GroupUser::where('user_id', $user->id)->where('group_id', $group->id)->first();
            if ($group_user) {
                $group->own = 1;
                if($group_user->admin) {
                    $group->admin = 1;
                }
                $group->own_status = $group_user->status;
            }

            $unreads = 0;
            $notifications = \App\Notification::where('group_id', $group->id)->where('status', 1)->get();
            foreach ($notifications as $notification) {
                $notification_unreads = \App\NotificationUnread::where('notification_id', $notification->id)->where('user_id', $user->id)->first();
                if ($notification_unreads)
                    $unreads = $unreads + 1;
                
                $comment_ids = \App\Comment::where('notification_id', $notification->id)->pluck('id')->toArray();
                $comment_unreads = \App\CommentUnread::whereIn('comment_id', $comment_ids)->where('user_id', $user->id)->get();
                if ($comment_unreads)
                    $unreads += count($comment_unreads);
            }
            
            $group->unreads = $unreads;
        }
        
        return response()->json(['status' => 'success', 'message' => 'Get as Group Data Successfully!', 'data' => $groups], 200);
    }

    public function getUserGroups(Request $request){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
        $user = JWTAuth::parseToken()->authenticate();
        $groups_of_user = \App\GroupUser::where('user_id', $user->id)->where('status', 'activated')->get();
        $user_groups = array();
        foreach ($groups_of_user as $user_group) {
            $group = \App\Group::where('id', $user_group->group_id)->where('status', 1)->select('id', 'name')->first();
            if ($group) {
                $unreads = 0;
                $notifications = \App\Notification::where('group_id', $user_group->group_id)->where('status', 1)->get();
                foreach ($notifications as $notification) {
                    $notification_unreads = \App\NotificationUnread::where('notification_id', $notification->id)->where('user_id', $user->id)->first();
                    if ($notification_unreads)
                        $unreads = $unreads + 1;
                    
                    $comment_ids = \App\Comment::where('notification_id', $notification->id)->pluck('id')->toArray();
                    $comment_unreads = \App\CommentUnread::whereIn('comment_id', $comment_ids)->where('user_id', $user->id)->get();
                    if ($comment_unreads)
                        $unreads += count($comment_unreads);
                }
                
                $group->unreads = $unreads;

                $user_groups[] = $group;
            }
        }

        return response()->json(['status' => 'success', 'message' => 'Get your group data successfully', 'data' => $user_groups], 200);
    }

    public function joinGroupUser(Request $request){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
        $validation = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first(), 'error_type' => 'no_fill'], 422);
        
        $user = JWTAuth::parseToken()->authenticate();
        $group_user = \App\GroupUser::where('user_id', $user->id)->where('group_id', request('id'))->first();
        if ($group_user)
            return response()->json(['status' => 'fail', 'message' => 'You are alrady this group memeber!', 'error_type' => 'user_exist'], 422);
        
        $group = \App\Group::find(request('id'));
        if (!$group)
            return response()->json(['status' => 'fail', 'message' => 'Could not find this group!', 'error_type' => 'no_group'], 422);
        
        $group_user = new \App\GroupUser;
        $group_user->user_id = $user->id;
        $group_user->group_id = request('id');
        $group_user->admin = 0;
        $group_user->status = 'pending';
        $group_user->save();
        
        $notification_names = array();
        $notification_names['swedish'] = 'En ny användare ansökte om att gå med i gruppen "' . $group->name . '"';
        $notification_names['else'] = 'A new user applied to join the group "' . $group->name . '"';
        
        $group_admins = \App\GroupUser::where('group_id', request('id'))->where('admin', 1)->where('status', 'activated')->get();
        if ($group_admins) {
            foreach ($group_admins as $group_admin) {
                $params_data = array(
                    'notification_id' => 0,
                    'group_id' => $group->id,
                    'group_name' => $group->name,
                    'user_id' => $user->id,
                    'status' => 'join'
                );
                $this->sendPushNotificationHttpRequest($group_admin->user_id, $notification_names, $params_data, 1);
            }
        }

        return response()->json(['status' => 'success', 'message' => 'Group added!', 'data' => $group]);
    }

    public function activeGroupUser(Request $request){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['authenticated' => false], 422);
        }

        $validation = Validator::make($request->all(), [
            'group_id' => 'required',
            'user_id' => 'required',
        ]);
        if ($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first(), 'error_type' => 'no_fill'], 422);
        
        $user = JWTAuth::parseToken()->authenticate();
        $own_group = \App\GroupUser::where('user_id', $user->id)->where('group_id', request('group_id'))->first();
        if (!$own_group)
            return response()->json(['status' => 'fail', 'message' => 'You are not this group memeber!', 'data' => null, 'error_type' => 'no_member'], 422);
        if (!$own_group->admin)
            return response()->json(['status' => 'fail', 'message' => 'You are not an admin of this group!', 'data' => null, 'error_type' => 'no_admin'], 422);
        if ($own_group->status == 'pending')
            return response()->json(['status' => 'fail', 'message' => 'You are pending on this group.', 'error_type' => 'pending'], 422);
        
        $group = \App\Group::find(request('group_id'));
        if (!$group)
            return response()->json(['status' => 'fail', 'message' => 'Could not find this group!', 'error_type' => 'no_group'], 422);
        
        $group_user = \App\GroupUser::where('user_id', request('user_id'))->where('group_id', request('group_id'))->first();
        if (!$group_user)
            return response()->json(['status' => 'fail', 'message' => 'The user is not a this group member!', 'data' => null, 'error_type' => 'no_user'], 422);
        if ($group_user->status == 'activated')
            return response()->json(['status' => 'fail', 'message' => 'The user is already activated!', 'data' => null, 'error_type' => 'activated'], 422);
        
        $group_user->status = 'activated';
        $group_user->save();
        
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
        $this->sendPushNotificationHttpRequest(request('user_id'), $notification_names, $params_data, 1);

        return response()->json(['status' => 'success', 'message' => 'Active User`s Group Member Successfully!', 'data' => $group], 200);
    }
    
    public function deactiveGroupUser(Request $request){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['authenticated' => false], 422);
        }

        $validation = Validator::make($request->all(), [
            'group_id' => 'required',
            'user_id' => 'required',
        ]);
        if ($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first(), 'error_type' => 'no_fill'], 422);
        
        $user = JWTAuth::parseToken()->authenticate();
        $own_group = \App\GroupUser::where('user_id', $user->id)->where('group_id', request('group_id'))->first();
        if (!$own_group)
            return response()->json(['status' => 'fail', 'message' => 'You are not this group memeber!', 'data' => null, 'error_type' => 'no_member'], 422);
        if (!$own_group->admin)
            return response()->json(['status' => 'fail', 'message' => 'You are not an admin of this group!', 'data' => null, 'error_type' => 'no_admin'], 422);
        if ($own_group->status == 'pending')
            return response()->json(['status' => 'fail', 'message' => 'You are pending on this group.', 'error_type' => 'pending'], 422);

        $group = \App\Group::find(request('group_id'));
        if (!$group)
            return response()->json(['status' => 'fail', 'message' => 'Could not find this group!', 'error_type' => 'no_group'], 422);
        
        $group_user = \App\GroupUser::where('user_id', request('user_id'))->where('group_id', request('group_id'))->first();
        if (!$group_user)
            return response()->json(['status' => 'fail', 'message' => 'The user is not a this group member!', 'data' => null, 'error_type' => 'no_user'], 422);
        if ($group_user->status == 'pending')
            return response()->json(['status' => 'fail', 'message' => 'The user is already pending!', 'data' => null, 'error_type' => 'deactived'], 422);
        
        $group_user->status = 'pending';
        $group_user->save();

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
        $this->sendPushNotificationHttpRequest(request('user_id'), $notification_names, $params_data, 1);

        return response()->json(['status' => 'success', 'message' => 'Deactive User`s Group Member Successfully!', 'data' => $group], 200);
    }

    public function makeGroupAdmin(Request $request){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['authenticated' => false], 422);
        }

        $validation = Validator::make($request->all(), [
            'group_id' => 'required',
            'user_id' => 'required',
        ]);
        if ($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first(), 'error_type' => 'no_fill'], 422);
        
        $user = JWTAuth::parseToken()->authenticate();
        $group_user = \App\GroupUser::where('user_id', $user->id)->where('group_id', request('group_id'))->first();
        if (!$group_user)
            return response()->json(['status' => 'fail', 'message' => 'You are not this group memeber!', 'data' => null, 'error_type' => 'no_member'], 422);
        if (!$group_user->admin)
            return response()->json(['status' => 'fail', 'message' => 'You are not an admin of this group!', 'data' => null, 'error_type' => 'no_admin'], 422);
        
        $group = \App\Group::find(request('group_id'));
        if (!$group)
            return response()->json(['status' => 'fail', 'message' => 'Could not find this group!', 'error_type' => 'no_group'], 422);
            
        $group_user = \App\GroupUser::where('user_id', request('user_id'))->where('group_id', request('group_id'))->first();
        if (!$group_user)
            return response()->json(['status' => 'fail', 'message' => 'The user is not a this group member!', 'data' => null, 'error_type' => 'no_user'], 422);
        if ($group_user->admin == 1)
            return response()->json(['status' => 'fail', 'message' => 'The user is already group admin!', 'data' => null, 'error_type' => 'admin'], 422);
        
        $group_user->admin = 1;
        $group_user->save();

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
        $this->sendPushNotificationHttpRequest(request('user_id'), $notification_names, $params_data, 1);

        return response()->json(['status' => 'success', 'message' => 'Make User as Group Manager Successfully!', 'data' => $group], 200);
    }

    public function makeGroupUser(Request $request){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['authenticated' => false], 422);
        }

        $validation = Validator::make($request->all(), [
            'group_id' => 'required',
            'user_id' => 'required',
        ]);
        if ($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first(), 'error_type' => 'no_fill'], 422);
        
        $user = JWTAuth::parseToken()->authenticate();
        $own_group = \App\GroupUser::where('user_id', $user->id)->where('group_id', request('group_id'))->first();
        if (!$own_group)
            return response()->json(['status' => 'fail', 'message' => 'You are not this group memeber!', 'data' => null, 'error_type' => 'no_member'], 422);
        if (!$own_group->admin)
            return response()->json(['status' => 'fail', 'message' => 'You are not an admin of this group!', 'data' => null, 'error_type' => 'no_admin'], 422);
        if ($own_group->status == 'pending')
            return response()->json(['status' => 'fail', 'message' => 'You are pending on this group.', 'error_type' => 'pending'], 422);

        $group = \App\Group::find(request('group_id'));
        if (!$group)
            return response()->json(['status' => 'fail', 'message' => 'Could not find this group!', 'error_type' => 'no_group'], 422);
            
        $group_user = \App\GroupUser::where('user_id', request('user_id'))->where('group_id', request('group_id'))->first();
        if (!$group_user)
            return response()->json(['status' => 'fail', 'message' => 'The user is not a this group member!', 'data' => null, 'error_type' => 'no_user'], 422);
        if ($group_user->admin == 0)
            return response()->json(['status' => 'fail', 'message' => 'The user is already group user!', 'data' => null, 'error_type' => 'user'], 422);
        
        $group_user->admin = 0;
        $group_user->save();

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
        $this->sendPushNotificationHttpRequest(request('user_id'), $notification_names, $params_data, 1);

        return response()->json(['status' => 'success', 'message' => 'Disable User as Group Manager Successfully!', 'data' => $group], 200);
    }

    public function deleteGroupUser(Request $request){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['authenticated' => false], 422);
        }

        $validation = Validator::make($request->all(), [
            'group_id' => 'required',
            'user_id' => 'required',
        ]);
        if ($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first(), 'error_type' => 'no_fill'], 422);
        
        $user = JWTAuth::parseToken()->authenticate();
        $own_group = \App\GroupUser::where('user_id', $user->id)->where('group_id', request('group_id'))->first();
        if (!$own_group)
            return response()->json(['status' => 'fail', 'message' => 'You are not this group memeber!', 'data' => null, 'error_type' => 'no_member'], 422);
        if ($user->id == request('user_id')) {
            if ($own_group->admin) {
                $group_admins = \App\GroupUser::where('group_id', request('group_id'))->where('admin', 1)->where('user_id', '!=', $user->id)->get();
                if (!count($group_admins))
                    return response()->json(['status' => 'fail', 'message' => 'You are only this group manager!', 'data' => null, 'error_type' => 'alone_admin'], 422);
            }
        } else {
            if (!$own_group->admin)
                return response()->json(['status' => 'fail', 'message' => 'You are not an admin of this group!', 'data' => null, 'error_type' => 'no_admin'], 422);
            if ($own_group->status == 'pending')
                return response()->json(['status' => 'fail', 'message' => 'You are pending on this group.', 'error_type' => 'pending'], 422);
        }

        $group = \App\Group::find(request('group_id'));
        if (!$group)
            return response()->json(['status' => 'fail', 'message' => 'Could not find this group!', 'error_type' => 'no_group'], 422);
            
        $group_user = \App\GroupUser::where('user_id', request('user_id'))->where('group_id', request('group_id'))->first();
        if (!$group_user)
            return response()->json(['status' => 'fail', 'message' => 'The user is not a this group member!', 'data' => null, 'error_type' => 'no_user'], 422);
        
        $notification_names = array();
        $notification_names['swedish'] = 'Du har blivit borttagen från gruppen ' . $group->name;
        $notification_names['else'] = 'You have been removed from the group ' . $group->name;
        $params_data = array(
            'notification_id' => 0,
            'group_id' => $group->id,
            'group_name' => $group->name,
            'user_id' => request('user_id'),
            'status' => 'delete'
        );
        $this->sendPushNotificationHttpRequest(request('user_id'), $notification_names, $params_data, 1);

        $notifications = \App\Notification::where('group_id', request('group_id'))->where('user_id', request('user_id'))->get();
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
        $notifications = \App\Notification::where('group_id', request('group_id'))->where('user_id', '!=', request('user_id'))->get();
        if($notifications) {
            foreach ($notifications as $notification) {
                $notification_unreads = \App\NotificationUnread::where('notification_id', $notification->id)->where('user_id', request('user_id'))->get();
                if ($notification_unreads) {
                    foreach ($notification_unreads as $notification_unread) {
                        $notification_unread->delete();
                    }
                }
                $comments = $notification->Comments;
                if ($comments) {
                    foreach ($comments as $comment) {
                        $comment_unreads = \App\CommentUnread::where('comment_id', $comment->id)->where('user_id', request('user_id'))->get();
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

        return response()->json(['status' => 'success', 'message' => 'Delete User from Group Successfully!', 'data' => $group], 200);
    }

    /* Notification */
    private function sendPushNotification($user_ids, $notification_id, $notification_type, $urgent){
        if (!$user_ids) return null;
        
        $notification_names = array();
        $notification_names['swedish'] = \App\NotificationType::where('id', '=', $notification_type)->pluck('trans_name')[0];
        $notification_names['else'] = \App\NotificationType::where('id', '=', $notification_type)->pluck('name')[0];
        
        $requests = $responses = [];
        if ( is_array( $user_ids ) ) {
            foreach( $user_ids as $user_id ) {
                $params_data = array(
                    'notification_id' => $notification_id,
                    'group_id' => 0,
                    'group_name' => 0,
                    'user_id' => 0,
                    'status' => 'notification'
                );
                $result = $this->sendPushNotificationHttpRequest($user_id, $notification_names, $params_data, $urgent);
                $requests[] = $result['request'];
                $responses[] = $result['response'];
            }
        }
        return array('request' => $requests, 'response' => $responses);
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
    
    public function createNotification(Request $request){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
        $validation = Validator::make($request->all(), [
            'group_id' => 'required',
            'type' => 'required',
            'contents' => 'required|min:1',
            'datetime' => 'date_format:"Y-m-d H:i:s"|required',
            'urgent' => 'required',
        ]);
        if($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first(), 'error_type' => 'no_fill'], 422);
            
        $user = JWTAuth::parseToken()->authenticate();
        $profile = $user->Profile;
        if (!$profile)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find user profile!', 'data' => null, 'error_type' => 'no_profile'], 422);
        
        $group = \App\Group::find(request('group_id'));
        if (!$group)
            return response()->json(['status' => 'fail', 'message' => 'Could not find this group!', 'error_type' => 'no_group'], 422);
        
        $own_group = \App\GroupUser::where('user_id', $user->id)->where('group_id', request('group_id'))->first();
        if ($own_group->status == 'pending')
            return response()->json(['status' => 'fail', 'message' => 'You are pending on this group.', 'error_type' => 'pending'], 422);
       
        if($request->hasfile('images')) {
            foreach($request->file('images') as $image)
            {
                $extension = $image->getClientOriginalExtension();
                if (!in_array(strtolower($extension), $this->image_extensions)) {
                    return response()->json(['status' => 'fail', 'message' => 'Your images must be jpeg, png, jpg, gif, bmp!', 'error_type' => 'image_type_error'], 422);
                }
            }
        }
        
        $notification_type = \App\NotificationType::find(request('type'));
        if (!$notification_type) {
            return response()->json(['status' => 'fail', 'message' => 'You must specify the right notification type!', 'error_type' => 'type_error'], 422);
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
                        $image_flag = true;
                        if( $this->stamp_image_path && \File::exists($this->stamp_image_path) ) {
                            $file_tmp_name = $name . 'tmp.' . $extension;
                            $file = $image->move($this->noti_images_path, $file_tmp_name);
                            if ( ( $image_flag = $this->stampImage($this->noti_images_path . $file_tmp_name, $this->noti_images_path . $file_name) ) ) {
                                \File::delete($this->noti_images_path . $file_tmp_name);
                            }
                        } else {
                            $file = $image->move($this->noti_images_path, $file_name);
                        }
                        
                        if ( $image_flag ) {
                            list($width, $height) = getimagesize($this->noti_images_path . $file_name);
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
        
        // Unread Notification
        $group_users = \App\GroupUser::where('user_id', '!=', $user->id)->where('group_id', $group->id)->pluck('user_id')->toArray();
        if ($group_users) {
            foreach ($group_users as $group_user) {
                $notification_unread = new \App\NotificationUnread;
                $notification_unread->user_id = $group_user;
                $notification_unread->notification_id = $notification->id;
                $notification_unread->save();
            }
        }
        
        // Signed Users
        $users = \App\User::with('profile')->whereIn('id', $group_users)->where('status', '=', 'activated');
        $users->whereHas('profile',function($q) {
            $q->whereNotNull('push_token')->where('push_token', '<>', '');
        });
        $users->where(function($q) {
            $q->whereNull('deactivated_at')->orWhere('deactivated_at', '')->orWhereRaw('users.activated_at > users.deactivated_at');
        });
        $push_users = $users->pluck('id')->toArray();
        $push_result = $this->sendPushNotification($push_users, $notification->id, request('type'), request('urgent'));
        
        return response()->json(['status' => 'success', 'message' => 'Notification has created succesfully!', 'notification_id' => $notification->id], 200); // , 'notification_name' => $notification_name, 'push_result' => $push_result
    }
    
    public function getGroupNotifications(Request $request){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }

        $validation = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first(), 'error_type' => 'no_fill'], 422);
         
        $user = JWTAuth::parseToken()->authenticate();
        $profile = $user->Profile;
        if (!$profile)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find user profile!', 'data' => null, 'error_type' => 'no_profile'], 422);
            
        $group = \App\Group::find(request('id'));
        if (!$group)
            return response()->json(['status' => 'fail', 'message' => 'Could not find this group!', 'error_type' => 'no_group'], 422);
        
        $own_group = \App\GroupUser::where('user_id', $user->id)->where('group_id', request('id'))->first();
        if ($own_group->status == 'pending')
            return response()->json(['status' => 'fail', 'message' => 'You are pending on this group.', 'error_type' => 'pending'], 422);

        $group_user = \App\GroupUser::where('group_id', request('id'))->where('user_id', $user->id)->first();
        if (!$group_user)
            return response()->json(['status' => 'fail', 'message' => 'You are not a this group member!', 'error_type' => 'no_member'], 422);
        
        $group_id = $group->id;
        $group_country = $group->country;
        $notification_ids = \App\Notification::whereStatus(1);
        $notification_ids = $notification_ids->where(function($q) use($group_id, $group_country) {
            $q->where('group_id', '=', $group_id)->orWhere(function($qq) use($group_id, $group_country) {
                $qq->where('type', 5)->where(function($qqq) use($group_id, $group_country) {
                    $qqq->where(function($qqqq) {
                        $qqqq->where('group_id', 0)->where('country', '');
                    })->orWhere(function($qqqq) use($group_country) {
                        $qqqq->where('group_id', 0)->where('country', $group_country);
                    })->orWhere(function($qqqq) use($group_country)  {
                        $qqqq->where('group_id', request('id'))->where('country', $group_country);
                    });
                });
            });
        })->where('created_at', '>=', $group_user->created_at)->pluck('id')->toArray();
        
        $notifications = \App\Notification::with('user.simple_profile');
        $notifications_tmp = $notifications->whereIn('id', $notification_ids)->whereStatus(1);
        $total_counts = count($notifications_tmp->get());
        
        $notifications->orderBy('updated_at', 'DESC');
        
        $page_end = false;
        $result = [];
        if($request->has('page')) {
            $page_id = request('page');
            if (($page_id + 1) * $this->app_page_rows >= $total_counts) {
                $page_end = true;
            }
            if ($page_id * $this->app_page_rows <= $total_counts) {
                $result = $notifications->offset($page_id * $this->app_page_rows)->limit($this->app_page_rows)->get();
            }
        } else {
            $result = $notifications->get();
        }

        if ($result) {
            foreach ($result as $notification) {
                $notification['notification_unread'] = 0;
                $notification_unreads = \App\NotificationUnread::where('notification_id', $notification->id)->where('user_id', $user->id)->first();
                if ($notification_unreads)
                    $notification['notification_unread'] = 1;
                
                $unreads = 0;
                $comment_ids = \App\Comment::where('notification_id', $notification->id)->whereStatus(1)->pluck('id')->toArray();
                $comment_unreads = \App\CommentUnread::whereIn('comment_id', $comment_ids)->where('user_id', $user->id)->get();
                if ($comment_unreads)
                    $unreads += count($comment_unreads);
                
                $notification['comment_unreads'] = $unreads;
            }
        }
        
        return response()->json(['status' => 'success', 'message' => 'Get Notification Data Successfully!', 'notifications' => $result, 'end' => $page_end], 200);
    }
    
    public function getNotificationDetail(Request $request){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
        $validation = Validator::make($request->all(), [
            'notification_id' => 'required',
        ]);
        if($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first(), 'error_type' => 'no_fill'], 422);
        
        $user = JWTAuth::parseToken()->authenticate();
        $profile = $user->Profile;
        if (!$profile)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find user profile!', 'data' => null, 'error_type' => 'no_profile'], 422);
            
        $notification = \App\Notification::with('user.simple_profile', 'images')->where('id', '=', request('notification_id'))->first();
        if(!$notification)
            return response()->json(['status' => 'fail', 'message' => 'We can not find the notification!', 'error_type' => 'no_notification'], 422);
        if(!$notification->status)
            return response()->json(['status' => 'fail', 'message' => 'We can not find the notification!', 'error_type' => 'no_notification'], 422);
        
        if ($notification->type != 5) {
            $group = \App\Group::find($notification->group_id);
            if(!$group)
                return response()->json(['status' => 'fail', 'message' => 'You must be any group memeber!', 'error_type' => 'no_group'], 422);
                        
            $own_group = \App\GroupUser::where('group_id', $notification->group_id)->where('user_id', $user->id)->first();
            if (!$own_group)
                return response()->json(['status' => 'fail', 'message' => 'You are not this group memeber!', 'data' => null, 'error_type' => 'no_member'], 422);
            if ($own_group->status == 'pending')
                return response()->json(['status' => 'fail', 'message' => 'You are pending on this group.', 'error_type' => 'pending'], 422);
        }
        
        $comments = \App\Comment::where('notification_id', '=', request('notification_id'))->whereStatus(1);
        $total_counts = count($comments->get());
        
        $page_end = false;
        $comments_result = [];
        $comments = \App\Comment::with('images', 'user.simple_profile')->where('notification_id', '=', request('notification_id'))->whereStatus(1);
        $comments->orderBy('created_at', 'ASC');
        $page_id = 0;
        if($request->has('page')) {
            $page_id = request('page');
        }
        if (($page_id + 1) * $this->app_page_rows >= $total_counts) {
            $page_end = true;
        }
        if ($page_id * $this->app_page_rows <= $total_counts) {
            $comments_result = $comments->offset($page_id * $this->app_page_rows)->limit($this->app_page_rows)->get();
        }
        if ($page_id == 0) {
            $notification_unread = \App\NotificationUnread::where('notification_id', $notification->id)->where('user_id', $user->id)->first();
            if ($notification_unread) {
                $notification_unread->delete();
            }
        }
        if ($comments_result) {
            foreach ($comments_result as $comment) {
                $comment_unreads = \App\CommentUnread::where('comment_id', $comment->id)->where('user_id', $user->id)->get();
                $comment['unread'] = 0;
                if ($comment_unreads) {
                    if (count($comment_unreads)) {
                        $comment['unread'] = 1;
                    }
                    foreach ($comment_unreads as $comment_unread) {
                        $comment_unread->delete();
                    }
                }
            }
        }
        
        return response()->json(['status' => 'success', 'message' => 'Get Notification Detail Data Successfully!', 'notification' => $notification, 'my_avatar' => $profile->avatar, 'comments' => $comments_result, 'end' => $page_end], 200);
    }

    public function updateNotification(Request $request){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
        $validation = Validator::make($request->all(), [
            'notification_id' => 'required',
            'contents' => 'required',
        ]);
        if($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first(), 'error_type' => 'no_fill'], 422);
                
        $user = JWTAuth::parseToken()->authenticate();
        $profile = $user->Profile;
        if (!$profile)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find user profile!', 'data' => null, 'error_type' => 'no_profile'], 422);
        
        $notification = \App\Notification::find(request('notification_id'));
        if(!$notification)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find notificaioin!', 'error_type' => 'no_notification'], 422);
        if(!$notification->status)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find notificaioin!', 'error_type' => 'no_notification'], 422);
        
        $own_group = \App\GroupUser::where('group_id', $notification->group_id)->where('user_id', $user->id)->first();
        if (!$own_group)
            return response()->json(['status' => 'fail', 'message' => 'You are not this group memeber!', 'data' => null, 'error_type' => 'no_member'], 422);
        if (!$own_group->admin && $notification->user_id != $user->id)
            return response()->json(['status' => 'fail', 'message' => 'You are not an admin of this group!', 'data' => null, 'error_type' => 'no_admin'], 422);
        if ($own_group->status == 'pending')
            return response()->json(['status' => 'fail', 'message' => 'You are pending on this group.', 'error_type' => 'pending'], 422);
        
        $notification->contents = request('contents');
        $notification->save();
        
        return response()->json(['status' => 'success', 'message' => 'Update Notification Data Successfully!'], 200);
    }

    private function _deleteNotification($user_id, $id) {
        $notification = \App\Notification::find($id);
        if(!$notification)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find notificaioin!', 'error_type' => 'no_notification'], 422);
        
        $own_group = \App\GroupUser::where('group_id', $notification->group_id)->where('user_id', $user_id)->first();
        if (!$own_group)
            return response()->json(['status' => 'fail', 'message' => 'You are not this group memeber!', 'data' => null, 'error_type' => 'no_member'], 422);
        if (!$own_group->admin && $notification->user_id != $user->id)
            return response()->json(['status' => 'fail', 'message' => 'You are not an admin of this group!', 'data' => null, 'error_type' => 'no_admin'], 422);
        if ($own_group->status == 'pending')
            return response()->json(['status' => 'fail', 'message' => 'You are pending on this group.', 'error_type' => 'pending'], 422);
        
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
            
        $user = JWTAuth::parseToken()->authenticate();
        $profile = $user->Profile;
        if (!$profile)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find user profile!', 'data' => null, 'error_type' => 'no_profile'], 422);
            
        return $this->_deleteNotification($user->id, request('notification_id'));
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
            
        $user = JWTAuth::parseToken()->authenticate();
        $profile = $user->Profile;
        if (!$profile)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find user profile!', 'data' => null, 'error_type' => 'no_profile'], 422);
            
        $notification = \App\Notification::find(request('notification_id'));
        if(!$notification)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find notificaioin!', 'error_type' => 'no_notification'], 422);
        if(!$notification->status)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find notificaioin!', 'error_type' => 'no_notification'], 422);
            
        $own_group = \App\GroupUser::where('group_id', $notification->group_id)->where('user_id', $user->id)->first();
        if (!$own_group)
            return response()->json(['status' => 'fail', 'message' => 'You are not this group memeber!', 'data' => null, 'error_type' => 'no_member'], 422);
        if (!$own_group->admin && $notification->user_id != $user->id)
            return response()->json(['status' => 'fail', 'message' => 'You are not an admin of this group!', 'data' => null, 'error_type' => 'no_admin'], 422);
        if ($own_group->status == 'pending')
            return response()->json(['status' => 'fail', 'message' => 'You are pending on this group.', 'error_type' => 'pending'], 422);
            
        $images = $notification->Images;
        if ($images) {
            foreach ($images as $image) {
                if ($image->url == request('url')) {
                    $image->delete();
                }
            }
        }
        $notification->save();
        
        return response()->json(['status' => 'success', 'message' => 'Notification Image deleted!'], 200);
    }

    // Comment    
    public function createComment(Request $request){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }

        $validation = Validator::make($request->all(), [
            'notification_id' => 'required',
            'contents' => 'required',
            'datetime' => 'date_format:"Y-m-d H:i:s"|required',
        ]);
        if($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first(), 'error_type' => 'no_fill'], 422);
        
        $user = JWTAuth::parseToken()->authenticate();
        $profile = $user->Profile;
        if (!$profile)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find user profile!', 'data' => null, 'error_type' => 'no_profile'], 422);
            
        if(request('notification_id') == 0) {
            return response()->json(['status' => 'fail', 'message' => 'You must specify the notificaion!', 'error_type' => 'no_notification'], 422);
        }
        
        $notification = \App\Notification::find(request('notification_id'));
        if(!$notification)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find notification!', 'error_type' => 'no_notification'], 422);
        if(!$notification->status)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find notification!', 'error_type' => 'no_notification'], 422);
        $notification->save();

        $own_group = \App\GroupUser::where('user_id', $user->id)->where('group_id', $notification->group_id)->first();
        if (!$own_group)
            return response()->json(['status' => 'fail', 'message' => 'You are not this group memeber!', 'data' => null, 'error_type' => 'no_member'], 422);
        if ($own_group->status == 'pending')
            return response()->json(['status' => 'fail', 'message' => 'You are pending on this group.', 'error_type' => 'pending'], 422);
        
        if($request->hasfile('images')) {
            foreach($request->file('images') as $image)
            {
                $extension = $image->getClientOriginalExtension();
                if (!in_array(strtolower($extension), $this->image_extensions)) {
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
                $image_flag = true;
                
                if($this->stamp_image_path && \File::exists($this->stamp_image_path)) {
                    $file_tmp_name = $name . 'tmp.' . $extension;
                    $file = $image->move($this->noti_images_path, $file_tmp_name);
                    if ( ( $image_flag = $this->stampImage($this->noti_images_path . $file_tmp_name, $this->noti_images_path . $file_name) ) ) {
                        \File::delete($this->noti_images_path . $file_tmp_name);
                    }
                } else {
                    $file = $image->move($this->noti_images_path, $file_name);
                }
                
                if ( $image_flag ) {
                    list($width, $height) = getimagesize($this->noti_images_path . $file_name);
                    $comment_image = new \App\Image;
                    $comment_image->type = 'comment';
                    $comment_image->width = $width;
                    $comment_image->height = $height;
                    $comment_image->parent_id = $comment->id;
                    $comment_image->url = $file_name;
                    $comment_image->save();
                }
            }
        }

        // Unread Comment
        $group_users = \App\GroupUser::where('user_id', '!=', $user->id)->where('group_id', $notification->group_id)->pluck('user_id')->toArray();
        if ($group_users) {
            foreach ($group_users as $group_user) {
                $comment_unread = new \App\CommentUnread;
                $comment_unread->user_id = $group_user;
                $comment_unread->comment_id = $comment->id;
                $comment_unread->notification_id = $notification->id;
                $comment_unread->save();
            }
        }
        return response()->json(['status' => 'success', 'message' => 'Comment has created succesfully!', 'comment_id' => $comment->id], 200);
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
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find comment!', 'error_type' => 'no_comment'], 422);
        
        if(!$comment->status)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find comment!', 'error_type' => 'no_comment'], 422);
            
        $user = JWTAuth::parseToken()->authenticate();
        $profile = $user->Profile;
        if (!$profile)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find user profile!', 'data' => null, 'error_type' => 'no_profile'], 422);
        
        $notification = \App\Notification::find($comment->notification_id);
        if(!$notification)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find notification!', 'error_type' => 'no_notification'], 422);
        if(!$notification->status)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find notification!', 'error_type' => 'no_notification'], 422);
        $notification->save();
            
        $own_group = \App\GroupUser::where('user_id', $user->id)->where('group_id', $notification->group_id)->first();
        if (!$own_group)
            return response()->json(['status' => 'fail', 'message' => 'You are not this group memeber!', 'data' => null, 'error_type' => 'no_member'], 422);
        if (!$own_group->admin && $notification->user_id != $user->id)
            return response()->json(['status' => 'fail', 'message' => 'You are not an admin of this group!', 'data' => null, 'error_type' => 'no_admin'], 422); 
        if ($own_group->status == 'pending')
            return response()->json(['status' => 'fail', 'message' => 'You are pending on this group.', 'error_type' => 'pending'], 422);
        
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
            
        $user = JWTAuth::parseToken()->authenticate();
        $profile = $user->Profile;
        if (!$profile)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find user profile!', 'data' => null, 'error_type' => 'no_profile'], 422);
        
        $comment = \App\Comment::find(request('comment_id'));
        if(!$comment)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find comment!', 'error_type' => 'no_comment'], 422);
        if(!$comment->status)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find comment!', 'error_type' => 'no_comment'], 422);
        
        $notification = \App\Notification::find($comment->notification_id);
        if(!$notification)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find notification!', 'error_type' => 'no_notification'], 422);
        if(!$notification->status)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find notification!', 'error_type' => 'no_notification'], 422);
        $notification->save();

        $own_group = \App\GroupUser::where('user_id', $user->id)->where('group_id', $notification->group_id)->first();
        if (!$own_group)
            return response()->json(['status' => 'fail', 'message' => 'You are not this group memeber!', 'data' => null, 'error_type' => 'no_member'], 422);
        if ($own_group->status == 'pending')
            return response()->json(['status' => 'fail', 'message' => 'You are pending on this group.', 'error_type' => 'pending'], 422);
        
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

    // Advertisement
    public function getAdvertisement(Request $request){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
        $user = JWTAuth::parseToken()->authenticate();
        $profile = $user->Profile;
        if (!$profile)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find user profile!', 'data' => null, 'error_type' => 'no_profile'], 422);
        
        $advertisements = \App\Advertisement::whereNotNull('id');
        
        $now_date = date("Y-m-d");
        $advertisements->whereRaw("((`start_date` IS NOT NULL AND `end_date` IS NOT NULL AND `start_date` <= '" . $now_date . "' AND `end_date` >= '" . $now_date . "') OR (`start_date` IS NULL AND `end_date` IS NULL) OR (`start_date` IS NULL AND `end_date` >= '" . $now_date . "') OR (`end_date` IS NULL AND `start_date` <= '" . $now_date . "'))");
        $advertisements->whereRaw("((`min_postal` = '' AND `max_postal` = '') OR (`min_postal` = '' AND `max_postal` >= '" . $profile->postal_code . "') OR (`max_postal` = '' AND `min_postal` <= '" . $profile->postal_code . "') OR (`min_postal` <= '" . $profile->postal_code . "' AND `max_postal` >= '" . $profile->postal_code . "'))");
        $advertisements->whereStatus(1);
        if ($profile->country) {
            $advertisements->where('country', '=', $profile->country);
        }

        $advertisements_result = $advertisements->get();
        $advertisements_count = 0;
        $ad_ids = array();
        if ($advertisements_result) {
            $advertisements_count = count($advertisements_result);
            foreach($advertisements_result as $advertisement) {
               array_push($ad_ids, $advertisement->id);
            }
        }
        if (!$advertisements_count) {
            return response()->json(['status' => 'success', 'message' => 'Advertisement!', 'id' => 0, 'image' => basename($this->images_base_path), 'link' => url('/')], 200);
        }
        
        $min_count = \App\UserAdsCount::whereUserId($user->id)->whereViewDate($now_date)->whereIn('ad_id', $ad_ids)->orderBy('count', 'asc')->first();
        if (!$min_count) {
            $advertisement_index = rand(1, $advertisements_count);
            $advertisement = $advertisements_result[$advertisement_index - 1];
            
            $user_ads_count = \App\UserAdsCount::create([
                'ad_id' => $advertisement->id,
                'user_id' => $user->id,
                'view_date' => $now_date,
                'count ' => 1,
            ]);
        } else {
            $new_advertisements = array();
            $new_advertisements_exist = array();
            $all_show = 1;
            foreach ($advertisements_result as $advertisement) {
                $count = \App\UserAdsCount::whereUserId($user->id)->whereViewDate($now_date)->whereAdId($advertisement->id)->first();
                if (!$count) {
                    $all_show = 0;
                }
            }
            
            foreach ($advertisements_result as $advertisement) {
                $count = \App\UserAdsCount::whereUserId($user->id)->whereViewDate($now_date)->whereAdId($advertisement->id)->first();
                if (!$count) {
                    $new_advertisements[] = $advertisement;
                    $new_advertisements_exist[] = 0;
                } else {
                    if ($all_show) {
                        if ($count->count <= $min_count->count) {
                            $new_advertisements[] = $advertisement;
                            $new_advertisements_exist[] = 1;
                        }
                    }
                }
            }
            
            $new_advertisements_count = count($new_advertisements);
            $advertisement_index = rand(1, $new_advertisements_count);
            $advertisement = $new_advertisements[$advertisement_index - 1];
            $advertisement_exist = $new_advertisements_exist[$advertisement_index - 1];
            
            if ($advertisement_exist) {
                $user_ads_count = \App\UserAdsCount::whereUserId($user->id)->whereViewDate($now_date)->whereAdId($advertisement->id)->first();
                $user_ads_count->count = $user_ads_count->count + 1;
                $user_ads_count->save();
            } else {
                \App\UserAdsCount::create([
                    'ad_id' => $advertisement->id,
                    'user_id' => $user->id,
                    'view_date' => $now_date,
                    'count ' => 1,
                ]);
            }
        }
        
        $advertisement_count = \App\AdvertisementCount::whereUserId($user->id)->whereAdId($advertisement->id)->whereViewDate($now_date)->whereType('show')->first();
        if (!$advertisement_count) {
            \App\AdvertisementCount::create([
                'ad_id' => $advertisement->id,
                'type' => 'show',
                'user_id' => $user->id,
                'view_date' => $now_date,
                'count ' => 1,
            ]);
        } else {
            $advertisement_count->count = $advertisement_count->count + 1;
            $advertisement_count->save();
        }

        return response()->json(['status' => 'success', 'message' => 'Advertisement!', 'id' => $advertisement->id, 'image' => $advertisement->image, 'link' => $advertisement->link], 200);
    }

    public function clickAdvertisement(Request $request){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
        $validation = Validator::make($request->all(), [
            'advertisement_id' => 'required'
        ]);
        if($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first(), 'error_type' => 'no_fill'], 422);
            
        $advertisement = \App\Advertisement::find(request('advertisement_id'));
        if(!$advertisement)
            return response()->json(['status' => 'fail', 'message' => 'Could not find the advertisement!', 'error_type' => 'no_advertisement'], 422);
            
        $user = JWTAuth::parseToken()->authenticate();
        $now_date = date("Y-m-d");
        $advertisement_count = \App\AdvertisementCount::whereUserId($user->id)->whereViewDate($now_date)->whereType('click')->whereAdId($advertisement->id)->first();
        if ($advertisement_count) {
            $advertisement_count->count = $advertisement_count->count + 1;
            $advertisement_count->save();
        } else {
            $advertisement_count_new = \App\AdvertisementCount::create([
                'ad_id' => $advertisement->id,
                'type' => 'click',
                'user_id' => $user->id,
                'view_date' => $now_date,
                'count ' => 1,
            ]);
        }
        
        return response()->json(['status' => 'success', 'message' => 'Advertisement Clicked!'], 200);
    }
}