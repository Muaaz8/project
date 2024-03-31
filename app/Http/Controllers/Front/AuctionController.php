<?php

namespace App\Http\Controllers\Front;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\Auction;
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
        $e = new Chat();
        $e->firebase($request->product_id,$request->user_id,$bid);
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
