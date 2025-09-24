<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CommentRequest;

class CommentController extends Controller
{
    /**
     * コメントを保存する
     */
    public function store(CommentRequest $request, Item $item)
    {
        $validated = $request->validated();

        $item->comments()->create([
            'user_id' => Auth::id(),
            'content' => $validated['content'],
        ]);

        // コメント一覧の位置へスクロール
        return redirect()->route('items.show', $item->id) . '#comments';
    }
}
