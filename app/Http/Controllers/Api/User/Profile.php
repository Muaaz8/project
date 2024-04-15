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
        $user = User::find($id);
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
        $data = $request->only('name','provider','provider_id','provider_token','phone','status','email','username','password','src');
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

        $sold = Product::where('user_id',$id)->where('is_sold',true)->get();
        $archive = Product::where('user_id',$id)->where('is_archived',true)->get();
        $purchase = Product::where('user_id',$id)->where('sold_to_user_id',$id)->get();

        $data = ['sold'=>$sold, 'purchase'=> $purchase, 'archive'=> $archive];
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

    public function list_blocked_user(){
        $block = BlockedUsers::with(['blocker','blocked'])->get();
        return $this->sendResponse($block,'Blocked Users Retrived Successfully.');
    }

}
