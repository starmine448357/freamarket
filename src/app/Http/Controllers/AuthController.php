<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class AuthController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        // LoginRequest で検証済みの値を使う（email / password）
        $credentials = $request->validated();

        // remember がフォームに無ければ false
        $remember = $request->boolean('remember');

        if (!Auth::attempt($credentials, $remember)) {
            return back()
                ->withErrors(['email' => 'メールアドレスまたはパスワードが正しくありません。'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = $request->user();

        // 1) まだメール認証していなければ、認証案内へ
        if (! $user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        // 2) 初回ログインだけプロフィール設定へ（needs_profile_setup フラグ方式）
        //   ※ フラグを作っていない場合は、avatar_path 等の未設定判定に置き換え可
        if (property_exists($user, 'needs_profile_setup') && $user->needs_profile_setup) {
            return redirect()
                ->route('mypage.profile.edit')
                ->with('status', '初回プロフィール設定をお願いします');
        }

        // 3) それ以外は intended（直前に見ていた保護ページ）or 商品一覧へ
        return redirect()->intended(route('items.index'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('items.index');
    }
}
