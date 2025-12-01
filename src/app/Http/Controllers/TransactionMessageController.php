<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\TransactionMessage;
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

        // ▼ 取引情報
        $purchase = Purchase::with([
            'item.user',
            'buyer',
        ])->findOrFail($purchaseId);

        // ▼ 出品者判定
        $isSeller = ($purchase->item->user_id === $user->id);

        // ▼ 関係者チェック
        if (!($purchase->buyer_id === $user->id || $isSeller)) {
            abort(403);
        }

        // ▼ メッセージ一覧
        $messages = TransactionMessage::with('user')
            ->where('purchase_id', $purchaseId)
            ->orderBy('created_at')
            ->get();

        // ▼ サイドバー（取引中）
        $relatedPurchases = Purchase::with('item')
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
            ->orderBy('created_at', 'desc')
            ->get();

        return view('transaction.chat', compact(
            'purchase',
            'messages',
            'relatedPurchases'
        ));
    }

    /**
     * 編集
     */
    public function edit($purchaseId, $messageId)
    {
        $message = TransactionMessage::findOrFail($messageId);

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

        if ($message->user_id !== Auth::id()) {
            abort(403);
        }

        $message->update([
            'message' => $request->message,
        ]);

        return redirect()->route('transaction.chat', $purchaseId)
            ->with('success', 'メッセージを更新しました');
    }

    /**
     * 削除
     */
    public function destroy($purchaseId, $messageId)
    {
        $message = TransactionMessage::findOrFail($messageId);

        if ($message->user_id !== Auth::id()) {
            abort(403);
        }

        $message->delete();

        return redirect()->route('transaction.chat', $purchaseId)
            ->with('success', 'メッセージを削除しました');
    }

    /**
     * メッセージ投稿（FormRequest 使用版）
     */
    public function store(TransactionMessageRequest $request, $purchaseId)
    {
        $user = Auth::user();
        $purchase = Purchase::with('item')->findOrFail($purchaseId);

        $isSeller = ($purchase->item->user_id === $user->id);

        if (!($purchase->buyer_id === $user->id || $isSeller)) {
            abort(403);
        }

        // 本文が空 & 画像も空 → NG（仕様書 FN006）
        if (!$request->message && !$request->file('image')) {
            return back()->withErrors([
                'message' => '本文を入力してください',
            ])->withInput();
        }

        // 画像保存
        $imagePath = null;
        if ($request->file('image')) {
            $imagePath = $request->file('image')->store('chat_images', 'public');
        }

        // 保存
        TransactionMessage::create([
            'purchase_id' => $purchase->id,
            'user_id'     => $user->id,
            'message'     => $request->message,
            'image_path'  => $imagePath,
        ]);

        return redirect()->route('transaction.chat', $purchaseId);
    }
}
