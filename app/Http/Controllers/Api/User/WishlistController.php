<?php

namespace App\Http\Controllers\Api\User;

use App\Models\Wishlist;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $wishlist_products = Wishlist::with(['user','product','product.photo'])->where('user_id',$request->user_id)->get();
        return $this->sendResponse($wishlist_products,'Wishlist Products Retrived Successfully.');
    }

    public function store(Request $request)
    {
        $wishlist_products = WishList::create([
            'user_id' => $request->user_id,
            'product_id' => $request->product_id,
        ]);
        return $this->sendResponse($wishlist_products,'Wishlist Products Stored Successfully.');
    }
    public function destroy(Request $request)
    {
        $wishlist_products = Wishlist::find($request->id)->delete();
        return $this->sendResponse([],'Wishlist Products Deleted Successfully.');
    }
}
