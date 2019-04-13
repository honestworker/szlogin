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

    public function login(Request $request){
        $credentials = $request->only('email', 'password');
        
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['status' => 'fail', 'message' => 'Email or Password is incorrect! Please try again.'], 422);
            }
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'message' => 'This is something wrong. Please try again!'], 500);
        }

        $email = strtolower(trim(request('email')));
        $user = \App\User::whereEmail($email)->where('backend', '=', 1)->first();
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

    public function check(){
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
    
    public function logout(){
        try {
            $token = JWTAuth::getToken();
            
            if ($token) {
                $user = JWTAuth::parseToken()->authenticate();
                if ($user) {
                    $user->deactivated_at = date('Y-m-d H:i:s');
                    $user->save();
                }
                JWTAuth::invalidate($token);
            }
            
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'message' => $e->getMessage(), 'error_type' => 'token_error'], 500);
        }
        
        return response()->json(['status' => 'success', 'message' => 'You are successfully logged out!'], 200);
    }

    public function signup(Request $request){
        $validation = Validator::make($request->all(), [
            'full_name' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6',
            'password_confirmation' => 'required|same:password'
        ]);
        
        if($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first()], 422);
        
        $email = strtolower(trim(request('email')));
        $user = \App\User::whereEmail($email)->where('backend', '=', 1)->first();
        if ($user) {
            return response()->json(['status' => 'fail', 'message' => 'Your email already registerd!<br/> Please try again!'], 422);
        }
        
        $user = \App\User::create([
            'email' => $email,
            'status' => 'pending',
            'backend' => 1,
        ]);
        $user->password = bcrypt(request('password'));
        $user->save();
        
        $profile = new \App\Profile;
        $profile->full_name = request('full_name');
        $user->profile()->save($profile);
        
        return response()->json(['status' => 'success', 'message' => 'You have signed up successfully.<br/> Please contact the administrator.<br/> The administrator will send you the activation link to your email.']);
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

    private function generateUuid($count){
        $result_uuid6 = '';
        for ($no = 0; $no < $count; $no++) {
            $result_uuid6 = $result_uuid6 . mt_rand(0, 9);
        }
        return $result_uuid6;
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
            return response()->json(['status' => 'fail', 'message' => 'We couldn\'t found any user with this email.<br/> Please try again!', 'error_type' => 'no_user'], 422);
        
        if($user->status != 'activated')
            return response()->json(['status' => 'fail', 'message' => 'We couldn\'t found any user with this email.<br/> Please try again!', 'error_type' => 'no_activated'], 422);
        
        if($user->backend) {
            $code = $this->generateUuid(100);
        } else {
            $code = $this->generateUuid(6);
        }
        $password_reset = \DB::table('password_resets')->where('email','=',$email)->first();
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
        
        return response()->json(['status' => 'success', 'message' => 'We have sent the verification code to your email.<br/> Please check your inbox!']);
    }

    public function validatePasswordReset(Request $request) {
        $validation = Validator::make($request->all(), [
            'token' => 'required',
        ]);

        $validate_password_request = \DB::table('password_resets')->where('code','=', request('token'))->first();        
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
            'token' => 'required',
            'password' => 'required|min:6',
            'password_confirmation' => 'required|same:password'
        ]);
        
        if($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first(), 'error_type' => 'no_fill'], 422);
        
        $email = strtolower(trim(request('email')));
        $user = \App\User::whereEmail($email)->first();
        if(!$user)
            return response()->json(['status' => 'fail', 'message' => 'We couldn\'t found any user with this email. Please try again!', 'error_type' => 'no_user'], 422);
            
        $validate_password_request = \DB::table('password_resets')->where('email','=',$email)->where('code','=', request('token'))->first();
        if(!$validate_password_request)
            return response()->json(['status' => 'fail', 'message' => 'Invalid password reset code!', 'error_type' => 'invalid_code'], 422);
            
        if(date("Y-m-d H:i:s", strtotime($validate_password_request->created_at . "+30 minutes")) < date('Y-m-d H:i:s'))
            return response()->json(['status' => 'fail', 'message' => 'Password reset code is expired. Please request reset password again!', 'error_type' => 'code_expired'], 422);
        
        $user->password = bcrypt(request('password'));
        $user->save();
        
        $user->notify(new PasswordResetted($user, $user->Profile->country, $user->Profile->full_name));
        
        return response()->json(['status' => 'success', 'message' => 'Your password has been changed successfully!. Please login again!']);
    }
}