<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    // /mypage/profile 画面表示
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    // /mypage/profile 更新
    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required','string','max:255'],
            // 必要なら他の項目も追加
        ]);

        $request->user()->update($validated);

        return back()->with('success', 'プロフィールを更新しました');
    }
}
