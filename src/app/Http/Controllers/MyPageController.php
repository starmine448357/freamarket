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
    public function show(Request $request)
    {
        $user = $request->user();

        // ★ 平均評価
        $ratingAvg = Review::where('target_id', $user->id)->avg('rating');
        $ratingAvgRounded = $ratingAvg ? round($ratingAvg) : null;

        // ★ タブ
        $tab = $request->query('tab', 'sell');

        $sellingItems     = collect();
        $purchasedItems   = collect();
        $transactionItems = collect();

        // ================================
        // ★ タブ関係なく未読総数
        // ================================
        $totalUnread = Purchase::where(function ($q) use ($user) {

            $q->where('buyer_id', $user->id)
                ->whereIn('status', ['pending', 'buyer_reviewed']);

            $q->orWhereHas('item', function ($q2) use ($user) {
                $q2->where('user_id', $user->id);
            });
        })
            ->with(['messages', 'reads'])
            ->get()
            ->reduce(function ($carry, $purchase) use ($user) {

                $lastReadAt = optional(
                    $purchase->reads->where('user_id', $user->id)->first()
                )->last_read_at;

                $unread = $purchase->messages
                    ->where('user_id', '!=', $user->id)
                    ->filter(fn($msg) => !$lastReadAt || $msg->created_at > $lastReadAt)
                    ->count();

                return $carry + $unread;
            }, 0);

        // ================================
        // ★ 出品タブ
        // ================================
        if ($tab === 'sell') {
            $sellingItems = Item::where('user_id', $user->id)
                ->select('id', 'title', 'image_path', 'price', 'user_id', 'created_at')
                ->latest()
                ->get();
        }

        // ================================
        // ★ 購入タブ
        // ================================
        if ($tab === 'buy') {
            $purchasedItems = Purchase::where('buyer_id', $user->id)
                ->with(['item:id,title,image_path,price,user_id'])
                ->select('id', 'item_id', 'user_id', 'buyer_id', 'status', 'created_at')
                ->latest()
                ->get();
        }

        // ================================
        // ★ 取引中タブ
        // ================================
        if ($tab === 'transaction') {

            $transactionItems = Purchase::where(function ($query) use ($user) {

                // 購入者側 = pending のみ
                $query->where(function ($q) use ($user) {
                    $q->where('buyer_id', $user->id)
                        ->where('status', 'pending');
                });

                // 出品者側 = pending or buyer_reviewed
                $query->orWhereHas('item', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                    ->whereIn('status', ['pending', 'buyer_reviewed']);
            })
                ->with(['item:id,title,image_path,price,user_id', 'messages'])
                ->select('id', 'item_id', 'user_id', 'buyer_id', 'status', 'created_at')
                ->get();

            // ★ 各取引の未読数を計算
            foreach ($transactionItems as $purchase) {

                $lastReadAt = PurchaseUserRead::where('purchase_id', $purchase->id)
                    ->where('user_id', $user->id)
                    ->value('last_read_at');

                $purchase->unread_count = TransactionMessage::where('purchase_id', $purchase->id)
                    ->when($lastReadAt, fn($q) => $q->where('created_at', '>', $lastReadAt))
                    ->count();
            }

            // ==================================================
            // ★ 並び替え（"未読あり → 未読なし" → 最新メッセージ順）
            // ==================================================
            $transactionItems = $transactionItems
                ->sortByDesc(function ($p) {
                    return [
                        $p->unread_count > 0 ? 1 : 0, // 未読優先
                        optional($p->messages()->latest()->first())->created_at, // 最新順
                    ];
                })
                ->values();
        }

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
