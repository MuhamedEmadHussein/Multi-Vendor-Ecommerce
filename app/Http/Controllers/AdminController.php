<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use constGuards;
use constDefaults;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Jobs\SendAdminResetPasswordEmail;;
class AdminController extends Controller
{
    //
    public function handleLogin(Request $request){
        $fieldType = filter_var($request->login_id, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        if($fieldType == 'email'){
            $request->validate([
                'login_id' => 'required|email|exists:admins,email',
                'password' => 'required|min:5|max:45'
            ],[
                'login_id.required' => 'Email or Username is required',
                'login_id.email' => 'Invalid Email Address',
                'login_id.exists' => 'Email address not in System',
                'password.required' => 'Password is required',
            ]);
        }else{
            $request->validate([
                'login_id' => 'required|string|exists:admins,username',
                'password' => 'required|min:5|max:45'
            ],[
                'login_id.required' => 'Email or Username is required',
                'login_id.string' => 'Invalid Username',
                'login_id.exists' => 'Username not in System',
                'password.required' => 'Password is required',
            ]);
        }

        $creds = array(
            $fieldType => $request->login_id,
            'password' => $request->password
        );

        if(Auth::guard('admin')->attempt($creds)){
            return redirect()->route('admin.home');
        }else{
            session()->flash('fail', 'Incorrect Credentials');
            return redirect()->route('admin.login');
        }
    }

    public function handleLogout(Request $request){
        Auth::guard('admin')->logout();
        session()->flash('fail', 'You have been logged out');
        return redirect()->route('admin.login');
    }

    public function sendPasswordResetLink(Request $request) {
        $request->validate([
            'email' => 'required|email|exists:admins,email'
        ],[
            'email.required' => 'The :attribute is required',
            'email.email' => 'Invalid email',
            'email.exists' => 'The :attribute is not in the system'
        ]);

        $admin = Admin::where('email', $request->email)->first();
        $token = base64_encode(Str::random(64));

        $old_token = DB::table('password_reset_tokens')
                        ->where(['email' => $request->email, 'guard' => constGuards::ADMIN])
                        ->first();
        if ($old_token) {
            DB::table('password_reset_tokens')
                ->where(['email' => $request->email, 'guard' => constGuards::ADMIN])
                ->update([
                    'token' => $token,
                    'created_at' => Carbon::now()
                ]);
        } else {
            DB::table('password_reset_tokens')->insert([
                'email' => $request->email,
                'guard' => constGuards::ADMIN,
                'token' => $token,
                'created_at' => Carbon::now()
            ]);
        }

        $actionLink = route('admin.password-reset.show', ['token' => $token, 'email' => $request->email]);
        $data = [
            'actionLink' => $actionLink,
            'admin' => $admin
        ];

        // Dispatch the job to send the email
        try{
            SendAdminResetPasswordEmail::dispatch($data);
        }catch (\Exception $e) {
            dd($e->getMessage(),$e->getTraceAsString());
        }
        session()->flash('success', 'We have e-mailed your reset password link');
        return redirect()->route('admin.forget-password');
    }

    public function resetPassword(Request $request, $token){
        $check_token = DB::table('password_reset_tokens')
                        ->where(['token' => $token, 'guard' => constGuards::ADMIN])
                        ->first();

        if($check_token){
            // Check if Expired
            $diffMins = Carbon::createFromFormat('Y-m-d H:i:s', $check_token->created_at)->diffInMinutes(Carbon::now());
            if($diffMins > constDefaults::tokenExpiredMinutes){
                session()->flash('fail','Token Expired, request another reset password Reset Link');
                return redirect()->route('admin.forget-password',['token' => $token]);

            }else{
                return view('back.pages.admin.auth.reset-password',['token'=>$token]);
            }
        }else{
            session()->flash('fail','Invalid token, request another reset password Reset Link');
            return redirect()->route('admin.forget-password',['token' => $token]);
        }
    }

    public function handleResetPassword(Request $request){
        $request->validate([
            'new_password' => 'required|min:5|max:45|required_with:new_password_confirmation|same:new_password_confirmation',
            'new_password_confirmation' => 'required'
        ]);

        $token = DB::table('password_reset_tokens')
                    ->where(['token' => $request->token, 'guard' => constGuards::ADMIN])
                    ->first();

        $admin = Admin::where('email', $token->email)->first();
        // update Password
        $admin->update([
            'password' =>Hash::make($request->new_password),
        ]);

        //Delete Old Token
        DB::table('password_reset_tokens')->where([
            'token' => $request->token,
            'email' => $admin->email,
            'guard' => constGuards::ADMIN
        ])->delete();

        return redirect()->route('admin.login')->with('success', 'Done! your password has been changed, use new password to log in to you Account');
    }
}
