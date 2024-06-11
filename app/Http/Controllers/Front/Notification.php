<?php

namespace App\Http\Controllers\Front;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Notification as Nt;
use Illuminate\Support\Facades\Validator;

class Notification extends Controller
{
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

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'           => 'required|exists:users,id',
            'text'              => 'required',
            'type'              => 'required',
            'type_id'           => 'required',
            'status'            => 'required',
        ]);

        $data = $request->all();
        $notif = Nt::create($data);
        if($notif){
            $this->firebase_notification($request->user_id,$notif);
            return $this->sendResponse($data,'Notification Created Successfully.');
        }else{
            return $this->sendError('Notification is not created');
        }


    }

    public function get_user_all_notifications($id)
    {
        $type = request('type');
        if($type){
            $notifications = Nt::with(['user'])->where('user_id',$id)->where('type',$type)->get();
            return $this->sendResponse($notifications,'All User Notifications Retreived Successfully.');
        }else{
            $notifications = Nt::with(['user'])->where('user_id',$id)->get();
            return $this->sendResponse($notifications,'All User Notifications Retreived Successfully.');
        }
    }

    public function get_user_unread_notifications($id)
    {

        $type = request('type');
        if($type){
            $notifications = Nt::with(['user'])->where('user_id',$id)->where('type',$type)->where('status','unread')->get();
            return $this->sendResponse($notifications,'All Unread User Notifications Retreived Successfully.');
        }else{
            $notifications = Nt::with(['user'])->where('user_id',$id)->where('status','unread')->get();
            return $this->sendResponse($notifications,'All Unread User Notifications Retreived Successfully.');
        }
    }

    public function get_user_read_notifications($id)
    {
        $type = request('type');
        if($type){
            $notifications = Nt::with(['user'])->where('user_id',$id)->where('type',$type)->where('status','read')->get();
            return $this->sendResponse($notifications,'All Read User Notifications Retreived Successfully.');
        }else{
            $notifications = Nt::with(['user'])->where('user_id',$id)->where('status','read')->get();
            return $this->sendResponse($notifications,'All Read User Notifications Retreived Successfully.');
        }
    }

    public function change_notification_status($id)
    {
        $notification = Nt::find($id);
        $notification->status = 'read';
        $notification->save();
        return $this->sendResponse($notification, 'Notification Status Updated Successfully.');
    }

    public function delete_single_notification($id)
    {
        $notification = Nt::find($id)->delete();
        return $this->sendResponse($notification, 'Notification Deleted Successfully.');
    }

    public function delete_notifications(Request $request)
    {
        $ids = $request->ids;
        if($ids!=null){
            $notification = Nt::whereIn('id',$ids)->delete();
            return $this->sendResponse($notification, 'All selected Notifications Deleted Successfully.');
        }else{
            return $this->sendResponse($ids, 'You have not selected notifications to be deleted.');
        }
    }
}
