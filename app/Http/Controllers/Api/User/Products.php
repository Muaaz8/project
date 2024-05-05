<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use JWTAuth;
use App\Models\User;
use App\Models\Product;
use App\Models\Photo;
use App\Models\Video;
use App\Models\Categories;
use App\Models\Sub_Category;

class Products extends Controller
{
    public function first_step(Request $request)
    {
        $validator_a = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'title' => 'required',
            'description' => 'required',
            // 'video' => 'nullable|file|mimetypes:video/avi,video/mpeg,video/quicktime,video/mp4',
        ]);
        if ($validator_a->fails()) {
            return response()->json([
                'status' => 'error',
                'msg' => $validator_a->errors(),
            ], 401);
        }


        // Generate slug from title
        $slug = Str::slug($request->title);
        $make_sure = Product::where('slug', $slug)->first();
        if ($make_sure) {
            $slug = $slug . '-' . Str::random(9);
        }

        $product = Product::create([
            'user_id' => $request->user_id,
            'title' => $request->title,
            'slug' => $slug,
            'description' => $request->description,
            'status' => 0,
        ]);
        $product_id = $product->id;

        // Video
        if ($request->hasfile('video')) {
            foreach ($request->file('video') as $key => $value) {
                $video = $value;
                $videoName = Str::random(9) . '-' . Str::uuid() . time() . '.' . $video->getClientOriginalExtension();
                $video->storeAs('ads_videos', $videoName, 'public');
                Video::create([
                    'product_id' => $product->id,
                    'src' => env('APP_URL')."storage/ads_videos/{$videoName}",
                ]);
            }

        }

        return response()->json([
            'status' => 'success',
            'msg' => 'Product details added successfully!',
            'product_id' => $product_id,
        ], 200);

    }
    // Second Step
    public function second_step(Request $request)
    {
        $validator_a = Validator::make($request->all(), [
            'product_id' => 'required|exists:product,id',
            'category_id' => 'required|exists:categories,id',
            'condition' => 'required',
        ]);
        if ($validator_a->fails()) {
            return response()->json([
                'status' => 'error',
                'msg' => $validator_a->errors(),
            ], 401);
        }
        $product = Product::where('id', $request->product_id)->first();
        if ($product) {
            $product->category_id = $request->category_id;
            $product->condition = $request->condition;

            if ($request->has('sub_category_id')) {
                $sub_category_id = $request->sub_category_id;
                $make_sure = Sub_Category::where('id', $sub_category_id)->first();
                if ($make_sure) {
                    $product->sub_category_id = $sub_category_id;
                }
            }

            if ($request->has('make_and_model')) {
                $product->make_and_model = $request->make_and_model;
            }
            if ($request->has('mileage')) {
                $product->mileage = $request->mileage;
            }
            if ($request->has('color')) {
                $product->color = $request->color;
            }
            if ($request->has('brand')) {
                $product->brand = $request->brand;
            }
            if ($request->has('model')) {
                $product->model = $request->model;
            }
            if ($request->has('edition')) {
                $product->edition = $request->edition;
            }
            if ($request->has('authenticity')) {
                $product->authenticity = $request->authenticity;
            }

            $product->save();
            return response()->json([
                'status' => 'success',
                'msg' => 'Product details added successfully!',
                'product_id' => $request->product_id,
            ], 200);
        }
    }

    // Thrid Step
    public function third_step(Request $request)
    {
        $validator_a = Validator::make($request->all(), [
            'product_id' => 'required|exists:product,id',
        ]);
        if ($validator_a->fails()) {
            return response()->json([
                'status' => 'error',
                'msg' => $validator_a->errors(),
            ], 401);
        }
        $product = Product::where('id', $request->product_id)->first();
        if ($product) {
            if ($request->has('fix_price')) {
                $product->fix_price = $request->fix_price;
                if ($request->has('firm_on_price')) {
                    $product->firm_on_price = 1;
                } else {
                    $product->firm_on_price = 0;
                }
                $product->save();
            } else if ($request->has('auction_price')) {
                $product->auction_price = $request->auction_price;
                $validator_b = Validator::make($request->all(), [
                    'starting_date' => 'required',
                    'starting_time' => 'required',
                    'ending_date' => 'required',
                    'ending_time' => 'required',
                ]);
                if ($validator_b->fails()) {
                    return response()->json([
                        'status' => 'error',
                        'msg' => $validator_b->errors(),
                    ], 401);
                }
                $product->starting_date = $request->starting_date;
                $product->starting_time = $request->starting_time;
                $product->ending_date = $request->ending_date;
                $product->ending_time = $request->ending_time;
                $product->save();
            } else if ($request->has('sell_to_us')) {
                $product->sell_to_us = 1;
                $product->save();
            }else{
                return response()->json([
                    'status' => 'error',
                    'msg' => 'Some went wrong!',
                ], 401);
            }
            return response()->json([
                'status' => 'success',
                'msg' => 'Product details added successfully!',
                'product_id' => $request->product_id,
            ], 200);
        }
    }

    // Last Step
    public function last_step(Request $request)
    {
         $validator_a = Validator::make($request->all(), [
             'product_id' => 'required|exists:product,id',
             'location' => 'required',
         ]);
         if ($validator_a->fails()) {
             return response()->json([
                 'status' => 'error',
                 'msg' => $validator_a->errors(),
             ], 401);
         }
         $product = Product::where('id', $request->product_id)->first();
         if ($product) {
            $product->location = $request->location;
            $product->status = 1;
            $product->save();
            return response()->json([
                'status' => 'success',
                'msg' => 'Product is live now!',
                'product_id' => $request->product_id,
                'data' => $product
            ], 200);
         }
    }

    public function upload_image(Request $request)
    {
        $validator_a = Validator::make($request->all(), [
            'product_id' => 'required|exists:product,id',
            'src' => 'required',
        ]);
        if ($validator_a->fails()) {
            return response()->json([
                'status' => 'error',
                'msg' => $validator_a->errors(),
            ], 401);
        }
        $product = Product::where('id', $request->product_id)->first();
        if ($product) {
            if ($request->hasfile('src')) {
                foreach ($request->file('src') as $key => $value) {
                    $image = $value;
                    $extension =  $image->getClientOriginalExtension();
                    $filename = Str::random(9) . '-' . Str::uuid() . time() . '.' . $image->getClientOriginalExtension();
                    $image->move(public_path('storage/ads_imgs'), $filename);
                    Photo::create([
                        'product_id' => $product->id,
                        'src' => env('APP_URL')."storage/ads_imgs/{$filename}",
                    ]);
                }
            }
            $product = Product::with('photo')->where('id', $request->product_id)->first();
            return response()->json([
                'status' => 'success',
                'msg' => 'Image added to product!',
                'product_id' => $request->product_id,
                'data' => $product
            ], 200);
        }else{
            return response()->json([
                'status' => 'error',
                'msg' => 'Product id not found!',
            ], 401);
        }
    }

    public function featured_products(Request $request){
        $query = Product::with(['user','category','sub_category','photo','video','wishlist' => function($query) {
            $query->where('user_id', JWTAuth::user()->id); // Replace $specificWishlistId with the ID you want to filter
        }])
        ->where('fix_price','!=',null)
        ->where('status','1')
        ->where('is_archived',false)
        ->where('is_sold',false);

        if ($request->filled('id')) {
            $query->where('id', $request->id);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('sub_category_id')) {
            $query->where('sub_category_id', $request->sub_category_id);
        }

        if ($request->filled('search')) {
            $query->where('title', 'LIKE', "%{$request->search}%");
        }

        if ($request->filled('limit')) {
            $query->limit($request->limit);
        }

        if ($request->filled('location')) {
            $query->where('location','LIKE',"%{$request->location}%");
        }
        if ($request->filled('sort_by')) {
            if(Str::lower($request->sort_by) == "newest on top"){
                $query->orderby('id','desc');
            }elseif(Str::lower($request->sort_by) == "newest on bottom"){
                $query->orderby('id','asc');
            }elseif(Str::lower($request->sort_by) == "lowest price on top"){
                $query->orderby('fix_price','asc');
            }elseif(Str::lower($request->sort_by) == "lowest price on bottom"){
                $query->orderby('fix_price','desc');
            }
        }
        if ($request->filled('is_urgert')) {
            $query->where('is_urgert',$request->is_urgert);
        }
        if ($request->filled('min_price')) {
            $query->where('fix_price','>=',$request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('fix_price','<=',$request->max_price);
        }

        $featured_products = $query->get();

        return $this->sendResponse($featured_products,'Featured Products Retrived Successfully.');
    }

    public function auction_products(Request $request){
        $query = Product::with(['user','category','sub_category','photo','video','auction','wishlist' => function($query) {
            $query->where('user_id', JWTAuth::user()->id); // Replace $specificWishlistId with the ID you want to filter
        }])
        ->where('auction_price','!=',null)
        ->where('status','1')
        ->where('is_archived',false)
        ->where('is_sold',false);

        if ($request->filled('id')) {
            $query->where('id', $request->id);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('sub_category_id')) {
            $query->where('sub_category_id', $request->sub_category_id);
        }

        if ($request->filled('search')) {
            $query->where('title', 'LIKE', "%{$request->search}%");
        }

        if ($request->filled('limit')) {
            $query->limit($request->limit);
        }

        if ($request->filled('location')) {
            $query->where('location','LIKE',"%{$request->location}%");
        }
        if ($request->filled('sort_by')) {
            if(Str::lower($request->sort_by) == "newest on top"){
                $query->orderby('id','desc');
            }elseif(Str::lower($request->sort_by) == "newest on bottom"){
                $query->orderby('id','asc');
            }elseif(Str::lower($request->sort_by) == "lowest price on top"){
                $query->orderby('auction_price','asc');
            }elseif(Str::lower($request->sort_by) == "lowest price on bottom"){
                $query->orderby('auction_price','desc');
            }
        }
        if ($request->filled('is_urgent')) {
            $query->where('is_urgent',$request->is_urgert);
        }
        if ($request->filled('min_price')) {
            $query->where('auction_price','>=',$request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('auction_price','<=',$request->max_price);
        }

        $auction_products = $query->get();

        return $this->sendResponse($auction_products,'Auction Products Retrived Successfully.');
    }

    public function product_review(Request $request){
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:product,id',
            'review_quantity' => 'required|max:5|min:0',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(),[],401);
        }
        $product = Product::where('id', $request->product_id)->first();

        if ($product->review_percentage == 0) {
            $rating = $request->review_quantity;
        } else {
            $rating = ($request->review_quantity+$product->review_percentage)/2;
        }
        $product->review_percentage = $rating;
        $product->total_review++;
        $product->save();
        return $this->sendResponse($product,'Review Added Successfully.');
    }

    public function mark_product_sold($id){
        $product = Product::find($id);
        if(!$product) {
            return $this->sendError('Product not Found',[],401);
        }

        $product->status = "0";
        $product->is_archived = false;
        $product->is_sold = true;
        $product->save();
        return $this->sendResponse($product,'Product Marked Sold Successfully.');
    }

    public function mark_product_archive($id){
        $product = Product::find($id);
        if(!$product) {
            return $this->sendError('Product not Found',[],401);
        }

        $product->status = "0";
        $product->is_archived = true;
        $product->is_sold = false;
        $product->save();
        return $this->sendResponse($product,'Product Marked Archived Successfully.');
    }

    public function mark_product_unarchive($id){
        $product = Product::find($id);
        if(!$product) {
            return $this->sendError('Product not Found',[],401);
        }

        $product->status = "1";
        $product->is_archived = false;
        $product->is_sold = false;
        $product->save();
        return $this->sendResponse($product,'Product Unarchived Successfully.');
    }

    public function all_products(Request $request){
        $query = Product::with(['user','category','sub_category','photo','video','wishlist' => function($query) {
            $query->where('user_id', JWTAuth::user()->id); // Replace $specificWishlistId with the ID you want to filter
        }])
        ->where('status','1')
        ->where('is_archived',false)
        ->where('is_sold',false);

        if ($request->filled('id')) {
            $query->where('id', $request->id);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('sub_category_id')) {
            $query->where('sub_category_id', $request->sub_category_id);
        }

        if ($request->filled('search')) {
            $query->where('title', 'LIKE', "%{$request->search}%");
        }

        if ($request->filled('limit')) {
            $query->limit($request->limit);
        }

        if ($request->filled('location')) {
            $query->where('location','LIKE',"%{$request->location}%");
        }
        if ($request->filled('sort_by')) {
            if(Str::lower($request->sort_by) == "newest on top"){
                $query->orderby('id','desc');
            }elseif(Str::lower($request->sort_by) == "newest on bottom"){
                $query->orderby('id','asc');
            }elseif(Str::lower($request->sort_by) == "lowest price on top"){
                $query->orderby('fix_price','asc');
            }elseif(Str::lower($request->sort_by) == "lowest price on bottom"){
                $query->orderby('fix_price','desc');
            }
        }
        if ($request->filled('is_urgert')) {
            $query->where('is_urgert',$request->is_urgert);
        }
        if ($request->filled('min_price')) {
            $query->where('fix_price','>=',$request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('fix_price','<=',$request->max_price);
        }

        $featured_products = $query->get();

        return $this->sendResponse($featured_products,'All Products Retrived Successfully.');
    }

    public function increase_product_view(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:product,id',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(),[],401);
        }

        $prod = Product::find($request->product_id);
        if($prod->views_count == null)
        {
            $prod->views_count = 1;
            $prod->save();
        }else{
            $prod->views_count = (int)$prod->views_count + 1;
            $prod->save();
        }

        return $this->sendResponse($prod,'Product View Increased Successfully.');
    }
}
