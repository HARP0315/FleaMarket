<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PurchaseController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\StripeWebhookController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

//★ログイン不要
Route::get('/', [ItemController::class, 'index']);
Route::get('/item/{item_id}',[ItemController::class,'show']);
Route::post('/item/{item}/comments',[CommentController::class,'store']);
Route::get('/purchase/success', [PurchaseController::class, 'success'])
    ->name('purchase.success');
Route::get('/purchase/{item_id}/cancel', [PurchaseController::class, 'cancel'])
    ->name('purchase.cancel');
//Stripe Webhook
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook'])
 ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

//★ログイン必要
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/mypage',[UserController::class,'index']);
    Route::get('/mypage/profile',[UserController::class,'edit']);
    Route::patch('/mypage/profile',[UserController::class,'update']);
    Route::get('/sell',[ItemController::class,'create']);
    Route::post('/sell',[ItemController::class,'store']);
    Route::post('/item/{item}/like',[LikeController::class,'store']);
    Route::delete('/item/{item}/unlike',[LikeController::class,'destroy']);
    Route::get('/purchase/{item_id}',[PurchaseController::class,'create']);
    Route::post('/purchase/{item_id}',[PurchaseController::class,'store']);
    Route::get('/purchase/address/{item_id}',[PurchaseController::class,'edit']);
    Route::post('/purchase/address/{item_id}',[PurchaseController::class,'update']);
});
