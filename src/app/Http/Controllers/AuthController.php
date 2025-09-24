<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class AuthController extends Controller
{
    /**
     * ログイン画面を表示
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * ログイン処理
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();
        $remember    = $request->boolean('remember');

        // ログイン失敗
        if (!Auth::attempt($credentials, $remember)) {
            return back()
                ->withErrors(['email' => 'ログイン情報が登録されていません'])
                ->onlyInput('email');
        }

        // セッション再生成（セッション固定攻撃対策）
        $request->session()->regenerate();

        $user = $request->user();

        // 1. 未認証 → 認証案内画面へ
        if (!$user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        // 2. 初回ログイン → プロフィール設定へ
        if (property_exists($user, 'needs_profile_setup') && $user->needs_profile_setup) {
            return redirect()
                ->route('mypage.profile.edit')
                ->with('status', '初回プロフィール設定をお願いします');
        }

        // 3. 通常ログイン → intended（直前ページ）or 商品一覧
        return redirect()->intended(route('items.index'));
    }

    /**
     * ログアウト処理
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('items.index');
    }
}
