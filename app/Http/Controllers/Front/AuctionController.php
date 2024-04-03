<?php

namespace App\Http\Controllers\Front;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\Auction;
use App\Models\Notification;
use App\Models\User;
use App\Models\Product;
use App\Http\Controllers\Front\Chat;

class AuctionController extends Controller
{
    public function place_bid(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:product,id',
            'price' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(),[],401);
        }

        $bid = Auction::create($request->all());

        $receiver_id = Product::find($request->product_id)->user_id;
        $sender = User::find($request->user_id);
        $receiver = User::find($receiver_id);
        $noti_text = $sender->name." placed a bid on your product.";
        $notification['user_id'] = $receiver_id;
        $notification['text'] = $noti_text;
        $notification['type'] = "auction";
        $notification['type_id'] = null;
        $notification['status'] = "unread";
        $notif = Notification::create($notification);

        $e = new Chat();
        $e->firebase_notification($receiver_id,$notif);
        $e->firebase_auction($request->product_id,$bid);

        return $this->sendResponse($bid,"Bid placed Successfully");
    }

    public function get_placed_bids(Request $request){
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:product,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(),[],401);
        }

        $bid = Auction::with('user')->where('product_id',$request->product_id)->orderBy('price','desc')->get();
        return $this->sendResponse($bid,"Bid placed Successfully");
    }
}
