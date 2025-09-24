<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;

class MyPageController extends Controller
{
    /**
     * マイページを表示（出品 or 購入した商品）
     */
    public function show(Request $request)
    {
        $user = $request->user();
        $tab  = $request->query('tab', 'sell'); // デフォルトは出品した商品

        $sellingItems   = collect();
        $purchasedItems = collect();

        if ($tab === 'sell') {
            // 出品した商品
            $sellingItems = Item::where('user_id', $user->id)
                ->latest()
                ->get(['id', 'title', 'image_path', 'user_id', 'created_at']);
        }

        if ($tab === 'buy') {
            // 購入した商品（リレーションで商品を eager load）
            $purchasedItems = $user->purchases()
                ->with(['item:id,title,image_path,price'])
                ->latest()
                ->get();
        }

        return view('mypage.mypage', compact('tab', 'sellingItems', 'purchasedItems'));
    }
}
