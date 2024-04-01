<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\Auth\Main;
use App\Http\Controllers\Api\User\Category;
use App\Http\Controllers\Api\User\Products;
use App\Http\Controllers\Front\Chat;
use App\Http\Controllers\Front\Notification;
use App\Http\Controllers\Front\AuctionController;
use App\Http\Controllers\Api\User\WishlistController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::post('/signup', [Main::class,'signup']);
Route::post('/login-email', [Main::class,'login_with_email']);
Route::post('/login-phone', [Main::class,'login_with_username']);

Route::post('/forgot-password', [Main::class,'forgot_pass_p']);
Route::post('/otp-verify', [Main::class,'reset_code']);
Route::post('/new-password', [Main::class,'new_password']);

// Listing Products Routes
Route::post('/categories', [Category::class,'index']);
Route::post('/sub-categories', [Category::class,'sub_category']);
Route::post('/featured-products', [Products::class,'featured_products']);
Route::post('/auction-products', [Products::class,'auction_products']);

// Product upload steps
Route::post('/add-product-first-step', [Products::class,'first_step']);
Route::post('/add-product-second-step', [Products::class,'second_step']);
Route::post('/add-product-third-step', [Products::class,'third_step']);
Route::post('/add-product-last-step', [Products::class,'last_step']);
Route::post('/upload-image', [Products::class,'upload_image']);

// Chatting Routes
Route::post('send_msg', [Chat::class, 'send_msg'])->name('send_msg');
Route::get('/get/user/all/chats/{id}', [Chat::class, 'get_all_chats_of_user'])->name('get_all_chats_of_user');
Route::get('/get/conversation/{conversation_id}', [Chat::class, 'get_conversation'])->name('get_conversation');
Route::get('/delete/message/{message_id}', [Chat::class, 'delete_message'])->name('delete_message');
Route::get('/delete/conversation/{conversation_id}', [Chat::class, 'delete_conversation'])->name('delete_conversation');

//Notification Routes
Route::get('/get/user/all/notifications/{id}',[Notification::class, 'get_user_all_notifications'])->name('get_user_all_notifications');
Route::get('/get/user/unread/notifications/{id}',[Notification::class, 'get_user_unread_notifications'])->name('get_user_unread_notifications');
Route::get('/get/user/read/notifications/{id}',[Notification::class, 'get_user_read_notifications'])->name('get_user_read_notifications');
Route::get('/change/notification/status/{id}', [Notification::class, 'change_notification_status'])->name('change_notification_status');
Route::get('/delete/single/notification/{id}', [Notification::class, 'delete_single_notification'])->name('delete_single_notification');
Route::post('/delete/notifications', [Notification::class, 'delete_notifications'])->name('delete_notifications');

// Wishlist Routes
Route::post('/wishlist-products', [WishlistController::class,'index']);
Route::post('/add-wishlist-products', [WishlistController::class,'store']);
Route::post('/remove-wishlist-products', [WishlistController::class,'destroy']);

// Auction Routes
Route::post('/place-bid', [AuctionController::class,'place_bid']);
Route::post('/get-placed-bids', [AuctionController::class,'get_placed_bids']);
