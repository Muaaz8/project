<?php

namespace App\Http\Controllers\Api\User;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\ReportUsers;
use App\Models\BlockedUsers;
use JWTAuth;
use Hash;
use Mail;
use App\Mail\VerifyEmail;
use App\Models\Product;

class Profile extends Controller
{
    public function index(){
        $id = JWTAuth::user()->id;
        $user = User::find($id);
        return $this->sendResponse($user,'User Retrived Successfully.');
    }

    public function user_info($id)
    {
        $user = User::with(['products','products.user','products.category','products.sub_category','products.photo','products.wishlist'])->find($id);
        return $this->sendResponse($user,'User Retrived Successfully.');
    }

    public function update_user_name(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(),[],401);
        }
        $user_id = JWTAuth::user()->id;

        $user = User::find($user_id);
        $user->name = $request->name;
        $user->save();
        return $this->sendResponse($user,'User Retrived Successfully.');
    }

    public function update_phone_number(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'phone' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(),[],401);
        }
        $user_id = JWTAuth::user()->id;

        $user = User::find($user_id);
        $user->phone = $request->phone;
        $user->save();
        return $this->sendResponse($user,'User Retrived Successfully.');
    }

    public function update_email(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'email' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(),[],401);
        }
        $user_id = JWTAuth::user()->id;

        $user = User::find($user_id);
        $user->email = $request->email;
        $user->save();
        return $this->sendResponse($user,'User Retrived Successfully.');
    }

    public function update_password(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'old_password' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(),[],401);
        }
        $user_id = JWTAuth::user()->id;
        $user = User::find($user_id);

        $credentials['email']    = $user->email;
        $credentials['password']    = $request->old_password;

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Old Password Does not Matched!'], 401);
        }else{
            $user->password = Hash::make($request->password);
            $user->save();
            return $this->sendResponse($user,'User Password Changed Successfully.');
        }
    }

    public function update_location(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'location' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(),[],401);
        }
        $user_id = JWTAuth::user()->id;

        $user = User::find($user_id);
        $user->location = $request->location;
        $user->save();
        return $this->sendResponse($user,'User Retrived Successfully.');
    }

    public function update_custom_link(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'custom_link' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(),[],401);
        }
        $user_id = JWTAuth::user()->id;

        $user = User::find($user_id);
        $user->custom_link = $request->custom_link;
        $user->save();
        return $this->sendResponse($user,'User Retrived Successfully.');
    }

    public function get_all_users()
    {
        $user = User::all();
        return $this->sendResponse($user,'Users Retrived Successfully.');
    }

    public function update_user(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(),[],401);
        }

        $id = $request->user_id;
        $data = $request->all();
        // $data = $request->only('name','provider','provider_id','provider_token','phone','status','email','username','password','src');
        // $data = $request->except('img','src','user_id','email','username','code','email_verified_at','phone_verified_at','');

        if($request->has('email')){
            $validate = Validator::make($request->all(),[
                'email' => 'required|unique:users,email',
            ]);
            if ($validate->fails()) {
                return $this->sendError($validate->errors(),[],401);
            }else{
                $data['email_verified_at'] = null;
            }
        }

        if ($request->has('username')) {
            $validate = Validator::make($request->all(),[
                'username' => 'required|unique:users,username',
            ]);
            if ($validate->fails()) {
                return $this->sendError($validate->errors(),[],401);
            }
        }

        if(isset($request->phone)){
            $data['phone_verified_at'] = null;
        }

        if(isset($request->password)){
            $validate = Validator::make($request->all(),[
                'password' => 'required|min:3',
            ]);
            if ($validate->fails()) {
                return $this->sendError($validate->errors(),[],401);
            }else{
                $data['password'] = Hash::make($request->password);
            }
        }

        if($request->hasfile('img')){
            $image = $request->file('img');
            $extension =  $image->getClientOriginalExtension();
            $filename = Str::random(9) . '-' . Str::uuid() . time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('storage/profile_imgs'), $filename);
            $data['img'] = env('APP_URL')."storage/profile_imgs/{$filename}";
        }

        // if($request->hasfile('src')){
        //     $image = $request->file('src');
        //     $extension =  $image->getClientOriginalExtension();
        //     $filename = Str::random(9) . '-' . Str::uuid() . time() . '.' . $image->getClientOriginalExtension();
        //     $image->move(public_path('storage/profile_src'), $filename);
        //     $data['src'] = env('APP_URL')."storage/profile_src/{$filename}";
        // }

        $user = User::find($id)->update($data);
        if($user){
            $updated_user = User::find($id);
            return $this->sendResponse($updated_user,'User Updated Successfully.');
        }else{
            return $this->sendError('User Not Updated',[],401);
        }
    }

    public function selling_screen(){
        $id = JWTAuth::user()->id;

        $selling = Product::with(['user','category','sub_category','photo','video','wishlist'])->where('user_id',$id)->where('status',1)->get();
        $archive = Product::with(['user','category','sub_category','photo','video','wishlist'])->where('user_id',$id)->where('is_archived',true)->get();
        $purchase = Product::with(['user','category','sub_category','photo','video','wishlist'])->where('user_id',$id)->where('sold_to_user_id',$id)->get();

        $data = ['selling'=>$selling, 'purchase'=> $purchase, 'archive'=> $archive];
        return $this->sendResponse($data,'User Retrived Successfully.');
    }

    public function user_review(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'review_quantity' => 'required|max:5|min:0',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(),[],401);
        }
        $user = User::where('id', $request->user_id)->first();

        if ($user->review_percentage == 0) {
            $rating = $request->review_quantity;
        } else {
            $rating = ($request->review_quantity+$user->review_percentage)/2;
        }
        $user->review_percentage = $rating;
        $user->total_review++;
        $user->save();
        return $this->sendResponse($user,'Review Added Successfully.');
    }

    public function report_user(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(),[],401);
        }

        $report = ReportUsers::create([
            'reporter_user_id' => JWTAuth::user()->id,
            'reported_user_id' => $request->user_id,
        ]);
        return $this->sendResponse($report,'User Reported Successfully.');
    }

    public function list_reported_user(){
        $report = ReportUsers::with(['reporter','reported'])->get();
        return $this->sendResponse($report,'Reported Users Retrived Successfully.');
    }

    public function block_user(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(),[],401);
        }

        $report = BlockedUsers::create([
            'blocker_user_id' => JWTAuth::user()->id,
            'blocked_user_id' => $request->user_id,
        ]);
        return $this->sendResponse($report,'User Reported Successfully.');
    }

    public function unblock_user(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(),[],401);
        }

        $report = BlockedUsers::where('blocker_user_id',JWTAuth::user()->id)->where('blocked_user_id', $request->user_id)->delete();
        return $this->sendResponse($report,'User Unblocked Successfully.');
    }

    public function list_blocked_user(){
        $block = BlockedUsers::with(['blocker','blocked'])->get();
        return $this->sendResponse($block,'Blocked Users Retrived Successfully.');
    }

    public function verify_email(Request $request)
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
            $code = rand(100000, 999999);
            $data->email_code = $code;
            $data->save();
            $mailData = [
                'title' => 'Email Verification Code',
                'body' => $code,
            ];
            Mail::to($data->email)->send(new VerifyEmail($mailData));
              return response()->json([
                  'status' => 'success',
                'msg' => 'We have sent an Email Verification otp code to your email',
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

    public function verify_email_otp(Request $request)
    {
        $validator_a = Validator::make($request->all(), [
            'otp' => 'required',
            'user_id' => 'required',
        ]);
        if ($validator_a->fails()) {
            return response()->json([
                  'status' => 'error',
                  'msg' => $validator_a->errors(),
            ], 401);
        }

        $user = User::find($request->user_id);
        if($user)
        {
            if($user->email_code == $request->otp)
            {
                $user->email_verified_at = date('Y-m-d H:i:s');
                $user->save();
                return response()->json([
                    'status' => 'success',
                    'msg' => 'Email Verified Successfully.',
                ], 200);
            }else{
                return response()->json([
                    'status' => 'error',
                    'msg' => 'Invalid Code.',
                ], 401);
            }
        }else{
            return response()->json([
                'status' => 'error',
                'msg' => 'User Does not exist.',
            ], 401);
        }
    }

}
