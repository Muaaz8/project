<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Chat as Ch;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\ServiceAccount;

class Chat extends Controller
{
    public function send_msg(Request $request)
    {
        $input['sender_id'] = $request->sender_id;
        $input['receiver_id'] = $request->receiver_id;
        $input['message'] = $request->msg;
        $input['status'] = "sent";
        $conversation = Ch::where('from',$request->from)->where('to',$request->to)->first();
        $conversation1 = Ch::where('to',$request->from)->where('from',$request->to)->first();
        if($conversation){
            $input['conversation_id'] = $conversation->conversation_id;
        }elseif ($conversation1) {
            $input['conversation_id'] = $conversation->conversation_id;
        }else{
            $input['conversation_id'] = date('YmdHis');
        }
        $msg = Ch::Create($input);
        $this->firebase($request->to,$request->from,$input);
    }

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

    public function agent_send_message(Request $request)
    {
        $client_id = $request->client_id;
        $imagePaths = [];
        $count = 0;
        $text_msg = $request->send_msg;
        if ($request->hasFile('images')) {
            foreach ($request->file('document') as $image) {
                $path = $image->store('public/images');
                $path = Storage::url($path);
                $filename = $image->getClientOriginalName();

                $response = $this->send_image_msg($path,$client_id,$filename);
                //$response = "success";
            }
        }

        $docsPaths = [];
        if ($request->hasFile('docs')) {
            foreach ($request->file('docs') as $image) {
                $path = $image->store('public/documents');
                $path = Storage::url($path);
                $filename = $image->getClientOriginalName();
                $response = $this->send_docs_msg($path,$client_id,$filename);
                //$response = "success";
                if($response == "success"){
                    $docsPaths[] = $path;
                }
            }
        }

        $audsPaths = [];
        if ($request->hasFile('auds')) {
            foreach ($request->file('auds') as $image) {
                $path = $image->store('public/audio');
                $path = Storage::url($path);
                $filename = $image->getClientOriginalName();
                $response = $this->send_aud_msg($path,$client_id,$filename);
                //$response = "success";
                if($response == "success"){
                    $audsPaths[] = $path;
                }
            }
        }

        $vidsPaths = [];
        if ($request->hasFile('vids')) {
            foreach ($request->file('vids') as $image) {
                $path = $image->store('public/video');
                $path = Storage::url($path);
                $filename = $image->getClientOriginalName();
                $response = $this->send_vid_msg($path,$client_id,$filename);
                //$response = "success";
                if($response == "success"){
                    $vidsPaths[] = $path;
                }
            }
        }

        if($text_msg != null && $text_msg != ""){
            $response = $this->send_text_msg($text_msg,$client_id);
            if($response == "success"){
                $data['msg'] = $text_msg;
            }else{
                $data['msg'] = $response;
            }
        }
        $data['client'] = $request->client_id;
        $data['images'] = $imagePaths;
        $data['docs'] = $docsPaths;
        $data['auds'] = $audsPaths;
        $data['vids'] = $vidsPaths;
        return $data;
    }
}
