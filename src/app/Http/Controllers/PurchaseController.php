<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\PurchaseRequest;

class PurchaseController extends Controller
{
    /**
     * 購入フォーム表示
     */
    public function create(Item $item)
    {
        // 売却済み商品は 404
        if (($item->status ?? null) === 'sold') {
            abort(404);
        }

        // 自分の商品は購入不可（403）
        if (Auth::id() === $item->user_id) {
            abort(403);
        }

        return view('purchases.create', compact('item'));
    }

    /**
     * Stripe Checkout へ遷移
     */
    public function store(PurchaseRequest $request, Item $item)
    {
        // 確認ボタン時 → そのまま戻す
        if ($request->input('action') === 'confirm') {
            return back()->withInput();
        }

        $validated = $request->validated();
        $payment   = $validated['payment'];
        $postal    = $validated['shipping_postal_code'];
        $address   = $validated['shipping_address'];
        $building  = $validated['shipping_building'] ?? '';

        try {
            // Stripe Checkout セッション作成
            $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));

            $session = $stripe->checkout->sessions->create([
                'mode' => 'payment',
                'payment_method_types' => $payment === 'card' ? ['card'] : ['konbini'],
                'customer_email' => optional(Auth::user())->email,
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'jpy',
                        'unit_amount' => (int) $item->price,
                        'product_data' => [
                            'name' => $item->title,
                        ],
                    ],
                    'quantity' => 1,
                ]],
                'success_url' => route('purchases.success', $item) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'  => route('purchases.cancel', $item),
                'metadata'    => [
                    'item_id'   => (string) $item->id,
                    'buyer_id'  => (string) Auth::id(),
                    'payment'   => $payment,
                    'postal'    => $postal,
                    'address'   => $address,
                    'building'  => $building,
                ],
            ]);

            return redirect()->away($session->url);

        } catch (\Throwable $e) {
            \Log::error('Stripe checkout error', [
                'message'  => $e->getMessage(),
                'item_id'  => $item->id,
                'buyer_id' => Auth::id(),
                'payment'  => $payment,
            ]);

            return back()
                ->withErrors(['payment' => '決済の開始に失敗しました: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * 決済成功後（暫定）
     * 本番では Webhook で checkout.session.completed を受けて処理するのが安全
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
