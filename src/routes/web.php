<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

use App\Http\Controllers\ItemController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ProfileController;

/* Public */
Route::get('/', [ItemController::class, 'index'])->name('items.index'); // /?tab=mylist は Controller で分岐
Route::get('/items', fn () => redirect()->route('items.index'), 301);
Route::get('/items/{item}', fn ($item) => redirect("/item/{$item}"), 301);
Route::get('/item/{item}', [ItemController::class, 'show'])->name('items.show');

/* Email Verification */
Route::get('/email/verify', fn () => view('auth.verify-email'))
    ->middleware('auth')
    ->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect()->route('mypage.profile.edit'); // 認証後はプロフィール編集へ
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', '認証メールを再送しました');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

/* Auth + Verified */
Route::middleware(['auth', 'verified'])->group(function () {
    // マイページ → プロフィール編集に直行
    Route::get('/mypage', function () {
        return redirect()->route('mypage.profile.edit');
    })->name('mypage.index');

    // プロフィール編集
    Route::get('/mypage/profile', [ProfileController::class, 'edit'])->name('mypage.profile.edit');
    Route::put('/mypage/profile', [ProfileController::class, 'update'])->name('mypage.profile.update');

    // 出品
    Route::get('/sell',  [ItemController::class, 'create'])->name('items.create');
    Route::post('/sell', [ItemController::class, 'store'])->name('items.store');

    // 編集・削除
    Route::get('/item/{item}/edit', [ItemController::class, 'edit'])->name('items.edit');
    Route::put('/item/{item}',       [ItemController::class, 'update'])->name('items.update');
    Route::delete('/item/{item}',    [ItemController::class, 'destroy'])->name('items.destroy');

    // いいね / コメント
    Route::post('/item/{item}/like',   [LikeController::class, 'store'])->name('likes.store');
    Route::delete('/item/{item}/like', [LikeController::class, 'destroy'])->name('likes.destroy');
    Route::post('/item/{item}/comments', [CommentController::class, 'store'])->name('comments.store');

    // 購入
    Route::get('/purchase/{item}',  [PurchaseController::class, 'create'])->name('purchases.create');
    Route::post('/purchase/{item}', [PurchaseController::class, 'store'])->name('purchases.store');
    Route::get('/purchase/address/{item}',  [PurchaseController::class, 'editAddress'])->name('purchases.address.edit');
    Route::post('/purchase/address/{item}', [PurchaseController::class, 'updateAddress'])->name('purchases.address.update');
});
