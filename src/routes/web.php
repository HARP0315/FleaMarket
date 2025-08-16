<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PurchaseController;
use Illuminate\Support\Facades\Route;

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

// ■ 誰でも見れるページ
Route::get('/', [ItemController::class, 'index']);
// 他に、商品詳細ページなどもここ

// ■ ログインが必要なページ
Route::middleware('auth')->group(function () {
    // Route::get('/mypage', ...)->name('mypage.index');
    // Route::get('/sell', ...);
    // ...など
});
