<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

use App\Http\Controllers\ItemController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MyPageController;
use App\Http\Controllers\TempImageController;

/*
|--------------------------------------------------------------------------
| 公開ルート（ログイン不要）
|--------------------------------------------------------------------------
*/

// おすすめ商品一覧（初期表示）
Route::get('/', [ItemController::class, 'index'])->name('items.index');

// マイリスト（公開で見られる一覧）
Route::get('/mylist', [ItemController::class, 'mylist'])->name('items.mylist');

// 旧URL対応
Route::permanentRedirect('/items', '/');
Route::permanentRedirect('/items/{item}', '/item/{item}')->whereNumber('item');

// 商品詳細ページ
Route::get('/item/{item}', [ItemController::class, 'show'])
    ->whereNumber('item')
    ->name('items.show');

// ★ コメント投稿（ログイン必須のみ／未ログインはloginへリダイレクト）
Route::post('/item/{item}/comments', [CommentController::class, 'store'])
    ->middleware('auth')
    ->whereNumber('item')
    ->name('comments.store');

/*
|--------------------------------------------------------------------------
| メール認証関連ルート
|--------------------------------------------------------------------------
*/

// 認証待ち画面
Route::get('/email/verify', fn () => view('auth.verify-email'))
    ->middleware('auth')
    ->name('verification.notice');

// メール内リンク（認証完了）
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill(); // 認証完了
    return redirect()->route('mypage'); // 認証後はマイページへ
})->middleware(['auth', 'signed'])->name('verification.verify');

// 認証メール再送
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', '認証メールを再送しました');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

/*
|--------------------------------------------------------------------------
| ログイン必須 ＋ メール認証必須ルート
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    // ★ 一時画像アップロード（プレビュー保持用）
    Route::post('/items/image/temp', [TempImageController::class, 'store'])
        ->name('items.image.temp');

    // マイページ（タブ切り替え対応）
    Route::get('/mypage', [MyPageController::class, 'show'])->name('mypage');

    // プロフィール編集
    Route::get('/mypage/profile', [ProfileController::class, 'edit'])->name('mypage.profile.edit');
    Route::put('/mypage/profile', [ProfileController::class, 'update'])->name('mypage.profile.update');

    // 出品
    Route::get('/sell', [ItemController::class, 'create'])->name('items.create');
    Route::post('/sell', [ItemController::class, 'store'])->name('items.store');

    // 商品の編集・削除
    Route::get('/item/{item}/edit', [ItemController::class, 'edit'])->whereNumber('item')->name('items.edit');
    Route::put('/item/{item}', [ItemController::class, 'update'])->whereNumber('item')->name('items.update');
    Route::delete('/item/{item}', [ItemController::class, 'destroy'])->whereNumber('item')->name('items.destroy');

    // いいね機能
    Route::post('/item/{item}/like', [LikeController::class, 'store'])->whereNumber('item')->name('likes.store');
    Route::delete('/item/{item}/like', [LikeController::class, 'destroy'])->whereNumber('item')->name('likes.destroy');

    // 購入処理
    Route::get('/items/{item}/purchase', [PurchaseController::class, 'create'])->whereNumber('item')->name('purchases.create');
    Route::post('/items/{item}/purchase', [PurchaseController::class, 'store'])->whereNumber('item')->name('purchases.store');

    // 決済成功・キャンセル
    Route::get('/items/{item}/purchase/success', [PurchaseController::class, 'success'])->whereNumber('item')->name('purchases.success');
    Route::get('/items/{item}/purchase/cancel',  [PurchaseController::class, 'cancel'])->whereNumber('item')->name('purchases.cancel');

    // 購入時の住所入力
    Route::get('/purchase/address/{item}', [PurchaseController::class, 'editAddress'])->whereNumber('item')->name('purchases.address.edit');
    Route::post('/purchase/address/{item}', [PurchaseController::class, 'updateAddress'])->whereNumber('item')->name('purchases.address.update');
});

/*
|--------------------------------------------------------------------------
| メール認証チェック用ルート
|--------------------------------------------------------------------------
*/
Route::post('/email/verify/check', function (Request $request) {
    if ($request->user()->hasVerifiedEmail()) {
        return redirect()->route('mypage.profile.edit')
            ->with('status', 'email-verified');
    }
    return back()->with('status', 'email-not-verified');
})->middleware(['auth'])->name('verification.check');
