<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use App\User;
use DB;
use Log;
use Redirect;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;

use Illuminate\Contracts\Auth\PasswordBroker as PasswordBrokerContract;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /* Passsword reset method */

    public function reset(Request $request)
    {
        $this->validate($request, [
            'token' => 'required',
            'password' => 'required|confirmed|min:8',
        ]);

        $credentials = $request->only(
         'password', 'password_confirmation', 'token'
        );

        $email = DB::table('password_resets')->where('token', $request->token)->first();
        
        /*if( !is_null($email) && $email != null )
            $user = User::where('email', $email->email)->first();*/
       
        //Log::info("User detail while reset password " . $user);
        
        // User not exist
        if( !is_null($email) && $email != null ) {
            $user = User::where('email', $email->email)->first();
            
            if (is_null($user)) {
                Log::info("Not getting detail for ". $email->email);
                return redirect()->back()
                            ->withInput($email->email)
                            ->withErrors(['email' => trans(PasswordBrokerContract::INVALID_USER)]);
            }
            
            // Token not exist
            $tokenData = DB::table('password_resets')->where('email', $email->email)->where('token', $request->token)->first();
            if (is_null($tokenData)) {
                Log::info("Not getting valid token for ". $email->email);
                return redirect()->back()
                            ->withInput($email->email)
                            ->withErrors(['email' => trans(PasswordBrokerContract::INVALID_TOKEN)]);
            }
            
            // Update user's password
        
            $user->password = $credentials['password'];
            $user->save();
            
            // If the user shouldn't reuse the token later, delete the token 
            DB::table('password_resets')->where('email', $user->email)->delete();
            Log::info("Password reset successfully for ". $user->email);
            
            return Redirect::to("/password/reset-success/");
        }
        else{
            return view("auth/passwords/reset");
        }
    }
    public function resetSuccess(Request $request)
    {
        return view("auth/passwords/success");
    }
}
