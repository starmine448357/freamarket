<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth; // ★ 追加
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

use App\Http\Controllers\ItemController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MyPageController;
use App\Http\Controllers\TempImageController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RegisterController;

/*
|--------------------------------------------------------------------------
| 公開ルート（ログイン不要）
|--------------------------------------------------------------------------
*/

// 商品一覧（トップ & /items 共通）
Route::get('/', [ItemController::class, 'index'])->name('items.index');
Route::get('/items', [ItemController::class, 'index']);

// マイリスト（公開で見られる一覧）
Route::get('/mylist', [ItemController::class, 'mylist'])->name('items.mylist');

// 商品詳細ページ
Route::get('/item/{item}', [ItemController::class, 'show'])
    ->whereNumber('item')
    ->name('items.show');

// コメント投稿（ログイン必須／未ログインはloginへリダイレクト）
Route::post('/item/{item}/comments', [CommentController::class, 'store'])
    ->middleware('auth')
    ->whereNumber('item')
    ->name('comments.store');

// ゲスト専用：ログイン / 会員登録
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');   // 画面表示
    Route::post('/login', [AuthController::class, 'store']);                  // LoginRequest で検証

    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);           // RegisterRequest で検証
});

// ログアウト
Route::post('/logout', [AuthController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| メール認証関連ルート
|--------------------------------------------------------------------------
*/

// 認証待ち画面（要ログイン）
Route::get('/email/verify', fn () => view('auth.verify-email'))
    ->middleware('auth')
    ->name('verification.notice');

// 認証リンク（メール内）→ 認証後はログアウトしてログインページへ
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill(); // 認証完了

    // ★ 認証直後にログアウト → ログインページへ戻す
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login')->with('status', 'email-verified');
})->middleware(['auth', 'signed'])->name('verification.verify');

// 認証メール再送
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', '認証メールを再送しました');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

/*
|--------------------------------------------------------------------------
| ログイン必須＋メール認証必須ルート
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {

    // 一時画像アップロード（プレビュー保持用）
    Route::post('/items/image/temp', [TempImageController::class, 'store'])
        ->name('items.image.temp');

    // マイページ
    Route::get('/mypage', [MyPageController::class, 'show'])->name('mypage');

    // プロフィール編集
    Route::get('/mypage/profile', [ProfileController::class, 'edit'])->name('mypage.profile.edit');
    Route::put('/mypage/profile', [ProfileController::class, 'update'])->name('mypage.profile.update');

    // 出品
    Route::get('/sell', [ItemController::class, 'create'])->name('items.create');
    Route::post('/sell', [ItemController::class, 'store'])->name('items.store');

    // 商品編集・削除
    Route::get('/item/{item}/edit', [ItemController::class, 'edit'])->whereNumber('item')->name('items.edit');
    Route::put('/item/{item}', [ItemController::class, 'update'])->whereNumber('item')->name('items.update');
    Route::delete('/item/{item}', [ItemController::class, 'destroy'])->whereNumber('item')->name('items.destroy');

    // いいね
    Route::post('/item/{item}/like', [LikeController::class, 'store'])->whereNumber('item')->name('likes.store');
    Route::delete('/item/{item}/like', [LikeController::class, 'destroy'])->whereNumber('item')->name('likes.destroy');

    // 購入処理（未ログインは intended 経由でログイン→戻る）
    Route::get('/items/{item}/purchase', [PurchaseController::class, 'create'])->whereNumber('item')->name('purchases.create');
    Route::post('/items/{item}/purchase', [PurchaseController::class, 'store'])->whereNumber('item')->name('purchases.store');

    // 決済完了・キャンセル
    Route::get('/items/{item}/purchase/success', [PurchaseController::class, 'success'])->whereNumber('item')->name('purchases.success');
    Route::get('/items/{item}/purchase/cancel',  [PurchaseController::class, 'cancel'])->whereNumber('item')->name('purchases.cancel');

    // 購入時住所入力
    Route::get('/purchase/address/{item}', [PurchaseController::class, 'editAddress'])->whereNumber('item')->name('purchases.address.edit');
    Route::post('/purchase/address/{item}', [PurchaseController::class, 'updateAddress'])->whereNumber('item')->name('purchases.address.update');
});

/*
|--------------------------------------------------------------------------
| メール認証チェック（案内ページの「認証はこちらから」ボタン）
|--------------------------------------------------------------------------
*/
// 認証済みならログアウトしてログインページへ、未認証なら案内ページに留まる
Route::post('/email/verify/check', function (Request $request) {
    if ($request->user()->hasVerifiedEmail()) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('status', 'email-verified');
    }
    return back()->with('status', 'email-not-verified');
})->middleware(['auth'])->name('verification.check');
