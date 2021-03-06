<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use JWTAuth;

use App\Notifications\GroupManager;
use App\Notifications\Administrator;

date_default_timezone_set("Europe/Stockholm");

class UserController extends Controller
{
    protected $avatar_path = 'images/users/';
    
    public function index(){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['authenticated' => false], 422);
        }
        
        $user = JWTAuth::parseToken()->authenticate();
        
        $users = \App\User::with('profile', 'groups')->whereNotNull('id');
        
        if(request()->has('full_name'))
            $users->whereHas('profile',function($q) {
                $q->where('full_name','like', '%'.request('full_name').'%');
            });
            
        if(request()->has('email'))
            $users->where('email','like','%'.strtolower(request('email')).'%');
        
        if(request()->has('group_id'))
            if (request('group_id'))
                $users->whereHas('groups',function($q) {
                    $q->where('group_id', request('group_id'));
                });

        if(request()->has('country'))
            if (request('country'))
                $users->whereHas('profile',function($q) {
                    $q->where('country','like', '%'. request('country') . '%');
                });
            
        if(request()->has('backend')) {
            $users->whereBackend(request('backend'));
        } else {
            $users->whereBackend(0);
        }
        
        if(request()->has('admin'))
            if(request('admin') >= 0)
                $users->whereHas('groups', function($q) {
                    $q->where('admin', '=', request('admin'));
                });
        
        if(request()->has('status'))
            $users->whereStatus(request('status'));
                
        if(request()->has('sortBy') && request()->has('order')) {
            if(request('sortBy') == 'status' || request('sortBy') == 'email' || request('sortBy') == 'created_at')
                $users->select('id', 'email', 'status', 'created_at')->orderBy(request('sortBy'), request('order'));
            else if(request('sortBy') == 'full_name' || request('sortBy') == 'country')
                $users->select('id', 'email', 'status', 'created_at', \DB::raw('(select ' . request('sortBy') . ' from profiles where users.id = profiles.user_id) as '. request('sortBy')))->orderBy(request('sortBy'), request('order'));
        }
        
        return $users->paginate(request('pageLength'));
    }

    public function getUser(Request $request, $id){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
        $user = \App\User::find($id);
        if(!$user)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find user!', 'data' => null, 'error_type' => 'no_user'], 422);
            
        $profile = $user->Profile;
        if (!$profile)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find user profile!', 'data' => null, 'error_type' => 'no_profile'], 422);

        $email = $user->email;
        
        return response()->json(['status' => 'success', 'message' => 'Get User Data Successfully!', 'data' => compact('profile', 'email')], 200);
    }
    
    public function getMyProfile(Request $request){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
        $user = JWTAuth::parseToken()->authenticate();
        if(!$user)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find user!', 'data' => null, 'error_type' => 'no_user'], 422);
            
        $profile = $user->Profile;
        if (!$profile)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find user profile!', 'data' => null, 'error_type' => 'no_profile'], 422);
            
        $email = $user->email;
        
        return response()->json(['status' => 'success', 'message' => 'Get User Data Successfully!', 'data' => compact('profile', 'user', 'email')], 200);
    }
   
    public function changePassword(Request $request){
        if(env('IS_DEMO'))
            return response()->json(['message' => 'You are not allowed to perform this action in this mode.'], 422);
        
        $validation = Validator::make($request->all(), [
            'current_password' => 'required|min:6',
            'new_password' => 'required|min:6',
            'new_password_confirmation' => 'required|same:new_password'
        ]);
        
        if($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first()], 422);
            
        $user = JWTAuth::parseToken()->authenticate();
        
        if(!\Hash::check(request('current_password'), $user->password))
            return response()->json(['status' => 'fail', 'message' => 'Old password does not match! Please try again!'], 422);
            
        $user->password = bcrypt(request('new_password'));
        $user->save();
                
        return response()->json(['status' => 'success', 'message' => 'Your password has been changed successfully!']);
    }

    public function updateProfile(Request $request){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
        $user = JWTAuth::parseToken()->authenticate();
        $profile = $user->Profile;
        if (!$profile)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find user profile!', 'data' => null, 'error_type' => 'no_profile'], 422);
        
        $profile->full_name = request('full_name');
        $profile->street_address = request('street_address');
        $profile->postal_code = request('postal_code');
        $profile->country = request('country');
        $profile->save();
        
        return response()->json(['message' => 'Your profile has been updated!','user' => $user]);
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
            \File::delete($this->avatar_path.$profile->avatar);
            
        $extension = $request->file('avatar')->getClientOriginalExtension();
        $filename = uniqid();
        $file = $request->file('avatar')->move($this->avatar_path, $filename.".".$extension);
        $img = \Image::make($this->avatar_path. $filename . "." . $extension);
        $img->save($this->avatar_path.$filename.".".$extension);
        $profile->avatar = $filename.".".$extension;
        $profile->save();
         
        return response()->json(['status' => 'success', 'message' => 'Avatar updated!', 'profile' => $profile]);
    }
    
    public function removeAvatar(Request $request){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
        $user = JWTAuth::parseToken()->authenticate();
        
        $profile = $user->Profile;
        if (!$profile)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find user profile!', 'data' => null, 'error_type' => 'no_profile'], 422);

        if(!$profile->avatar)
            return response()->json(['status' => 'fail', 'message' => 'No avatar uploaded!', 'error_type' => 'no_fill'], 422);
            
        if(\File::exists($this->avatar_path.$profile->avatar))
            \File::delete($this->avatar_path.$profile->avatar);
            
        $profile->avatar = null;
        $profile->save();
        
        return response()->json(['status' => 'success', 'message' => 'Avatar removed!'], 200);
    }

    public function deleteAccount(Request $request, $id){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
        $user = JWTAuth::parseToken()->authenticate();
        $is_manager = $user->backend;
        
        if (!$is_manager) {
            return response()->json(['status' => 'fail', 'message' => 'You do not have a administrator permission.', 'error_type' => 'no_admin'], 422);
        }
        
        $user = \App\User::find($id);
        if(!$user)
            return response()->json(['status' => 'fail', 'message' => 'Could not find user!', 'error_type' => 'no_user'], 422);
            
        if($user->avatar && \File::exists($this->avatar_path.$user->avatar))
            \File::delete($this->avatar_path.$user->avatar);
            
        $profile = $user->Profile;
        if ($profile)
            $profile->delete();
        
        $notifications = $user->Notification;
        if ($notifications) {
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
                    $notification->delete();
                }
            }
        }
        
        $comments = $user->Comments;
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

        $user_groups = \App\GroupUser::where('user_id', $user->id)->get();
        if ($user_groups) {
            foreach ($user_groups as $user_group) {
                $notifications = \App\Notification::where('group_id', $user_group->group_id)->where('user_id', '!=', $user->id)->get();
                if($notifications) {
                    foreach ($notifications as $notification) {
                        $notification_unreads = \App\NotificationUnread::where('notification_id', $notification->id)->where('user_id', $user->id)->get();
                        if ($notification_unreads) {
                            foreach ($notification_unreads as $notification_unread) {
                                $notification_unread->delete();
                            }
                        }
                        $comments = $notification->Comments;
                        if ($comments) {
                            foreach ($comments as $comment) {
                                $comment_unreads = \App\CommentUnread::where('comment_id', $comment->id)->where('user_id', $user->id)->get();
                                if ($comment_unreads) {
                                    foreach ($comment_unreads as $comment_unread) {
                                        $comment_unread->delete();
                                    }
                                }
                            }
                        }
                    }
                }
                $user_group->delete();
            }
        }
        
        $user->delete();
        
        return response()->json(['status' => 'success', 'message' => 'User deleted!'], 200);
    }

    public function makeAdministrator(Request $request, $id){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        $user = \App\User::find($id);
        if(!$user)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find user!'], 422);

        $profile = $user->Profile;
        if (!$profile)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find user profile!', 'data' => null, 'error_type' => 'no_profile'], 422);
            
        if ($user->status == 'activated')
            return response()->json(['status' => 'fail', 'message' => 'This user already is administrator!'], 422);
            
        $user->status = 'activated';
        $user->save();
        
        //$user->notify(new Administrator(true, $profile->country, $profile->full_name));
        
        return response()->json(['status' => 'success', 'message' => 'The user is made as a administrator successfully.', 'user' => $user]);
    }

    public function disableAdministrator(Request $request, $id){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        $user = \App\User::find($id);
        if(!$user)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find user!'], 422);
            
        $profile = $user->Profile;
        if (!$profile)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find user profile!', 'data' => null, 'error_type' => 'no_profile'], 422);
            
        if ($user->status == 'pending')
            return response()->json(['status' => 'fail', 'message' => 'This user already is not administrator!'], 422);
            
        $user->status = 'pending';
        $user->save();
        
        //$user->notify(new Administrator(false, $profile->country, $profile->full_name));
        
        return response()->json(['status' => 'success', 'message' => 'The user is made as not administrator successfully.', 'user' => $user]);
    }
    
    public function overview(){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['authenticated' => false], 422);
        }
        
        $total = count(\App\User::where('backend', '=', 0)->get());
        
        $infor = array();
        $now_date = date("Y-m-d");
        $year = date('Y', strtotime($now_date));
        $month = date('m', strtotime($now_date));
        for ($month_index = 1; $month_index <= $month; $month_index++) {
            $users = \App\User::whereNotNull('id');
            //$infor[] = count($users->whereStatus('activated')->whereYear('created_at', '=',  $year)->whereMonth('created_at', '=',   $month_index )->get());
            $infor[] = count($users->where('backend', '=', '0')->whereYear('created_at', '=',  $year)->whereMonth('created_at', '=',   $month_index )->get());
        }
        
        $visitor_infor = array();
        $day_before = date("Y-m-d H:i:s", strtotime("$now_date  -1 day"));
        $users = \App\User::whereNotNull('id');
        //$visitor_infor[] = count($users->whereStatus('activated')->where('created_at', '>=',  $day_before)->get());
        $visitor_infor[] = count($users->where('backend', '=', '0')->where('created_at', '>=',  $day_before)->get());
        
        $week_before = date("Y-m-d H:i:s", strtotime("$now_date  -7 days"));
        $users = \App\User::whereNotNull('id');
        //$visitor_infor[] = count($users->whereStatus('activated')->where('created_at', '>=',  $week_before)->get());
        $visitor_infor[] = count($users->where('backend', '=', '0')->where('created_at', '>=',  $week_before)->get());
        
        $month_before = date("Y-m-d H:i:s", strtotime("$now_date  -30 days"));
        $users = \App\User::whereNotNull('id');
        //$visitor_infor[] = count($users->whereStatus('activated')->where('created_at', '>=',  $month_before)->get());
        $visitor_infor[] = count($users->where('backend', '=', '0')->where('created_at', '>=',  $month_before)->get());
        
        $users = \App\User::whereNotNull('id');
        //$visitor_infor[] = count($users->whereStatus('activated')->whereYear('created_at', '=',  $year)->get());
        $visitor_infor[] = count($users->where('backend', '=', '0')->whereYear('created_at', '=',  $year)->get());
        
        $visitors_infor = $visitor_infor1 = $visitor_infor2 = array();
        for ($month_index = 1; $month_index <= 12; $month_index++) {
            $visitor = \App\Visitor::where('year', '=', $year - 1)->where('month', '=', $month_index)->first();
            if ($visitor) {
                $visitor_infor1[] = $visitor->value + 0;
            } else {
                $visitor_infor1[] = 0;
            }
        }
        for ($month_index = 1; $month_index <= 12; $month_index++) {
            $visitor = \App\Visitor::where('year', '=', $year)->where('month', '=', $month_index)->first();
            if ($visitor) {
                $visitor_infor2[] = $visitor->value + 0;
            } else {
                $visitor_infor2[] = 0;
            }
        }
        $visitors_infor = [$visitor_infor1, $visitor_infor2];
        
        $users = \App\User::whereNotNull('id');
        //$activated_users = count($users->whereStatus('activated')->where('activated_at', '>=',  $month_before)->get());
        $activated_users = count($users->where('backend', '=', '0')->where('activated_at', '>=',  $month_before)->get());
        
        return response()->json(['status' => 'success', 'message' => 'User and Visitor Overview!', 'data' => compact('total', 'infor', 'year',  'activated_users', 'visitor_infor', 'visitors_infor')]);
    }
}
