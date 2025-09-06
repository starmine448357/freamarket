<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PurchaseController extends Controller
{
    /**
     * 購入フォーム
     */
    public function create(Item $item)
    {
        // 404: すでに売却済み
        if (($item->status ?? null) === 'sold') {
            abort(404);
        }
        // 403: 自分の商品は買えない
        if (Auth::id() === $item->user_id) {
            abort(403);
        }

        return view('purchases.create', compact('item'));
    }

    /**
     * Stripe Checkout へ遷移
     */
    public function store(\Illuminate\Http\Request $request, Item $item)
    {
        // 事前ガード
        if (($item->status ?? null) === 'sold') {
            abort(404);
        }
        if (Auth::id() === $item->user_id) {
            abort(403);
        }

        // ===== 入力バリデーション =====
        // 互換対応: 旧name="payment_method"（credit_card/convenience_store）も受けて正規化
        $validated = $request->validate([
            'payment' => ['nullable', Rule::in(['card', 'konbini'])],
            'payment_method' => ['nullable', Rule::in(['credit_card', 'convenience_store'])],
        ]);

        $payment = $validated['payment']
            ?? (isset($validated['payment_method'])
                ? ($validated['payment_method'] === 'credit_card' ? 'card' : 'konbini')
                : null);

        if (!$payment) {
            return back()->withErrors(['payment' => '支払い方法を選択してください'])->withInput();
        }

        try {
            // ===== Stripe Checkout セッション作成 =====
            $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));

            $session = $stripe->checkout->sessions->create([
                'mode' => 'payment',
                'payment_method_types' => $payment === 'card' ? ['card'] : ['konbini'],
                'customer_email' => optional(Auth::user())->email,
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'jpy',
                        'unit_amount' => (int) $item->price, // 円
                        'product_data' => [
                            'name' => $item->title,
                            // 画像URLはローカルだと外しておくのが無難
                        ],
                    ],
                    'quantity' => 1,
                ]],
                'success_url' => route('purchases.success', $item) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'  => route('purchases.cancel',  $item),
                'metadata'    => [
                    'item_id'  => (string) $item->id,
                    'buyer_id' => (string) Auth::id(),
                    'payment'  => $payment,
                ],
            ]);

            // Stripeの決済画面へ
            return redirect()->away($session->url);

        } catch (\Throwable $e) {
            \Log::error('Stripe checkout error', [
                'message' => $e->getMessage(),
                'item_id' => $item->id,
                'buyer_id'=> Auth::id(),
                'payment' => $payment,
            ]);

            return back()
                ->withErrors(['payment' => '決済の開始に失敗しました: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * 決済成功後（暫定）
     * 本番では Webhook で checkout.session.completed を受けて
     * Purchase作成・在庫更新(sold) を行うのが安全。
     */
    public function success(Item $item)
    {
        return view('purchases.success', compact('item'));
    }

    /**
     * キャンセル時
     */
    public function cancel(Item $item)
    {
        return redirect()->route('items.show', $item->id);
    }
}
