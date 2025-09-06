<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class TempImageController extends Controller
{
    /**
     * 画像の一時アップロード
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // バリデーション
        $request->validate([
            'image' => ['required', 'file', 'mimes:jpeg,png', 'max:5120'], // 5MBまで
        ]);

        $file = $request->file('image');
        $ext = $file->getClientOriginalExtension();
        $name = Str::uuid()->toString() . '.' . $ext;

        // 保存先ディレクトリを保証（無ければ作成）
        if (!Storage::exists('public/temp')) {
            Storage::makeDirectory('public/temp');
        }

        // public/temp に保存（storage:link 済み前提）
        $file->storeAs('public/temp', $name);

        // セッションにファイル名を保持（エラー時の復元に利用）
        session()->put('temp_image', $name);

        return response()->json([
            'filename' => $name,
            'url'      => asset('storage/temp/' . $name),
        ]);
    }
}
