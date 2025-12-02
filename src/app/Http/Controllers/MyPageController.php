<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\Review;
use App\Models\TransactionMessage;
use App\Models\PurchaseUserRead;

class MyPageController extends Controller
{
    /**
     * マイページの一覧表示（出品 / 購入 / 取引中）
     */
    public function show(Request $request)
    {
        $user = $request->user();

        // -------------------------------------------------
        // ★ 自分の平均評価（プロフィール表示用）
        // -------------------------------------------------
        $ratingAvg = Review::where('target_id', $user->id)->avg('rating');
        $ratingAvgRounded = $ratingAvg ? round($ratingAvg) : null;

        // -------------------------------------------------
        // ★ タブ（sell / buy / transaction）
        // -------------------------------------------------
        $tab = $request->query('tab', 'sell');

        // 各タブ用の配列を初期化
        $sellingItems     = collect();
        $purchasedItems   = collect();
        $transactionItems = collect();

        // デフォルト（未読件数）
        $totalUnread = 0;

        // -------------------------------------------------
        // ★ タブ：出品した商品（sell）
        // -------------------------------------------------
        if ($tab === 'sell') {
            $sellingItems = Item::where('user_id', $user->id)
                ->select('id', 'title', 'image_path', 'price', 'user_id', 'created_at')
                ->latest()
                ->get();
        }

        // -------------------------------------------------
        // ★ タブ：購入した商品（buy）
        // -------------------------------------------------
        if ($tab === 'buy') {
            $purchasedItems = Purchase::where('buyer_id', $user->id)
                ->with(['item:id,title,image_path,price,user_id'])
                ->select('id', 'item_id', 'user_id', 'buyer_id', 'status', 'created_at')
                ->latest()
                ->get();
        }

        // -------------------------------------------------
        // ★ タブ：取引中（transaction）
        // -------------------------------------------------
        if ($tab === 'transaction') {

            // ▼ 取引中のデータ取得（購入者 or 出品者）
            $transactionItems = Purchase::where(function ($query) use ($user) {

                // ▼ 購入者側：status = pending
                $query->where(function ($q) use ($user) {
                    $q->where('buyer_id', $user->id)
                        ->where('status', 'pending');
                });

                // ▼ 出品者側：status = pending / buyer_reviewed
                $query->orWhereHas('item', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                    ->whereIn('status', ['pending', 'buyer_reviewed']);
            })
                ->with(['item:id,title,image_path,price,user_id', 'messages'])
                ->select('id', 'item_id', 'user_id', 'buyer_id', 'status', 'created_at')
                ->latest()
                ->get();

            // -------------------------------------------------
            // ★ 各取引の未読メッセージ数を計算
            // -------------------------------------------------
            foreach ($transactionItems as $purchase) {

                $lastReadAt = PurchaseUserRead::where('purchase_id', $purchase->id)
                    ->where('user_id', $user->id)
                    ->value('last_read_at');

                $unreadCount = TransactionMessage::where('purchase_id', $purchase->id)
                    ->when($lastReadAt, function ($q) use ($lastReadAt) {
                        $q->where('created_at', '>', $lastReadAt);
                    })
                    ->count();

                $purchase->unread_count = $unreadCount;
            }

            // -------------------------------------------------
            // ★ 並び替え（未読件数 → 最新メッセージ順）
            // -------------------------------------------------
            $transactionItems = $transactionItems
                ->sortByDesc('unread_count')
                ->sortByDesc(function ($purchase) {
                    return optional($purchase->messages()->latest()->first())->created_at;
                })
                ->values();

            // 総未読件数
            $totalUnread = $transactionItems->sum('unread_count');
        }

        // -------------------------------------------------
        // ★ ビューへ返却
        // -------------------------------------------------
        return view('mypage.mypage', [
            'tab'               => $tab,
            'sellingItems'      => $sellingItems,
            'purchasedItems'    => $purchasedItems,
            'transactionItems'  => $transactionItems,
            'ratingAvgRounded'  => $ratingAvgRounded,
            'totalUnread'       => $totalUnread,
        ]);
    }
}
