<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\Chat as Ch;
use App\Models\Notification;
use App\Models\User;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\ServiceAccount;
use Illuminate\Support\Facades\Validator;
use App\Models\BlockedUsers;
use DB;

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

    public function firebase_notification($id,$data)
    {
        $app = "Notifications";
        // Get a reference to the database
        $firebase = (new \Kreait\Firebase\Factory)
        ->withServiceAccount(base_path(env('FIREBASE_CREDENTIALS')))
        ->withDatabaseUri(env('FIREBASE_DATABASE_URL'));
        // Get a reference to the "users" node
        $database = $firebase->createDatabase();
        $blogRef = $database->getReference($app);
        $data['date'] = date('m-d-Y');
        $data['time'] = date('h:i A');
        $blogRef->getChild($id)->set($data);
    }

    public function firebase_auction($id,$data)
    {
        $app = "Auction";
        // Get a reference to the database
        $firebase = (new \Kreait\Firebase\Factory)
        ->withServiceAccount(base_path(env('FIREBASE_CREDENTIALS')))
        ->withDatabaseUri(env('FIREBASE_DATABASE_URL'));
        // Get a reference to the "users" node
        $database = $firebase->createDatabase();
        $blogRef = $database->getReference($app);
        $data['date'] = date('m-d-Y');
        $data['time'] = date('h:i A');
        $blogRef->getChild($id)->set($data);
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
                $input['file'] = "https://ttoffer.com".$path;
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
                $input['file'] = "https://ttoffer.com".$path;
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
        if($text!=[] || $docs!=[] || $images!=[]){
            $sender = User::find($request->sender_id);
            $convo  = Ch::where('sender_id',$request->sender_id)->where('receiver_id',$request->receiver_id)->first();
            $noti_text = "You have got a new message from ".$sender->name;
            $notification['user_id'] = $request->receiver_id;
            $notification['text'] = $noti_text;
            $notification['type'] = "conversation";
            $notification['type_id'] = $convo->conversation_id;
            $notification['status'] = "unread";
            $notif = Notification::create($notification);
            $this->firebase_notification($request->receiver_id,$notif);
        }
        return $this->sendResponse($data,'Message Send Successfully.');
    }

    public function get_all_chats_of_user($id)
    {
        // $chats = Ch::where('sender_id',$id)->orwhere('receiver_id',$id)->groupby('conversation_id')->get();
        $chats = Ch::whereIn('id', function($query) use ($id) {
            $query->select(DB::raw('MAX(id)'))
                  ->from('chats')
                  ->where('sender_id', $id)
                  ->orWhere('receiver_id', $id)
                  ->groupBy('conversation_id');
        })
        ->orderBy('created_at', 'desc')
        ->get();

        foreach ($chats as $key => $chat) {
            $chat->sender = User::find($chat->sender_id);
            $chat->receiver = User::find($chat->receiver_id);
            if($id == $chat->sender->id){
                $chat->user_image = $chat->receiver->img;
            }else{
                $chat->user_image = $chat->sender->img;
            }
            $block = BlockedUsers::where('blocker_user_id',$chat->sender->id)->where('blocked_user_id',$chat->receiver->id)->first();
            $blocked = BlockedUsers::where('blocker_user_id',$chat->receiver->id)->where('blocked_user_id',$chat->sender->id)->first();
            if($block){
               $chat->block = 1;
            }elseif ($blocked) {
                $chat->block = 1;
            }else{
                $chat->block = 0;
            }
        }
        return $this->sendResponse($chats,'Retreived All Chats of user Successfully.');
    }

    public function get_conversation_id(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sender_id'     => 'required|exists:users,id',
            'receiver_id'   => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(),[],401);
        }

        $chat = Ch::where('sender_id',$request->sender_id)->where('receiver_id',$request->receiver_id)->first();
        $chat1 = Ch::where('sender_id',$request->receiver_id)->where('receiver_id',$request->sender_id)->first();
        if($chat){
            $conversation_id = $chat->conversation_id;
        }elseif($chat1){
            $conversation_id = $chat->conversation_id;
        }else{
            $conversation_id = date('YmdHis');
        }

        return $this->sendResponse($conversation_id,'Retreived Conversation of user Successfully.');
    }

    public function get_conversation($conversation_id)
    {
        $conversation = Ch::where('conversation_id',$conversation_id)->get();
        $msg = Ch::where('conversation_id',$conversation_id)->first();
        $user1 = User::find($msg->sender_id);
        $user2 = User::find($msg->receiver_id);
        $data['conversation'] = $conversation;
        $data['Participant1'] = $user1;
        $data['Participant2'] = $user2;
        return $this->sendResponse($data,'Retreived Conversation of user Successfully.');
    }

    public function delete_message($id)
    {
        $Message = Ch::find($id);
        $Message->delete();
        return $this->sendResponse($Message,'Message Deleted Successfully.');
    }

    public function delete_conversation($id)
    {
        $conversation = Ch::where('conversation_id',$id)->delete();
        return $this->sendResponse($conversation,'Conversation Deleted Successfully.');
    }
}
