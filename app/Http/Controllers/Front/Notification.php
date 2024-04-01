<?php

namespace App\Http\Controllers\Front;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Notification as Nt;

class Notification extends Controller
{
    public function get_user_all_notifications($id)
    {
        $type = request('type');
        if($type){
            $notifications = Nt::where('user_id',$id)->where('type',$type)->get();
            return $this->sendResponse($notifications,'All User Notifications Retreived Successfully.');
        }else{
            $notifications = Nt::where('user_id',$id)->get();
            return $this->sendResponse($notifications,'All User Notifications Retreived Successfully.');
        }
    }

    public function get_user_unread_notifications($id)
    {
        $type = request('type');
        if($type){
            $notifications = Nt::where('user_id',$id)->where('type',$type)->where('status','unread')->get();
            return $this->sendResponse($notifications,'All Unread User Notifications Retreived Successfully.');
        }else{
            $notifications = Nt::where('user_id',$id)->where('status','unread')->get();
            return $this->sendResponse($notifications,'All Unread User Notifications Retreived Successfully.');
        }
    }

    public function get_user_read_notifications($id)
    {
        $type = request('type');
        if($type){
            $notifications = Nt::where('user_id',$id)->where('type',$type)->where('status','read')->get();
            return $this->sendResponse($notifications,'All Read User Notifications Retreived Successfully.');
        }else{
            $notifications = Nt::where('user_id',$id)->where('status','read')->get();
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
