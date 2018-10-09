<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use JWTAuth;

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
        $roles = $user->Profile->Roles;
        $is_super = 0;
        foreach ($roles as $role) {
            if ($role->role_id == 1) {
                $is_super = 1;
                break;
            }
        }
        
		$users = \App\User::with('profile', 'profile.group', 'profile.roles')->whereNotNull('id');
		
		// if(request()->has('first_name'))
        //     $users->whereHas('profile',function($q) use ($request){
        //         $q->where('first_name','like','%'.request('first_name').'%');
        //     });
            
		// if(request()->has('family_name'))
        //     $users->whereHas('profile',function($q) use ($request){
        //         $q->where('family_name','like','%'.request('family_name').'%');
        //     });
            
		if(request()->has('contact_person'))
            $users->whereHas('profile',function($q) {
                $q->where('contact_person','like','%'.request('contact_person').'%');
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
            
        if(request()->has('status'))
            $users->whereStatus(request('status'));
        
        if(request()->has('user_role')) {
            $role_id = request('user_role');
            if (request('user_role') != '0') {
                $users->whereHas('profile.roles', function($q) use ($role_id) {
                    $q->where('role_id', $role_id);
                });
            }
        }
            
		if (!$is_super) {
            $users->whereHas('profile.roles', function($q) {
                $q->where('role_id', '>', 1);
            });
		}
        
        if(request()->has('sortBy') && request()->has('order')) {
            if(request('sortBy') == 'status' || request('sortBy') == 'email')
                $users->select('id', 'email', 'status')->orderBy(request('sortBy'), request('order'));
            else if(request('sortBy') == 'contact_person' || request('sortBy') == 'phone_number')
                $users->select('id', 'email', 'status', \DB::raw('(select ' . request('sortBy') . ' from profiles where users.id = profiles.user_id) as '. request('sortBy')))->orderBy(request('sortBy'), request('order'));
            // else if(request('sortBy') == 'group_id')
            //     $users->select('id', 'email', 'status', \DB::raw('(select group_id from groups where ' .  .' = groups.id) as group_id'))->orderBy('group_id', request('order'));
        }
        
		return $users->paginate(request('pageLength'));
	}

	public function allRole(){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['authenticated' => false], 422);
        }

		$roles = \App\Role::whereNotNull('id');
		
        $roles->orderBy('id', 'ASC');
        $roles->where('id', '>', 1);
        
		return response()->json(['status' => 'success', 'message' => 'Get User Role Data Successfully!', 'data' => $roles->get()], 200);
	}

	public function getUser(Request $request, $id){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['authenticated' => false], 422);
        }

        $user = \App\User::find($id);
        if(!$user)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find user!', 'data' => null], 422);
            
        $profile = $user->Profile;
        $email = $user->email;
        $group_id = $role = "";
        
        $user_roles = \App\UserRole::where('user_id', '=', $id)->pluck('role_id');
        if (count($user_roles)) {
            foreach ($user_roles as $user_role) {
                $role_name = \App\Role::where('id', '=', $user_role)->pluck('name');
                if (count($role_name)) {
                    if ($role) {
                        $role = $role . ", ";
                    }
                    $role = $role . $role_name[0];
                }
            }
        }
		
        $user_group = \App\Group::where('id', '=', $profile->group_id)->pluck('group_id');
        if (count($user_group)) {
            $group_id = $user_group[0];
        }
		
		return response()->json(['status' => 'success', 'message' => 'Get User Data Successfully!', 'data' => compact('profile','role','group_id', 'email')], 200);
    }
    
    public function profile() {
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['authenticated' => false], 422);
        }

        $user = JWTAuth::parseToken()->authenticate();
        if (!$user) {
            return response()->json(['status' => 'fail', 'message' => 'Your token is invaild!']);
        }
        $profile = $user->Profile;
        $user_avatar = "";
        if ($profile->avatar) {
            $user_avatar = url('/') . '/images/users/' . $profile->avatar;
        }
        return response()->json(['status' => 'success', 'first_name' => $profile->first_name, 'family_name' => $profile->family_name, 'email' => $user->email, 'phone_number' => $profile->phone_number, 'avatar' => $user_avatar]);
    }
    
    public function changePassword(Request $request) {
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['authenticated' => false], 422);
        }

        $user = JWTAuth::parseToken()->authenticate();
        if (!$user) {
            return response()->json(['status' => 'fail', 'message' => 'Your token is invaild!']);
        }
        
        $validation = Validator::make($request->all(),[
            'password' => 'required|min:6',
        ]);
        
        if($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first()], 422);
            
        $user->password = bcrypt(request('password'));
        $user->save();
        
        return response()->json(['status' => 'success', 'message' => 'Your password has been updated!'], 200);
    }
    
    public function getGroupUsers(Request $request) {
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['authenticated' => false], 422);
        }

        $user = JWTAuth::parseToken()->authenticate();
        $roles = $user->Profile->Roles;
        $is_manager = 0;
        foreach ($roles as $role) {
            if ($role->role_id >= 3) {
                $is_manager = 1;
                break;
            }
        }

        if (!$is_manager) {
            return response()->json(['status' => 'fail', 'message' => 'You do not have a manager permission.'], 422);
        }

        $profile = $user->Profile;
        $group_id = $profile->group_id;
        if (!$group_id) {
            return response()->json(['status' => 'fail', 'message' => 'You must became a group member.'], 422);
        }
		
        $group = \App\Group::find($group_id);
        if (!$group) {
            return response()->json(['status' => 'fail', 'message' => 'Could not find the your group.'], 422);
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
            return response()->json(['authenticated' => false], 422);
        }

        $validation = Validator::make($request->all(),[
            'first_name' => 'required|min:2',
            'family_name' => 'required|min:2',
            'date_of_birth' => 'required|date_format:Y-m-d',
            'gender' => 'required|in:male,female'
        ]);
        
        if($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first()], 422);
            
        $user = JWTAuth::parseToken()->authenticate();
        $profile = $user->Profile;
        
        $profile->first_name = request('first_name');
        $profile->family_name = request('family_name');
        $profile->date_of_birth = request('date_of_birth');
        $profile->gender = request('gender');
        $profile->twitter_profile = request('twitter_profile');
        $profile->facebook_profile = request('facebook_profile');
        $profile->google_plus_profile = request('google_plus_profile');
        $profile->save();
        
        return response()->json(['message' => 'Your profile has been updated!','user' => $user]);
    }

    public function updateAvatar(Request $request){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['authenticated' => false], 422);
        }

        $validation = Validator::make($request->all(), [
            'avatar' => 'required|image'
        ]);
        
        if ($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first()], 422);
            
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
            return response()->json(['authenticated' => false], 422);
        }

        $user = JWTAuth::parseToken()->authenticate();
        if(!$user)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find user!'], 422);
            
        if($user->avatar && \File::exists($this->avatar_path.$user->avatar))
            \File::delete($this->avatar_path.$user->avatar);
            
        $roles = $user->Profile->Roles;
        foreach ($roles as $role) {
            $role->delete();
        }

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
                    $notification->delete();
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
            return response()->json(['authenticated' => false], 422);
        }

        $user = JWTAuth::parseToken()->authenticate();
        
        $profile = $user->Profile;
        if(!$profile->avatar)
            return response()->json(['status' => 'fail', 'message' => 'No avatar uploaded!'], 422);
            
        if(\File::exists($this->avatar_path.$profile->avatar))
            \File::delete($this->avatar_path.$profile->avatar);
            
        $profile->avatar = null;
        $profile->save();
        
        return response()->json(['status' => 'success', 'message' => 'Avatar removed!'], 200);
    }

    public function deleteAccount(Request $request){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['authenticated' => false], 422);
        }
          
        $validation = Validator::make($request->all(),[
            'id' => 'required',
        ]);
        if ($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first()], 422);
        
        $user = \App\User::find($id);
        if(!$user)
            return response()->json(['status' => 'fail', 'message' => 'Could not find user!'],422);
            
        if($user->avatar && \File::exists($this->avatar_path.$user->avatar))
            \File::delete($this->avatar_path.$user->avatar);

        $profile = $user->Profile;
        $roles = $user->Profile->Roles;
        foreach ($roles as $role) {
            $role->delete();
        }
        
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
                    $notification->delete();
                }
                $comment->delete();
            }
        }

        $user->delete();
        
        return response()->json(['success','message' => 'User deleted!'], 200);
    }

    public function dashboard(){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['authenticated' => false], 422);
        }

        $users_count = \App\User::count();
        $tasks_count = \App\Task::count();
        $recent_incomplete_tasks = \App\Task::whereStatus(0)->orderBy('due_date','desc')->limit(5)->get();
        return response()->json(compact('users_count','tasks_count','recent_incomplete_tasks'));
    }
}
