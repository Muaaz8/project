<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\Auth\Main;
use App\Http\Controllers\Api\User\Category;
use App\Http\Controllers\Api\User\Products;
use App\Http\Controllers\Front\Chat;
use App\Http\Controllers\Front\Notification;
use App\Http\Controllers\Front\AuctionController;
use App\Http\Controllers\Front\MakeOfferController;
use App\Http\Controllers\Api\User\WishlistController;
use App\Http\Controllers\Api\User\Profile;
use App\Http\Controllers\Api\User\Payment;

use App\Http\Controllers\Api\Admin\CategoryA;
use App\Http\Controllers\Api\Admin\UserA;
use App\Http\Controllers\Api\Admin\ProductA;
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

Route::group(['middleware' => 'jwt.auth'], function () {

    //User Profile Routes
    Route::get('/me',[Profile::class, 'index'])->name('my_info');
    Route::get('/user/info/{id}',[Profile::class, 'user_info'])->name('user_info');
    Route::get('/get/all/users',[Profile::class, 'get_all_users'])->name('get_all_users');
    Route::post('/update/user',[Profile::class, 'update_user'])->name('update_user');
    Route::post('/update/user/name',[Profile::class, 'update_user_name'])->name('update_user_name');
    Route::post('/update/email',[Profile::class, 'update_email'])->name('update_email');
    Route::post('/update/phone/number',[Profile::class, 'update_phone_number'])->name('update_phone_number');
    Route::post('/update/password',[Profile::class, 'update_password'])->name('update_password');
    Route::post('/update/location',[Profile::class, 'update_location'])->name('update_location');
    Route::post('/update/custom/link',[Profile::class, 'update_custom_link'])->name('update_custom_link');
    Route::post('/verify-email',[Profile::class, 'verify_email'])->name('verify_email');
    Route::post('/verify-email-otp',[Profile::class, 'verify_email_otp'])->name('verify_email_otp');

    // Listing Products Routes
    Route::post('/categories', [Category::class,'index']);
    Route::post('/sub-categories', [Category::class,'sub_category']);
    Route::post('/featured-products', [Products::class,'featured_products']);
    Route::post('/auction-products', [Products::class,'auction_products']);
    Route::post('/get-all-products', [Products::class,'all_products']);

    // Product upload steps
    Route::post('/add-product-first-step', [Products::class,'first_step']);
    Route::post('/add-product-second-step', [Products::class,'second_step']);
    Route::post('/add-product-third-step', [Products::class,'third_step']);
    Route::post('/add-product-last-step', [Products::class,'last_step']);
    Route::post('/edit-product-first-step', [Products::class,'edit_product_first_step']);
    Route::post('/edit-product-second-step', [Products::class,'second_step']);
    Route::post('/edit-product-third-step', [Products::class,'third_step']);
    Route::post('/edit-product-last-step', [Products::class,'last_step']);
    Route::post('/upload-image', [Products::class,'upload_image']);
    Route::post('/delete-image', [Products::class,'delete_photo']);

    // Chatting Routes
    Route::middleware(['blockeduser'])->group(function () {
        Route::post('send_msg', [Chat::class, 'send_msg'])->name('send_msg');
    });
    Route::post('/get/conversation_id', [Chat::class, 'get_conversation_id'])->name('get_conversation_id');
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

    // Make Offer Routes
    Route::post('/make-offer', [MakeOfferController::class,'make_offer']);
    Route::post('/get-offer', [MakeOfferController::class,'get_offer']);
    Route::post('/accept-offer', [MakeOfferController::class,'accept_offer']);
    Route::post('/reject-offer', [MakeOfferController::class,'reject_offer']);

    //Add Review to Product Routes
    Route::post('/product-review',[Products::class,'product_review']);
    Route::post('/user-review',[Profile::class,'user_review']);

    //Payment API
    Route::post('/sell-faster', [Payment::class,'sell_faster'])->name('sell_faster');
    Route::post('/charge', [Payment::class,'charge'])->name('charge');
    Route::get('/get/user/all/transactions/{id}', [Payment::class,'get_user_all_trans'])->name('get_user_all_trans');
    Route::get('/get/all/transactions', [Payment::class,'get_all_trans'])->name('get_all_trans');
    Route::get('/get/transaction/{id}', [Payment::class,'get_trans'])->name('get_trans');


    Route::get('/mark-product-sold/{id}',[Products::class,'mark_product_sold']);
    Route::get('/mark-product-archive/{id}',[Products::class,'mark_product_archive']);
    Route::get('/mark-product-unarchive/{id}',[Products::class,'mark_product_unarchive']);
    Route::get('/selling-screen',[Profile::class,'selling_screen']);
    Route::post('/increase-product-view',[Products::class,'increase_product_view']);

    Route::post('/report-a-user',[Profile::class,'report_user']);
    Route::get('/list-report-a-user',[Profile::class,'list_reported_user']);

    Route::post('/block-a-user',[Profile::class,'block_user']);
    Route::post('/unblock-a-user',[Profile::class,'unblock_user']);
    Route::get('/list-block-a-user',[Profile::class,'list_blocked_user']);
    
    Route::get('/get-payment-status',[Profile::class,'list_blocked_user']);
    Route::get('/list-block-a-user',[Profile::class,'list_blocked_user']);

    
    
});

Route::get('/payment-status',[Profile::class,'payment_status']);
Route::get('/payment-fee',[Profile::class,'payment_fee']);

// Admin Apis
Route::post('/admin/upload/settings',[UserA::class,'upload_setting']);
Route::post('/admin/add-category',[CategoryA::class, 'add_category']);
Route::post('/admin/update-category',[CategoryA::class, 'edit_category']);
Route::post('/admin/update-user-data',[UserA::class, 'update_user']);
Route::post('/admin/update-product',[ProductA::class, 'update_product']);