<?php

namespace App\Http\Controllers\APi\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Session;
use Hash;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use JWTAuth;
use Illuminate\Support\Str;
use Mail;
use App\Mail\Reset;
class Main extends Controller
{
    public function reset_code(Request $request)
    {
        $validator_a = Validator::make($request->all(), [
            'email' => 'required',
            'otp' => 'required',
        ]);
        if ($validator_a->fails()) {
            return response()->json([
                'status' => 'error',
                'msg' => $validator_a->errors(),
            ], 401);
        }
        $email = $request->email;
        $sql = DB::select("SELECT * FROM users where code=$request->otp AND email='$email'");
        if (count($sql) > 0) {
            $sql = DB::select("UPDATE `users` SET code=0 WHERE email='$email'");
            $data = User::where('email',$email)->first();
              return response()->json([
                'status' => 'success',
                'msg' => 'OTP verified, Redirecting...',
                'data' => $data,
              ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'msg' =>"You've entered incorrect code!",
            ], 401);
        }

    }

    public function new_password(Request $request)
    {
        $validator_a = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);
        if ($validator_a->fails()) {
            return response()->json([
                'status' => 'error',
                'msg' => $validator_a->errors(),
            ], 401);
        }
        $email = $request->email;
        $data = User::where('email',$email)->first();
        if(!$data){
            return response()->json([
                'status' => 'error',
                'msg' => 'User not found!',
            ], 401);
        }
            DB::table('users')
                ->where('email', $email)
                ->update(['password' => Hash::make($request->password)]);
              return response()->json([
                'status' => 'success',
                'msg' => 'Your password has been updated.',
                'data' => $data,
              ], 200);

    }
    public function forgot_pass_p(Request $request)
      {
          $validator_a = Validator::make($request->all(), [
              'email' => 'required|email',
          ]);
          if ($validator_a->fails()) {
              return response()->json([
                    'status' => 'error',
                    'msg' => $validator_a->errors(),
              ], 401);
          }
          $data = User::where('email', $request->email)
              ->first();
          if ($data) {
                  if($data->provider!=='site'){
              return response()->json([
                'status' => 'error',
                'msg' => "This email utilizes a different login method!",
              ], 401);
                  }
              $code = rand(100000, 999999);
              $data->code = $code;
              $data->save();
              $mailData = [
                  'title' => 'Password Reset Code',
                  'body' => $code,
              ];
              Mail::to($data->email)->send(new Reset($mailData));
                return response()->json([
                    'status' => 'success',
                  'msg' => 'We have sent a password reset otp code to your email',
                  'otp' => $code,
                  'data' => $data,
                ], 200);
          } else {
              return response()->json([
                'status' => 'error',
                'msg' => "Email doesn't exist!",
              ], 401);
          }
      }

    public function signup(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'username' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:3',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 401); // Bad Request
        }

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'status' => 1,
            'provider' => 'site',
            'src' => 'app',
            'password' => Hash::make($request->password),
        ]);
        $data = User::where('id',$user->id)->first();
            return response()->json([
                'status' => 'success',
                'data' => $data,
                'message' => 'Account registered, redirecting...',
            ], 200); // Created
    }

    public function login_with_username(Request $request){
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 401); // Unprocessable Entity
        }

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Login details are not valid!',
            ], 401); // Unauthorized
        }

        if ($user->provider !== 'site') {
            return response()->json([
                'status' => 'error',
                'message' => 'This account utilizes a different login method!',
            ], 401); // Unauthorized
        }

        if (intval($user->status) !== 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'Your account is blocked by admin!',
            ], 401); // Forbidden
        }
        $credentials['phone'] = $request->phone;
        $credentials['password'] = $request->password;

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }

        $return['user']   = $user;
        $return['token']  = $token;

        // if (!Auth::attempt($credentials)) {
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'Login details are not valid!',
        //     ], 401); // Unauthorized
        // }

        return response()->json([
            'status' => 'success',
            'message' => 'You are logged in successfully.',
            'data' => $return,
        ], 200); // OK
    }

    public function login_with_email(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 401); // Unprocessable Entity
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Login details are not valid!',
            ], 401); // Unauthorized
        }

        if ($user->provider !== 'site') {
            return response()->json([
                'status' => 'error',
                'message' => 'This account utilizes a different login method!',
            ], 401); // Unauthorized
        }

        if (intval($user->status) !== 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'Your account is blocked by admin!',
            ], 401); // Forbidden
        }

        $credentials['email']    = $request->email;
        $credentials['password'] = $request->password;

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }

        $return['user']   = $user;
        $return['token']  = $token;

        // if (!Auth::attempt($request->only('email', 'password'))) {
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'Login details are not valid!',
        //     ], 401); // Unauthorized
        // }

        return response()->json([
            'status' => 'success',
            'message' => 'You are logged in successfully.',
            'data' => $return,
        ], 200); // OK
    }
}
