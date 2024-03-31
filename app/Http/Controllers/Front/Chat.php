<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\Chat as Ch;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\ServiceAccount;
use Illuminate\Support\Facades\Validator;

class Chat extends Controller
{
    public function firebase($id,$type,$data)
    { 
        $app = "Chats";
        // Get a reference to the database
        $firebase = (new \Kreait\Firebase\Factory)
        ->withServiceAccount(base_path(env('FIREBASE_CREDENTIALS')))
        ->withDatabaseUri(env('FIREBASE_DATABASE_URL'));
        // Get a reference to the "users" node
        $database = $firebase->createDatabase();
        $blogRef = $database->getReference($app);
        $data['date'] = date('m-d-Y');
        $data['time'] = date('h:i A');
        $blogRef->getChild($id)->getChild($type)->set($data);
    }

    public function send_msg(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sender_id'     => 'required|exists:users,id',
            'receiver_id'   => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(),[],401);
        }

        $images = [];
        $docs = [];
        $text = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('public/images');
                $path = Storage::url($path);
                $filename = $image->getClientOriginalName();
                $input = [];
                $input['sender_id'] = $request->sender_id;
                $input['receiver_id'] = $request->receiver_id;
                $input['file'] = $path;
                $input['file_name'] = $filename;
                $input['file_type'] = "img";
                $input['status'] = "sent";
                $conversation = Ch::where('sender_id',$request->sender_id)->where('receiver_id',$request->receiver_id)->first();
                $conversation1 = Ch::where('receiver_id',$request->sender_id)->where('sender_id',$request->receiver_id)->first();
                if($conversation){
                    $input['conversation_id'] = $conversation->conversation_id;
                }elseif ($conversation1) {
                    $input['conversation_id'] = $conversation->conversation_id;
                }else{
                    $input['conversation_id'] = date('YmdHis');
                }
                $msg = Ch::Create($input);
                $this->firebase($request->receiver_id,$request->sender_id,$input);
                $images[] = $msg;
            }
        }

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $image) {
                $path = $image->store('public/documents');
                $path = Storage::url($path);
                $filename = $image->getClientOriginalName();
                $input = [];
                $input['sender_id'] = $request->sender_id;
                $input['receiver_id'] = $request->receiver_id;
                $input['file'] = $path;
                $input['file_name'] = $filename;
                $input['file_type'] = "doc";
                $input['status'] = "sent";
                $conversation = Ch::where('sender_id',$request->sender_id)->where('receiver_id',$request->receiver_id)->first();
                $conversation1 = Ch::where('receiver_id',$request->sender_id)->where('sender_id',$request->receiver_id)->first();
                if($conversation){
                    $input['conversation_id'] = $conversation->conversation_id;
                }elseif ($conversation1) {
                    $input['conversation_id'] = $conversation->conversation_id;
                }else{
                    $input['conversation_id'] = date('YmdHis');
                }
                $msg = Ch::Create($input);
                $this->firebase($request->receiver_id,$request->sender_id,$input);
                $docs[] = $msg;
            }
        }

        if($request->has('message')){
            $input = [];
            $input['sender_id'] = $request->sender_id;
            $input['receiver_id'] = $request->receiver_id;
            $input['message'] = $request->message;
            $input['status'] = "sent";
            $conversation = Ch::where('sender_id',$request->sender_id)->where('receiver_id',$request->receiver_id)->first();
            $conversation1 = Ch::where('receiver_id',$request->sender_id)->where('sender_id',$request->receiver_id)->first();
            if($conversation){
                $input['conversation_id'] = $conversation->conversation_id;
            }elseif ($conversation1) {
                $input['conversation_id'] = $conversation->conversation_id;
            }else{
                $input['conversation_id'] = date('YmdHis');
            }
            $msg = Ch::Create($input);
            $this->firebase($request->receiver_id,$request->sender_id,$input);
            $text[] = $msg;
        }
        $data['Message'] = $text;
        $data['Documanets'] = $docs;
        $data['Images'] = $images;
        return $this->sendResponse($data,'Message Send Successfully.');
    }
}
