<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;

class MyPageController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        // デフォルトは 'sell'（出品した商品を表示）
        $tab  = $request->query('tab', 'sell');

        $sellingItems   = collect();
        $purchasedItems = collect();

        if ($tab === 'sell') {
            // 出品した商品
            $sellingItems = Item::where('user_id', $user->id)
                ->latest()
                ->get(['id','title','image_path','user_id','created_at']);
        }

        if ($tab === 'buy') {
            // 購入した商品（Purchase -> item を eager load）
            $purchasedItems = $user->purchases()
                ->with(['item:id,title,image_path,price'])
                ->latest()
                ->get();
        }

        return view('mypage.mypage', compact('tab','sellingItems','purchasedItems'));
    }
}
