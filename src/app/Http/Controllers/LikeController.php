<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Like;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    // いいね追加
    public function store(Item $item)
    {
        // 自分の出品にいいねを禁止するなら↓
        // if ($item->user_id === Auth::id()) { return back(303); }

        Like::firstOrCreate([
            'user_id' => Auth::id(),
            'item_id' => $item->id,
        ]);

        return back(303);

        // フラッシュ不要なら ->with() を外してOK
    }

    // いいね解除
    public function destroy(Item $item)
    {
        Like::where('user_id', Auth::id())
            ->where('item_id', $item->id)
            ->delete();

            return back(303);

    }


}
