<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\TransactionMessage;
use App\Models\PurchaseUserRead;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\TransactionMessageRequest;

class TransactionMessageController extends Controller
{
    /**
     * 取引チャット画面の表示
     */
    public function show($purchaseId)
    {
        $user = Auth::user();

        // -------------------------------------------------
        // ▼ 取引情報
        // -------------------------------------------------
        $purchase = Purchase::with(['item.user', 'buyer'])
            ->findOrFail($purchaseId);

        // 出品者判定
        $isSeller = ($purchase->item->user_id === $user->id);

        // 関係者チェック（購入者 or 出品者）
        if (!($purchase->buyer_id === $user->id || $isSeller)) {
            abort(403);
        }

        // -------------------------------------------------
        // ▼ 既読更新
        // -------------------------------------------------
        PurchaseUserRead::updateOrCreate(
            [
                'purchase_id' => $purchase->id,
                'user_id'     => $user->id,
            ],
            [
                'last_read_at' => now(),
            ]
        );

        // -------------------------------------------------
        // ▼ メッセージ一覧
        // -------------------------------------------------
        $messages = TransactionMessage::with('user')
            ->where('purchase_id', $purchaseId)
            ->orderBy('created_at')
            ->get();

        // -------------------------------------------------
        // ▼ サイドバー用：取引中一覧（★ マイページと同じ条件に統一）
        // -------------------------------------------------
        $relatedPurchases = Purchase::with(['item', 'messages'])
            ->where(function ($q) use ($user) {

                // 購入者 → pending
                $q->where(function ($sub) use ($user) {
                    $sub->where('buyer_id', $user->id)
                        ->where('status', 'pending');
                });

                // 出品者 → pending / buyer_reviewed
                $q->orWhere(function ($sub) use ($user) {
                    $sub->whereHas('item', function ($itemQuery) use ($user) {
                        $itemQuery->where('user_id', $user->id);
                    })
                        ->whereIn('status', ['pending', 'buyer_reviewed']);
                });
            })
            ->get();

        // -------------------------------------------------
        // ▼ サイドバー：未読数を計算
        // -------------------------------------------------
        foreach ($relatedPurchases as $p) {

            $lastReadAt = PurchaseUserRead::where('purchase_id', $p->id)
                ->where('user_id', $user->id)
                ->value('last_read_at');

            $p->unread_count = TransactionMessage::where('purchase_id', $p->id)
                ->when($lastReadAt, fn($q) => $q->where('created_at', '>', $lastReadAt))
                ->where('user_id', '!=', $user->id)
                ->count();
        }

        // -------------------------------------------------
        // ▼ サイドバー：並び替え（★ マイページと同じロジック）
        // -------------------------------------------------
        $relatedPurchases = $relatedPurchases
            ->sortByDesc(function ($p) {
                return [
                    $p->unread_count > 0 ? 1 : 0,                       // 未読優先
                    optional($p->messages()->latest()->first())->created_at, // 最新メッセージ順
                ];
            })
            ->values();

        return view('transaction.chat', compact(
            'purchase',
            'messages',
            'relatedPurchases',
        ));
    }

    /**
     * 編集フォーム表示
     */
    public function edit($purchaseId, $messageId)
    {
        $message = TransactionMessage::findOrFail($messageId);

        // 自分のメッセージ以外は編集不可
        if ($message->user_id !== Auth::id()) {
            abort(403);
        }

        return view('transaction.edit', compact('message', 'purchaseId'));
    }

    /**
     * 更新
     */
    public function update(TransactionMessageRequest $request, $purchaseId, $messageId)
    {
        $message = TransactionMessage::findOrFail($messageId);

        // 権限チェック（投稿者本人のみ）
        if ($message->user_id !== Auth::id()) {
            abort(403);
        }

        $message->update([
            'message' => $request->message,
        ]);

        return redirect()
            ->route('transaction.chat', $purchaseId)
            ->with('success', 'メッセージを更新しました');
    }

    /**
     * 削除
     */
    public function destroy($purchaseId, $messageId)
    {
        $message = TransactionMessage::findOrFail($messageId);

        // 権限チェック
        if ($message->user_id !== Auth::id()) {
            abort(403);
        }

        $message->delete();

        return redirect()
            ->route('transaction.chat', $purchaseId)
            ->with('success', 'メッセージを削除しました');
    }

    /**
     * メッセージ投稿（FormRequest 使用）
     */
    public function store(TransactionMessageRequest $request, $purchaseId)
    {
        $user     = Auth::user();
        $purchase = Purchase::with('item')->findOrFail($purchaseId);

        $isSeller = ($purchase->item->user_id === $user->id);

        // 関係者チェック
        if (!($purchase->buyer_id === $user->id || $isSeller)) {
            abort(403);
        }

        // 本文も画像も空 → エラー
        if (!$request->message && !$request->file('image')) {
            return back()->withErrors([
                'message' => '本文を入力してください',
            ])->withInput();
        }

        // -------------------------------------------------
        // ▼ 画像保存（任意）
        // -------------------------------------------------
        $imagePath = null;
        if ($request->file('image')) {
            $imagePath = $request->file('image')->store('chat_images', 'public');
        }

        // -------------------------------------------------
        // ▼ メッセージ保存
        // -------------------------------------------------
        TransactionMessage::create([
            'purchase_id' => $purchase->id,
            'user_id'     => $user->id,
            'message'     => $request->message,
            'image_path'  => $imagePath,
        ]);

        return redirect()->route('transaction.chat', $purchaseId);
    }
}
