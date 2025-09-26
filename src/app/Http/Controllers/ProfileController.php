<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * プロフィール編集画面を表示 (/mypage/profile)
     */
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    /**
     * プロフィール更新処理 (/mypage/profile)
     */
    public function update(ProfileRequest $request)
    {
        $user = $request->user();

        // アップロード画像がある場合
        if ($request->hasFile('profile_image')) {
            // 古い画像を削除
            if (
                $user->profile_image_path &&
                Storage::disk('public')->exists($user->profile_image_path)
            ) {
                Storage::disk('public')->delete($user->profile_image_path);
            }

            // 新しい画像を保存（public/avatars/に保存）
            $path = $request->file('profile_image')->store('avatars', 'public');
            $user->profile_image_path = $path;
        }
        
        // 他のプロフィール情報を更新
        $user->name        = $request->input('name');
        $user->postal_code = $request->input('postal_code');
        $user->address     = $request->input('address');
        $user->building    = $request->input('building');
        $user->save();

        return redirect()
            ->route('mypage')
            ->with('status', 'プロフィールを更新しました');
    }
}
