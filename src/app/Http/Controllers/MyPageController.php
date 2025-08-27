<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;

class MyPageController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        $tab  = $request->query('tab', 'selling'); // 'selling' or 'purchased'

        // 出品した商品
        $sellingItems = Item::where('user_id', $user->id)
            ->latest()
            ->get(['id','title','image_path','user_id','created_at']);

        // 購入した商品（Purchase -> item を eager load）
        $purchasedItems = $user->purchases()
            ->with(['item:id,title,image_path'])
            ->latest()
            ->get();

        return view('mypage.mypage', compact('tab','sellingItems','purchasedItems'));
    }
}
