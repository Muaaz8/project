<?php

namespace App\Http\Controllers\Api\User;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\User;
use JWTAuth;
use Hash;

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
}