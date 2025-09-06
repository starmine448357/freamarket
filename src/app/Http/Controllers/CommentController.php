<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CommentRequest;

class CommentController extends Controller
{
    /**
     * コメント保存
     */
public function store(CommentRequest $request, Item $item)
{
    $data = $request->validated();

    $item->comments()->create([
        'user_id' => Auth::id(),
        'content' => $data['content'],
    ]);

    return redirect()->to(route('items.show', $item->id) . '#comments');
}
}

