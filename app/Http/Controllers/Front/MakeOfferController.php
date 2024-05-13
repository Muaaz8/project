<?php

namespace App\Http\Controllers\Front;

use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\MakeOffer;
use App\Models\User;
use App\Models\Product;
use App\Models\Chat as Ch;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Front\Chat;

class MakeOfferController extends Controller
{
    public function make_offer(Request $request){
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:product,id',
            'seller_id' => 'required|exists:users,id',
            'buyer_id' => 'required|exists:users,id',
            'offer_price' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(),[],401);
        }

        $offer = MakeOffer::create($request->all());
        $conversation = Ch::where('sender_id',$request->buyer_id)->where('receiver_id',$request->seller_id)->first();
        $conversation1 = Ch::where('receiver_id',$request->buyer_id)->where('sender_id',$request->seller_id)->first();
        if($conversation){
            $conversation_id = $conversation->conversation_id;
        }elseif ($conversation1) {
            $conversation_id = $conversation->conversation_id;
        }else{
            $conversation_id = date('YmdHis');
        }
        $sender = User::find($request->buyer_id);
        $receiver = User::find($request->seller_id);
        $prod = Product::find($request->product_id);

        $ch_input['sender_id'] = $sender->id;
        $ch_input['receiver_id'] = $receiver->id;
        $ch_input['message'] = $sender->name." made an offer ".$request->offer_price." for your listed product ".$prod->name;
        $ch_input['status'] = "sent";
        $ch_input['conversation_id'] = $conversation_id;
        $ch_input['offer_id'] = $offer->id;
        $ch_input['product_id'] = $request->product_id;
        $msg = Ch::Create($ch_input);
        $noti_text = $sender->name." made an offer for your listed product.";
        $notification['user_id'] = $receiver->id;
        $notification['text'] = $noti_text;
        $notification['type'] = "conversation";
        $notification['type_id'] = $conversation_id;
        $notification['status'] = "unread";
        $notif = Notification::create($notification);

        $e = new Chat();
        $e->firebase($receiver->id,$sender->id,$ch_input);
        $e->firebase_notification($receiver->id,$notif);

        return $this->sendResponse($offer,"Offer Made placed Successfully");
    }

    public function get_offer(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(),[],401);
        }

        $offer = MakeOffer::with(['product','seller','buyer'])->where('buyer_id',$request->user_id)->where('status',1)->first();
        return $this->sendResponse($offer,"Offer Made placed Successfully");
    }

    public function accept_offer(Request $request){
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:product,id',
            'seller_id' => 'required|exists:users,id',
            'buyer_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(),[],401);
        }

        $offer = MakeOffer::where('product_id',$request->product_id)
            ->where('seller_id',$request->seller_id)
            ->where('buyer_id',$request->buyer_id)
            ->where('status','1')
            ->first();
        $offer->status = 0;
        $offer->save();
        $conversation = Ch::where('sender_id',$request->buyer_id)->where('receiver_id',$request->seller_id)->first();
        $conversation1 = Ch::where('receiver_id',$request->buyer_id)->where('sender_id',$request->seller_id)->first();
        if($conversation){
            $conversation_id = $conversation->conversation_id;
        }elseif ($conversation1) {
            $conversation_id = $conversation->conversation_id;
        }else{
            $conversation_id = date('YmdHis');
        }
        $sender = User::find($request->seller_id);
        $receiver = User::find($request->buyer_id);
        $prod = Product::find($request->product_id);
        $prod->is_sold = "1";
        $prod->sold_to_user_id = $request->buyer_id;
        $prod->save();

        $ch_input['sender_id'] = $sender->id;
        $ch_input['receiver_id'] = $receiver->id;
        $ch_input['message'] = $sender->name." accepted the offer ".$request->offer_price." for your bid to ".$prod->name;
        $ch_input['status'] = "sent";
        $ch_input['conversation_id'] = $conversation_id;
        $msg = Ch::Create($ch_input);
        $noti_text = $sender->name." accepted your bid.";
        $notification['user_id'] = $receiver->id;
        $notification['text'] = $noti_text;
        $notification['type'] = "conversation";
        $notification['type_id'] = $conversation_id;
        $notification['status'] = "unread";
        $notif = Notification::create($notification);

        $e = new Chat();
        $e->firebase($receiver->id,$sender->id,$ch_input);
        $e->firebase_notification($receiver->id,$notif);

        return $this->sendResponse($offer,"Offer Accepted Successfully");
    }

    public function reject_offer(Request $request){
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:product,id',
            'seller_id' => 'required|exists:users,id',
            'buyer_id' => 'required|exists:users,id',
            'offer_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(),[],401);
        }

        $offer = MakeOffer::find($request->offer_id);
        $offer->status = 0;
        $offer->save();
        $conversation = Ch::where('sender_id',$request->buyer_id)->where('receiver_id',$request->seller_id)->first();
        $conversation1 = Ch::where('receiver_id',$request->buyer_id)->where('sender_id',$request->seller_id)->first();
        if($conversation){
            $conversation_id = $conversation->conversation_id;
        }elseif ($conversation1) {
            $conversation_id = $conversation->conversation_id;
        }else{
            $conversation_id = date('YmdHis');
        }
        $sender = User::find($request->seller_id);
        $receiver = User::find($request->buyer_id);
        $prod = Product::find($request->product_id);

        $ch_input['sender_id'] = $sender->id;
        $ch_input['receiver_id'] = $receiver->id;
        $ch_input['message'] = $sender->name." rejected the offer ".$request->offer_price." for your bid to ".$prod->name;
        $ch_input['status'] = "sent";
        $ch_input['conversation_id'] = $conversation_id;
        $msg = Ch::Create($ch_input);
        $noti_text = $sender->name." rejected your bid.";
        $notification['user_id'] = $receiver->id;
        $notification['text'] = $noti_text;
        $notification['type'] = "conversation";
        $notification['type_id'] = $conversation_id;
        $notification['status'] = "unread";
        $notif = Notification::create($notification);

        $e = new Chat();
        $e->firebase($receiver->id,$sender->id,$ch_input);
        $e->firebase_notification($receiver->id,$notif);

        return $this->sendResponse($offer,"Offer Rejected Successfully");
    }
}
