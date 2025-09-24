<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TempImageController extends Controller
{
    /**
     * 商品画像を一時保存（プレビュー用）
     */
    public function store(Request $request)
    {
        if (!$request->hasFile('image')) {
            return response()->json(['error' => 'ファイルが選択されていません'], 422);
        }

        $file = $request->file('image');

        if (!$file->isValid()) {
            return response()->json(['error' => 'アップロードに失敗しました'], 422);
        }

        Storage::disk('public')->makeDirectory('temp');

        $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();

        $file->storeAs('temp', $filename, 'public');

        session(['temp_image' => $filename]);

        return response()->json([
            'filename' => $filename,
            'url'      => Storage::disk('public')->url("temp/{$filename}"),
        ]);
    }
}
