<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Purchase;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\PurchaseRequest;
use Illuminate\Support\Facades\Log;

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
        // 「確認」の場合は戻す
        if ($request->input('action') === 'confirm') {
            return back()->withInput();
        }

        $validated = $request->validated();
        $payment   = $validated['payment'];
        $postal    = $validated['shipping_postal_code'];
        $address   = $validated['shipping_address'];
        $building  = $validated['shipping_building'] ?? '';

        try {
            $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));

            // Stripe セッション生成
            $session = $stripe->checkout->sessions->create([
                'mode' => 'payment',

                'payment_method_types' => $payment === 'card'
                    ? ['card']
                    : ['konbini'],

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

                'success_url' => url('/purchase/' . $item->id . '/success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'  => route('purchases.cancel', $item),

                'metadata' => [
                    'item_id'  => (string) $item->id,
                    'buyer_id' => (string) Auth::id(),
                    'payment'  => $payment,
                    'postal'   => $postal,
                    'address'  => $address,
                    'building' => $building,
                ],
            ]);

            return redirect()->away($session->url);
        } catch (\Throwable $e) {

            Log::error('Stripe checkout error', [
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
     * 決済成功
     */
    public function success(Item $item)
    {
        $sessionId = request()->query('session_id');

        if (!$sessionId) {
            return redirect()->route('items.show', $item->id);
        }

        $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
        $session = $stripe->checkout->sessions->retrieve($sessionId);

        $metadata = $session->metadata;
        $buyerId  = Auth::id();

        // レコードがなければ新規作成
        if (!Purchase::where('item_id', $metadata->item_id)->exists()) {
            Purchase::create([
                'user_id'              => $item->user_id,
                'buyer_id'             => $buyerId,
                'item_id'              => $metadata->item_id,
                'payment_method'       => $metadata->payment,
                'amount'               => $item->price,
                'shipping_postal_code' => $metadata->postal,
                'shipping_address'     => $metadata->address,
                'shipping_building'    => $metadata->building,

                // 初期ステータス：pending
                'status'               => 'pending',

                'paid_at' => now(),
            ]);
        }

        // Item は購入されたので sold
        $item->update([
            'status' => 'sold',
        ]);

        return redirect()->route('mypage');
    }

    /**
     * キャンセル時
     */
    public function cancel(Item $item)
    {
        return redirect()->route('items.show', $item->id);
    }
}
