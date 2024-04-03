<?php

namespace App\Http\Controllers\Front;

use Illuminate\Http\Request;
use App\Models\MakeOffer;

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
        $noti_text = $sender->name." made an offer for your listed product.";
        $notification['user_id'] = $receiver->id;
        $notification['text'] = $noti_text;
        $notification['type'] = "offer";
        $notification['type_id'] = $conversation_id;
        $notification['status'] = "unread";
        $notif = Notification::create($notification);

        $e = new Chat();
        $e->firebase_notification($receiver_id,$notif);

        return $this->sendResponse($offer,"Offer Made placed Successfully");
    }

    public function get_offer(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(),[],401);
        }

        $offer = MakeOffer::with(['product','seller','buyer'])->where('buyer_id',$request->user_id)->first();
        return $this->sendResponse($offer,"Offer Made placed Successfully");
    }
}
