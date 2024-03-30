<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\Auth\Main;
use App\Http\Controllers\Api\User\Category;
use App\Http\Controllers\Api\User\Products;
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

Route::post('/categories', [Category::class,'index']);
Route::post('/sub-categories', [Category::class,'sub_category']);
// Product upload steps
Route::post('/add-product-first-step', [Products::class,'first_step']);
Route::post('/add-product-second-step', [Products::class,'second_step']);
Route::post('/add-product-third-step', [Products::class,'third_step']);
Route::post('/add-product-last-step', [Products::class,'last_step']);
Route::post('/upload-image', [Products::class,'upload_image']);


Route::post('/featured-products', [Products::class,'featured_products']);
Route::post('/auction-products', [Products::class,'auction_products']);

Route::post('/wishlist-products', [WishlistController::class,'index']);
Route::post('/add-wishlist-products', [WishlistController::class,'store']);
Route::post('/remove-wishlist-products', [WishlistController::class,'destroy']);
