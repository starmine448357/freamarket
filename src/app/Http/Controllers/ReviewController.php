<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\TransactionCompleted;

class ReviewController extends Controller
{
    /**
     * 購入者・出品者 共通レビュー送信
     *
     * status 仕様
     * - pending          … 初期状態（レビュー前）
     * - buyer_reviewed   … 購入者がレビュー済み（出品者待ち）
     * - completed        … 双方レビュー済み（取引完全終了）
     */
    public function store(Request $request, $purchaseId)
    {
        $user   = Auth::user();
        $userId = $user->id;

        // -------------------------------------------------
        // ▼ 取引情報の取得
        // -------------------------------------------------
        $purchase = Purchase::with(['item.user', 'buyer'])
            ->findOrFail($purchaseId);

        $buyerId  = $purchase->buyer_id;
        $sellerId = $purchase->item->user_id;

        // -------------------------------------------------
        // ▼ バリデーション
        // rating は null を許容（未選択なら 0 扱い）
        // -------------------------------------------------
        $request->validate([
            'rating'  => ['nullable', 'integer', 'min:0', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        // 未選択（null）は rating=0 に変換
        $rating = $request->rating ?? 0;

        // -------------------------------------------------
        // ▼ 二重レビュー防止（同じ purchase + reviewer で1回だけ）
        // -------------------------------------------------
        $alreadyReviewed = Review::where('purchase_id', $purchaseId)
            ->where('reviewer_id', $userId)
            ->exists();

        if ($alreadyReviewed) {
            return redirect()->route('items.index');
        }

        // -------------------------------------------------
        // ▼ 購入者レビュー
        // -------------------------------------------------
        if ($userId === $buyerId) {

            Review::create([
                'purchase_id' => $purchaseId,
                'reviewer_id' => $buyerId,
                'target_id'   => $sellerId,
                'rating'      => $rating,
                'comment'     => $request->comment,
            ]);

            // 購入者 → buyer_reviewed
            $purchase->status = "buyer_reviewed";
            $purchase->save();

            // 出品者へメール通知
            Mail::to($purchase->item->user->email)
                ->send(new TransactionCompleted($purchase));

            return redirect()->route('items.index');
        }

        // -------------------------------------------------
        // ▼ 出品者レビュー
        // -------------------------------------------------
        if ($userId === $sellerId) {

            // 購入者がレビューしていない場合は出品者は評価不可
            if ($purchase->status !== "buyer_reviewed") {
                abort(403, '購入者が評価するまで出品者は評価できません');
            }

            Review::create([
                'purchase_id' => $purchaseId,
                'reviewer_id' => $sellerId,
                'target_id'   => $buyerId,
                'rating'      => $rating,
                'comment'     => $request->comment,
            ]);

            // 双方評価済み → completed
            $purchase->status = "completed";
            $purchase->save();

            return redirect()->route('items.index');
        }

        // -------------------------------------------------
        // ▼ 関係者以外はアクセス禁止
        // -------------------------------------------------
        abort(403);
    }
}
