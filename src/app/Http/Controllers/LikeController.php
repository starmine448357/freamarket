<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Like;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    /**
     * いいね追加
     */
    public function store(Item $item)
    {
        Like::firstOrCreate([
            'user_id' => Auth::id(),
            'item_id' => $item->id,
        ]);

        return back(303);
    }

    /**
     * いいね解除
     */
    public function destroy(Item $item)
    {
        Like::where('user_id', Auth::id())
            ->where('item_id', $item->id)
            ->delete();

        return back(303);
    }
}
