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

class AuthController extends Controller
{
    protected $avatar_path = 'images/users/';

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['status' => 'fail', 'message' => 'Email or Password is incorrect! Please try again.'], 422);
            }
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'message' => 'This is something wrong. Please try again!'], 500);
        }
        
        $user = \App\User::whereEmail(request('email'))->where('backend', '=', 1)->first();
        if (!$user)
            return response()->json(['status' => 'fail', 'message' => 'You do not have any administrator account. Please sign up.'], 422);
            
        if($user->status == 'pending')
            return response()->json(['status' => 'fail', 'message' => 'Your account is disabled administrator permission.'], 422);
            
        if($user->status == 'banned')
            return response()->json(['status' => 'fail', 'message' => 'Your account is banned. Please contact system administrator.'], 422);
            
        if($user->status != 'activated')
            return response()->json(['status' => 'fail', 'message' => 'There is something wrong with your account. Please contact system administrator.'], 422);
            
        return response()->json(['status' => 'success', 'message' => 'You are successfully logged in!', 'token' => $token], 200);
    }

    private function calculateVisitor($user) {
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

    public function authenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');
        
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['status' => 'fail', 'message' => 'Email or Password is incorrect! Please try again.', 'error_type' => 'incorrect'], 422);
            }
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'message' => 'This is something wrong. Please try again!', 'error_type' => 'token_error'], 500);
        }
        
        $user = \App\User::whereEmail(request('email'))->where('backend', '=', 0)->first();
        if (!$user)
            return response()->json(['status' => 'fail', 'message' => 'You have not registed. Please sign up and try to login again.', 'error_type' => 'no_user'], 422);
            
        if($user->status == 'pending')
            return response()->json(['status' => 'fail', 'message' => 'Your account hasn\'t been activated. Please check your email & activate account.', 'error_type' => 'no_groupid'], 422);
            
        if($user->status == 'banned')
            return response()->json(['status' => 'fail', 'message' => 'Your account is banned. Please contact system administrator.', 'error_type' => 'banned'], 422);
            
        if($user->status != 'activated' && $user->status != 'pending_activated')
            return response()->json(['status' => 'fail', 'message' => 'There is something wrong with your account. Please contact system administrator.', 'error_type' => 'no_signup'], 422);
            
        $this->calculateVisitor($user);
                
        if ($user->Profile->is_admin == 1)
            $manager = true;
        else
            $manager = false;
            
        $user = \App\User::with('profile')->whereEmail(request('email'))->where('backend', '=', 0)->select('id', 'email')->get();
        
        return response()->json(['status' => 'success', 'message' => 'You are successfully logged in!', 'token' => $token, 'user' => $user], 200);
    }

    public function getAuthUser(){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
        $user = JWTAuth::parseToken()->authenticate();
        $profile = $user->Profile;
        if (!$profile)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find user profile!', 'data' => null, 'error_type' => 'no_profile'], 422);
            
        $social_auth = ($user->password) ? 0 : 1;
        
        return response()->json(compact('user','profile','social_auth'));
    }

    public function check()
    {
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response(['authenticated' => false]);
        }
        
        $user = JWTAuth::parseToken()->authenticate();
        if ($user) {
            return response(['authenticated' => true]);
        } else {
            return response(['authenticated' => false]);
        }
    }

    public function logout()
    {
        try {
            $token = JWTAuth::getToken();
            
            if ($token) {
                $user = JWTAuth::parseToken()->authenticate();
                if ($user) {
                    $user->deactivated_at = date('Y-m-d H:i:s');
                    $user->push_token = "";
                    $user->save();
                }
                JWTAuth::invalidate($token);
            }
            
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'message' => $e->getMessage(), 'error_type' => 'token_error'], 500);
        }
        
        return response()->json(['status' => 'success', 'message' => 'You are successfully logged out!'], 200);
    }

    public function register(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'group_name' => 'required',
            'org_number' => 'required',
            'contact_person' => 'required',
            'phone_number' => 'required',
            'email' => 'required|email|unique:users',
        ]);
        
        if($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first(), 'error_type' => 'incorrect'], 422);
            
        $user = \App\User::whereEmail(request('email'))->first();
        if($user)
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first(), 'error_type' => 'email_registered'], 422);
            
        $user = \App\User::create([
            'email' => request('email'),
            'status' => 'pending',
        ]);
        
        $profile = new \App\Profile;
        $profile->group_name = request('group_name');
        $profile->org_number = request('org_number');
        $profile->contact_person = request('contact_person');
        $profile->phone_number = request('phone_number');
        $user->profile()->save($profile);
        
        return response()->json(['status' => 'success', 'message' => 'You have registered successfully. We will send you Group ID!'], 200);
    }

    public function signup_origin(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'group_id' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'first_name' => 'required',
            'family_name' => 'required',
            'street_address' => 'required',
            'street_number' => 'required',
            'postal_code' => 'required',
            'phone_number' => 'required',
            'country' => 'required',
            // 'password_confirmation' => 'required|same:password'
        ]);
        
        if($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first(), 'error_type' => 'no_fill'], 422);
            
        $user = \App\User::whereEmail(request('email'))->first();
        if (!$user) {
            return response()->json(['status' => 'fail', 'message' => 'Your email does not exist! Please first register your email!', 'error_type' => 'no_user'], 422);
        }
        
        // Check Activate Status
        if ($user->status == 'pending') {
            return response()->json(['status' => 'fail', 'message' => 'Your account does not assign Group ID yet! Please contact the administrator!', 'error_type' => 'no_assgin'], 422);
        }
        if ($user->status == 'activated') {
            return response()->json(['status' => 'fail', 'message' => 'Your account areadly signed up.', 'error_type' => 'signed_up'], 422);
        }
        
        $profile = $user->profile;
        if (!$profile)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find user profile!', 'data' => null, 'error_type' => 'no_profile'], 422);

        $group_id = \App\Group::where('id', '=', $profile->group_id)->pluck('group_id');
        $group_match = 0;
        if (count($group_id)) {
            if ($group_id[0] == request('group_id')) {
                $group_match = 1;
            }
        }
        if (!$group_match) {
            return response()->json(['status' => 'fail', 'message' => 'Your group id does not match!', 'error_type' => 'match_group'], 422);
        }
        
        $user->password = bcrypt(request('password'));
        $user->status = 'activated';
        $user->save();
        
        $profile->first_name = request('first_name');
        $profile->family_name = request('family_name');
        $profile->street_address = request('street_address');
        $profile->street_number = request('street_number');
        $profile->postal_code = request('postal_code');
        $profile->phone_number = request('phone_number');
        $profile->country = request('country');
        
        if(request()->file('avatar')) {
            $extension = $request->file('avatar')->getClientOriginalExtension();
            $filename = time('Ymdhis');
            $file = $request->file('avatar')->move($this->avatar_path, $filename.".".$extension);
            $img = \Image::make($this->avatar_path.$filename.".".$extension);
            $img->save($this->avatar_path.$filename.".".$extension);
            $profile->avatar = $filename.".".$extension;
        }
        
        $user->profile()->save($profile);
        
        return response()->json(['status' => 'success', 'message' => 'You have signed up successfully.']);
    }

    public function signup(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'group_id' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'first_name' => 'required',
            'family_name' => 'required',
            'street_address' => 'required',
            // 'street_number' => 'required',
            'postal_code' => 'required',
            'phone_number' => 'required',
            'country' => 'required',
            'city' => 'required',
            // 'password_confirmation' => 'required|same:password'
        ]);
        
        if($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first(), 'error_type' => 'no_fill'], 422);
            
        $user = \App\User::whereEmail(request('email'))->where('backend', '=', 0)->first();
        if ($user) {
            return response()->json(['status' => 'fail', 'message' => 'Your email already have registed! Please try with other email again.', 'error_type' => 'email_exist'], 422);
        }
        
        $group = \App\Group::where('group_id', '=', request('group_id'))->first();
        // Check Activate Status
        if (!$group) {
            return response()->json(['status' => 'fail', 'message' => 'The Group ID you have requested does not exist!', 'error_type' => 'match_group'], 422);
        }
        
        $user = \App\User::create([
            'email' => request('email'),
            'status' => 'activated',
            'password' => bcrypt(request('password'))
        ]);
        
        $profile = new \App\Profile;
        $profile->group_id = $group->id;
        $profile->first_name = request('first_name');
        $profile->family_name = request('family_name');
        $profile->full_name = request('first_name') . " " . request('family_name');
        $profile->street_address = request('street_address');
        // $profile->street_number = request('street_number');
        $profile->postal_code = request('postal_code');
        $profile->phone_number = request('phone_number');
        $profile->country = request('country');
        $profile->city = request('city');
        
        if(request()->file('avatar')) {
            $extension = $request->file('avatar')->getClientOriginalExtension();
            $filename = time('Ymdhis');
            $file = $request->file('avatar')->move($this->avatar_path, $filename.".".$extension);
            $img = \Image::make($this->avatar_path.$filename.".".$extension);
            $img->save($this->avatar_path.$filename.".".$extension);
            $profile->avatar = $filename.".".$extension;
        }        
        $user->profile()->save($profile);
                
        return response()->json(['status' => 'success', 'message' => 'You have signed up successfully.']);
    }

    public function signupBackend(Request $request){
        $validation = Validator::make($request->all(), [
            'full_name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'password_confirmation' => 'required|same:password'
        ]);
        
        if($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first()], 422);
            
        $user = \App\User::whereEmail(request('email'))->where('backend', '=', 1)->first();
        if ($user) {
            return response()->json(['status' => 'fail', 'message' => 'Your email already registerd! Please try again!'], 422);
        }
                
        $user = \App\User::create([
            'email' => request('email'),
            'status' => 'pending',
            'backend' => 1,
        ]);
        $user->password = bcrypt(request('password'));
        $user->save();
        
        $profile = new \App\Profile;
        $profile->full_name = request('full_name');
        $user->profile()->save($profile);
        
        return response()->json(['status' => 'success', 'message' => 'You have signed up successfully.\n Please contact the administrator.\n The administrator will send you the activation link to your email.']);
    }

    public function assignGroup(Request $request){
        if(env('IS_DEMO'))
            return response()->json(['status' => 'fail', 'message' => 'You are not allowed to perform this action in this mode.'], 422);
        
        if(!request()->has('id'))
            return response()->json(['status' => 'fail', 'message' => 'You must specify user ID!'], 422);
        
        if(!request()->has('group_id'))
            return response()->json(['status' => 'fail', 'message' => 'You must specify Group ID!'], 422);
        
        if (!request('group_id'))
            return response()->json(['status' => 'fail', 'message' => 'You must specify Group ID!'], 422);
        
        $user = \App\User::find(request('id'));
        
        if(!$user)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find user!'], 422);
        
        $profile = $user->Profile;
        if (!$profile)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find user profile!', 'data' => null, 'error_type' => 'no_profile'], 422);
            
        $profile->group_id = request('group_id');
        $profile->save();
        
        $user->status = 'assigned';
        $user->save();
        
        $group = \App\Group::find(request('group_id'));
        $user->notify(new Assign($group));
        
        return response()->json(['status' => 'success', 'message' => 'Group Id has assigned successfully!', 'user' => $user]);
    }

    public function activate($activation_token){
        $user = \App\User::whereActivationToken($activation_token)->first();
        
        if(!$user)
            return response()->json(['message' => 'Invalid activation token!'], 422);
            
        if($user->status == 'activated')
            return response()->json(['message' => 'Your account has already been activated!'], 422);
        
        if($user->status != 'pending_activated')
            return response()->json(['message' => 'Invalid activation token!'], 422);
        
        $user->status = 'activated';
        $user->save();
        $user->notify(new Activated($user));
        
        return response()->json(['message' => 'Your account has been activated!']);
    }

    private function generateUuid6() {
        $result_uuid6 = '';
        for ($no = 0; $no < 6; $no++) {
            $result_uuid6 = $result_uuid6 . mt_rand(0, 9);
        }
        return $result_uuid6;
    }

    public function forgetPassword(Request $request){
        $validation = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);
        
        if($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first(), 'error_type' => 'no_fill'], 422);
            
        $user = \App\User::whereEmail(request('email'))->first();
        if(!$user)
            return response()->json(['status' => 'fail', 'message' => 'We couldn\'t found any user with this email. Please try again!', 'error_type' => 'no_user'], 422);
            
        $code = $this->generateUuid6();
        $password_reset = \DB::table('password_resets')->where('email','=',request('email'))->first();
        if($password_reset) {
            \DB::table('password_resets')->where('email','=',request('email'))->update(array('code' => $code, 'created_at' => date("Y-m-d H:i:s")));
        } else {
            \DB::table('password_resets')->insert([
                'email' => request('email'),
                'code' => $code,
                'created_at' => date("Y-m-d H:i:s"),
            ]);
        }
        
        $user->notify(new PasswordReset($user, $code, $user->Profile->country, $user->Profile->first_name));
        
        return response()->json(['status' => 'success', 'message' => 'We have sent the verification code to your email. Please check your inbox!']);
    }

    public function validatePasswordReset(Request $request) {
        $validation = Validator::make($request->all(), [
            'email' => 'required|email',
            'code' => 'required',
            //'password_confirmation' => 'required|same:password'
        ]);
        
        $validate_password_request = \DB::table('password_resets')->where('email','=', request('email'))->first();
        if(!$validate_password_request)
            return response()->json(['status' => 'fail', 'message' => 'Invalid email!', 'error_type' => 'no_user'], 422);
            
        $validate_password_request = \DB::table('password_resets')->where('email','=', request('email'))->where('code','=', request('code'))->first();        
        if(!$validate_password_request)
            return response()->json(['status' => 'fail', 'message' => 'Invalid password reset code!', 'error_type' => 'invalid_code'], 422);
        
        if(date("Y-m-d H:i:s", strtotime($validate_password_request->created_at . "+30 minutes")) < date('Y-m-d H:i:s'))
            return response()->json(['status' => 'fail', 'message' => 'Password reset code is expired. Please request reset password again!', 'error_type' => 'code_expired'], 422);
            
        return response()->json(['status' => 'success', 'message' => 'Please reset the password!']);
    }

    public function resetPassword(Request $request){
        if(env('IS_DEMO'))
            return response()->json(['message' => 'You are not allowed to perform this action in this mode.'], 422);
            
        $validation = Validator::make($request->all(), [
            'email' => 'required|email',
            'code' => 'required',
            'password' => 'required|min:6',
        ]);
        
        if($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first(), 'error_type' => 'no_fill'], 422);
            
        $user = \App\User::whereEmail(request('email'))->first();
        if(!$user)
            return response()->json(['status' => 'fail', 'message' => 'We couldn\'t found any user with this email. Please try again!', 'error_type' => 'no_user'], 422);
            
        $validate_password_request = \DB::table('password_resets')->where('email','=',request('email'))->where('code','=', request('code'))->first();
        if(!$validate_password_request)
            return response()->json(['status' => 'fail', 'message' => 'Invalid password reset code!', 'error_type' => 'invalid_code'], 422);
            
        if(date("Y-m-d H:i:s", strtotime($validate_password_request->created_at . "+30 minutes")) < date('Y-m-d H:i:s'))
            return response()->json(['status' => 'fail', 'message' => 'Password reset code is expired. Please request reset password again!', 'error_type' => 'code_expired'], 422);
            
        $user->password = bcrypt(request('password'));
        $user->save();
        
        $user->notify(new PasswordResetted($user, $user->Profile->country, $user->Profile->first_name));
        
        return response()->json(['status' => 'success', 'message' => 'Your password has been changed successfully!. Please login again!']);
    }
}