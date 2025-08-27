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
        $user = $request->user();
        $user->update($request->all());

        // 更新後はマイページへリダイレクト
        return redirect()->route('mypage');
    }
}
