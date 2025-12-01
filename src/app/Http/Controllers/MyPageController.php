<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Purchase;

class MyPageController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        // タブ：sell / buy / transaction
        $tab = $request->query('tab', 'sell');

        $sellingItems      = collect();
        $purchasedItems    = collect();
        $transactionItems  = collect();

        /**
         * ▼ 出品した商品（sell）
         */
        if ($tab === 'sell') {
            $sellingItems = Item::where('user_id', $user->id)
                ->select('id', 'title', 'image_path', 'price', 'user_id', 'created_at')
                ->latest()
                ->get();
        }

        /**
         * ▼ 購入した商品（buy）
         */
        if ($tab === 'buy') {
            $purchasedItems = Purchase::where('buyer_id', $user->id)
                ->with(['item:id,title,image_path,price,user_id'])
                ->select('id', 'item_id', 'user_id', 'buyer_id', 'status', 'created_at')
                ->latest()
                ->get();
        }

        /**
         * ▼ 取引中の商品（transactionタブ）
         *
         * 【あなたの仕様】
         * - 購入者 → pending のみ
         * - 出品者 → pending / buyer_reviewed
         */
        if ($tab === 'transaction') {

            $transactionItems = Purchase::where(function ($query) use ($user) {

                /** ▼ 購入者として参加している取引 */
                $query->where(function ($q) use ($user) {
                    $q->where('buyer_id', $user->id)
                        ->where('status', 'pending'); // ★ 購入者は pending のみ
                });

                /** ▼ 出品者として参加している取引 */
                $query->orWhereHas('item', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                    ->whereIn('status', ['pending', 'buyer_reviewed']); // ★ 出品者は両方
            })
                ->with(['item:id,title,image_path,price,user_id'])
                ->select('id', 'item_id', 'user_id', 'buyer_id', 'status', 'created_at')
                ->latest()
                ->get();
        }

        return view('mypage.mypage', compact(
            'tab',
            'sellingItems',
            'purchasedItems',
            'transactionItems'
        ));
    }
}
