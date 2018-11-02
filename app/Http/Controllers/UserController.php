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
        
		$users = \App\User::with('profile', 'profile.group')->whereNotNull('id');
		
		// if(request()->has('first_name'))
        //     $users->whereHas('profile',function($q) use ($request){
        //         $q->where('first_name','like','%'.request('first_name').'%');
        //     });
            
		// if(request()->has('family_name'))
        //     $users->whereHas('profile',function($q) use ($request){
        //         $q->where('family_name','like','%'.request('family_name').'%');
        //     });
            
		if(request()->has('full_name'))
            $users->whereHas('profile',function($q) {
                $q->where('full_name','like', '%'.request('full_name').'%');
            });
        
		if(request()->has('phone_number'))
            $users->whereHas('profile',function($q) {
                $q->where('phone_number','like','%'.request('phone_number').'%');
            });
            
		if(request()->has('email'))
			$users->where('email','like','%'.request('email').'%');
		
		if(request()->has('group_id'))
            $users->whereHas('profile.group',function($q) {
                $q->where('group_id','like','%'.request('group_id').'%');
            });
            
		if(request()->has('backend')) {
            $users->whereBackend(request('backend'));
        } else {
            $users->whereBackend(0);
        }
        
		if(request()->has('is_admin'))
            if(request('is_admin') >= 0)
                $users->whereHas('profile',function($q) {
                    $q->where('is_admin','=', request('is_admin'));
                });
                
        if(request()->has('status'))
            $users->whereStatus(request('status'));
                
        if(request()->has('sortBy') && request()->has('order')) {
            if(request('sortBy') == 'status' || request('sortBy') == 'email')
                $users->select('id', 'email', 'status')->orderBy(request('sortBy'), request('order'));
            else if(request('sortBy') == 'contact_person' || request('sortBy') == 'phone_number' || request('sortBy') == 'full_name' || request('sortBy') == 'group_id')
                $users->select('id', 'email', 'status', \DB::raw('(select ' . request('sortBy') . ' from profiles where users.id = profiles.user_id) as '. request('sortBy')))->orderBy(request('sortBy'), request('order'));
            else if(request('sortBy') == 'group_id')
                $users->select('id', 'email', 'status', \DB::raw('(select ' . request('sortBy') . ' from groups where profiles.user_id = groups.group_id) as '. request('sortBy')))->orderBy(request('sortBy'), request('order'));
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
        $email = $user->email;
        $group_id = $role = "";
        		
        $user_group = \App\Group::where('id', '=', $profile->group_id)->pluck('group_id');
        if (count($user_group)) {
            $group_id = $user_group[0];
        }
		
		return response()->json(['status' => 'success', 'message' => 'Get User Data Successfully!', 'data' => compact('profile', 'group_id', 'email')], 200);
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
        $email = $user->email;
        $group_id = $role = "";
        		
        $user_group = \App\Group::where('id', '=', $profile->group_id)->pluck('group_id');
        if (count($user_group)) {
            $group_id = $user_group[0];
        }
		
		return response()->json(['status' => 'success', 'message' => 'Get User Data Successfully!', 'data' => compact('profile', 'user', 'group_id', 'email')], 200);
    }

    public function profile() {
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
        $user = JWTAuth::parseToken()->authenticate();
        if (!$user) {
            return response()->json(['status' => 'fail', 'message' => 'Your token is invaild!', 'error_type' => 'token_error']);
        }
        $profile = $user->Profile;
        $user_avatar = "";
        if ($profile->avatar) {
            $user_avatar = url('/') . '/images/users/' . $profile->avatar;
        }
        return response()->json(['status' => 'success', 'first_name' => $profile->first_name, 'family_name' => $profile->family_name, 'full_name' => $profile->full_name, 'email' => $user->email, 'phone_number' => $profile->phone_number, 'avatar' => $user_avatar]);
    }
    
    public function changePassword(Request $request) {
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
        $user = JWTAuth::parseToken()->authenticate();
        if (!$user) {
            return response()->json(['status' => 'fail', 'message' => 'Your token is invaild!', 'status' => 'no_user']);
        }
        
        $validation = Validator::make($request->all(),[
            'password' => 'required|min:6',
        ]);
        
        if($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first(), 'status' => 'no_fill'], 422);
            
        $user->password = bcrypt(request('password'));
        $user->save();
        
        return response()->json(['status' => 'success', 'message' => 'Your password has been updated!'], 200);
    }
    
    public function changePasswordBackend(Request $request) {
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

    public function getGroupUsers(Request $request) {
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
        $user = JWTAuth::parseToken()->authenticate();
        $is_manager = $user->Profile->is_admin;
        
        if (!$is_manager) {
            return response()->json(['status' => 'fail', 'message' => 'You do not have a manager permission.', 'error_type' => 'no_manager'], 422);
        }
        
        $profile = $user->Profile;
        $group_id = $profile->group_id;
        if (!$group_id) {
            return response()->json(['status' => 'fail', 'message' => 'You must became a group member.', 'error_type' => 'no_memeber'], 422);
        }
		
        $group = \App\Group::find($group_id);
        if (!$group) {
            return response()->json(['status' => 'fail', 'message' => 'Could not find the your group.', 'error_type' => 'no_group'], 422);
        }
        
        $users = \App\User::with('profile');
        $users->whereHas('profile', function($q) use ($group_id) {
            $q->where('group_id', $group_id);
        });
        
        $users->where('id', '!=', $user->id);
        
        return response()->json(['status' => 'success', 'message' => 'Get Group User Data successfully!', 'users' => $users->select('id', 'email')->get()], 200);
    }

    public function updateProfile(Request $request){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
        // $validation = Validator::make($request->all(),[
        //     'contact_person' => 'required|min:1',
        //     'group_name' => 'required|min:1',
        //     'org_number' => 'required|min:1',
        // ]);
        
        // if($validation->fails())
        //     return response()->json(['status' => 'fail', 'message' => $validation->messages()->first(), 'error_type' => 'no_fill'], 422);
            
        $user = JWTAuth::parseToken()->authenticate();
        $profile = $user->Profile;
        
        // $profile->contact_person = request('contact_person');
        // $profile->group_name = request('group_name');
        // $profile->org_number = request('org_number');
        $profile->first_name = request('first_name');
        $profile->family_name = request('family_name');
        $profile->full_name = request('first_name') . " " . request('family_name');
        $profile->phone_number = request('phone_number');
        $profile->street_address = request('street_address');
        $profile->postal_code = request('postal_code');
        $profile->country = request('country');
        $profile->group_id = request('group_id');
        $profile->city = request('city');
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
    
    public function destroy(Request $request){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
        $user = JWTAuth::parseToken()->authenticate();
        if(!$user)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find user!', 'error_type' => 'no_user'], 422);
            
        if($user->avatar && \File::exists($this->avatar_path.$user->avatar))
            \File::delete($this->avatar_path.$user->avatar);
        
        $profile = $user->Profile;
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

    public function removeAvatar(Request $request){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
        $user = JWTAuth::parseToken()->authenticate();
        
        $profile = $user->Profile;
        if(!$profile->avatar)
            return response()->json(['status' => 'fail', 'message' => 'No avatar uploaded!', 'error_type' => 'no_fill'], 422);
            
        if(\File::exists($this->avatar_path.$profile->avatar))
            \File::delete($this->avatar_path.$profile->avatar);
            
        $profile->avatar = null;
        $profile->save();
        
        return response()->json(['status' => 'success', 'message' => 'Avatar removed!'], 200);
    }

    public function deleteAccountBackend(Request $request, $id){
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
        
        return response()->json(['status' => 'success', 'message' => 'User deleted!'], 200);
    }

    public function deleteAccount(Request $request){
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
        $is_manager = $user->Profile->is_admin;
        
        if (!$is_manager) {
            return response()->json(['status' => 'fail', 'message' => 'You do not have a manager permission.', 'error_type' => 'no_manager'], 422);
        }
        
        $user = \App\User::find(request('id'));
        if(!$user)
            return response()->json(['status' => 'fail', 'message' => 'Could not find user!', 'error_type' => 'no_user'], 422);
            
            
        if($user->avatar && \File::exists($this->avatar_path.$user->avatar))
            \File::delete($this->avatar_path.$user->avatar);
            
        $profile = $user->Profile;        
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
        
        return response()->json(['status' => 'success', 'message' => 'User deleted!'], 200);
    }

    public function makeGroupManager(Request $request, $id){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
        $user = \App\User::find($id);
        if(!$user)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find user!'], 422);
            
        $profile = $user->Profile;
        
        $group = \App\Group::find($profile->group_id);
        if(!$group)
            return response()->json(['status' => 'fail', 'message' => 'This user is not any group member.'], 422);
            
        if ($profile->is_admin)
            return response()->json(['status' => 'fail', 'message' => 'This user already is group manager!'], 422);
            
        $profile->is_admin = 1;
        $profile->save();
        
        $user->notify(new GroupManager(true, $profile->country, $profile->first_name));
        
        return response()->json(['status' => 'success', 'message' => 'The user is made as a group manager successfully.', 'user' => $user]);
    }

    public function disableGroupManager(Request $request, $id){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
        $user = \App\User::find($id);
        if(!$user)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find user!'], 422);
        
        $profile = $user->Profile;
        
        if (!$profile->is_admin)
            return response()->json(['status' => 'fail', 'message' => 'This user already is not group manager!'], 422);
        
        $profile->is_admin = 0;
        $profile->save();
        
        $user->notify(new GroupManager(false, $profile->country, $profile->first_name));
        
        return response()->json(['status' => 'success', 'message' => 'The user is made as not group manager successfully.', 'user' => $user]);
    }

    public function makeAdministrator(Request $request, $id){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        $user = \App\User::find($id);
        $profile = $user->Profile;
        if(!$user)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find user!'], 422);
        
        if ($user->status == 'activated')
            return response()->json(['status' => 'fail', 'message' => 'This user already is administrator!'], 422);
            
        $user->status = 'activated';
        $user->save();
        
        //$user->notify(new Administrator(true, $profile->country, $profile->first_name));
        
        return response()->json(['status' => 'success', 'message' => 'The user is made as a administrator successfully.', 'user' => $user]);
    }

    public function disableAdministrator(Request $request, $id){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        $user = \App\User::find($id);
        $profile = $user->Profile;
        if(!$user)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find user!'], 422);
        
        if ($user->status == 'pending')
            return response()->json(['status' => 'fail', 'message' => 'This user already is not administrator!'], 422);
            
        $user->status = 'pending';
        $user->save();
        
        //$user->notify(new Administrator(false, $profile->country, $profile->first_name));
        
        return response()->json(['status' => 'success', 'message' => 'The user is made as not administrator successfully.', 'user' => $user]);
    }

    public function savePushToken(Request $request){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
        $validation = Validator::make($request->all(),[
            'push_token' => 'required',
        ]);
        if ($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first(), 'error_type' => 'no_fill'], 422);
        
        $user = JWTAuth::parseToken()->authenticate();
        $user->push_token = request('push_token');
        $user->save();
        
        return response()->json(['status' => 'success', 'message' => 'Your push token is saved successfully.', 'user' => $user]);
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
        $user->language = request('language');
        $user->save();
        
        return response()->json(['status' => 'success', 'message' => 'Your language is updated successfully.']);
    }
    
    public function overview() {
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
                $visitor_infor1[] = $visitor->value;
            } else {
                $visitor_infor1[] = 0;
            }
        }
        for ($month_index = 1; $month_index <= 12; $month_index++) {
            $visitor = \App\Visitor::where('year', '=', $year)->where('month', '=', $month_index)->first();
            if ($visitor) {
                $visitor_infor2[] = $visitor->value;
            } else {
                $visitor_infor2[] = 0;
            }
        }
        $visitors_infor = [$visitor_infor1, $visitor_infor2];
        
        $users = \App\User::whereNotNull('id');
        //$activated_users = count($users->whereStatus('activated')->where('activated_at', '>=',  $month_before)->get());
        $activated_users = count($users->where('backend', '=', '0')->where('activated_at', '>=',  $month_before)->get());

        return response()->json(['status' => 'success', 'message' => 'User and Visitor Overview!', 'data' => compact('total', 'infor', 'activated_users', 'visitor_infor', 'visitors_infor')]);
    }
}
