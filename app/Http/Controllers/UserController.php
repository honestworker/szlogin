<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use JWTAuth;

class UserController extends Controller
{

    protected $avatar_path = 'images/users/';

	public function index(){
        $user = JWTAuth::parseToken()->authenticate();
        $roles = $user->Profile->Role;
        $is_super = 0;
        foreach ($roles as $role) {
            if ($role->role_id == 1) {
                $is_super = 1;
                break;
            }
        }
        
		$users = \App\User::with('profile', 'profile.group', 'profile.role');

		if(request()->has('first_name'))
            $query->whereHas('profile',function($q) use ($request){
                $q->where('first_name','like','%'.request('first_name').'%');
            });
            
		if(request()->has('family_name'))
            $query->whereHas('profile',function($q) use ($request){
                $q->where('family_name','like','%'.request('family_name').'%');
            });
            
		if(request()->has('email'))
			$users->where('email','like','%'.request('email').'%');
		
        if(request()->has('status'))
            $users->whereStatus(request('status'));
        
		if (!$is_super) {
            $users->whereHas('profile.role', function($q) {
                   $q->where('role_id', '>', 1);
            });
		}
        
        if(request()->has('sortBy') && request()->has('order')) {
            if(request('sortBy') == 'status' || request('sortBy') == 'email')
                $users->orderBy(request('sortBy'), request('order'));
            else if(request('sortBy') == 'contact_person' || request('sortBy') == 'group_name' || request('sortBy') == 'phone_number' || request('sortBy') == 'org_number')
                $users->with(['profile' => function ($q) {
                    $q->orderBy(request('sortBy'), request('order'));
                }]);
        }
        
		return $users->paginate(request('pageLength'));
	}

	public function allRole(){
		$roles = \App\Role::whereNotNull('id');
		
        $roles->orderBy('id', 'ASC');
        
		return response()->json(['status' => 'success', 'message' => 'Get User Role Data Successfully!', 'data' => $roles->get()], 200);
	}

	public function getUser(Request $request, $id){
        $user = \App\User::find($id);
        if(!$user)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find user!', 'data' => null],422);
            
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
    
    public function updateProfile(Request $request){

        $validation = Validator::make($request->all(),[
            'first_name' => 'required|min:2',
            'family_name' => 'required|min:2',
            'date_of_birth' => 'required|date_format:Y-m-d',
            'gender' => 'required|in:male,female'
        ]);

        if($validation->fails())
            return response()->json(['message' => $validation->messages()->first()],422);

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
        $validation = Validator::make($request->all(), [
            'avatar' => 'required|image'
        ]);

        if ($validation->fails())
            return response()->json(['message' => $validation->messages()->first()],422);

        $user = JWTAuth::parseToken()->authenticate();
        $profile = $user->Profile;

        if($profile->avatar && \File::exists($this->avatar_path.$profile->avatar))
            \File::delete($this->avatar_path.$profile->avatar);

        $extension = $request->file('avatar')->getClientOriginalExtension();
        $filename = uniqid();
        $file = $request->file('avatar')->move($this->avatar_path, $filename.".".$extension);
        $img = \Image::make($this->avatar_path.$filename.".".$extension);
        $img->resize(200, null, function ($constraint) {
            $constraint->aspectRatio();
        });
        $img->save($this->avatar_path.$filename.".".$extension);
        $profile->avatar = $filename.".".$extension;
        $profile->save();

        return response()->json(['message' => 'Avatar updated!','profile' => $profile]);
    }

    public function removeAvatar(Request $request){

        $user = JWTAuth::parseToken()->authenticate();

        $profile = $user->Profile;
        if(!$profile->avatar)
            return response()->json(['message' => 'No avatar uploaded!'],422);

        if(\File::exists($this->avatar_path.$profile->avatar))
            \File::delete($this->avatar_path.$profile->avatar);

        $profile->avatar = null;
        $profile->save();

        return response()->json(['message' => 'Avatar removed!']);
    }

    public function destroy(Request $request, $id){
        if(env('IS_DEMO'))
            return response()->json(['message' => 'You are not allowed to perform this action in this mode.'],422);

        $user = \App\User::find($id);

        if(!$user)
            return response()->json(['message' => 'Couldnot find user!'],422);

        if($user->avatar && \File::exists($this->avatar_path.$user->avatar))
            \File::delete($this->avatar_path.$user->avatar);

        $user->delete();

        return response()->json(['success','message' => 'User deleted!']);
    }

    public function dashboard(){
      $users_count = \App\User::count();
      $tasks_count = \App\Task::count();
      $recent_incomplete_tasks = \App\Task::whereStatus(0)->orderBy('due_date','desc')->limit(5)->get();
      return response()->json(compact('users_count','tasks_count','recent_incomplete_tasks'));
    }
}
