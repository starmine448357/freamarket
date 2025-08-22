<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PurchaseController extends Controller
{
    // 購入フォーム
    public function create(Item $item)
    {
        abort_if($item->status === 'sold', 404);
        return view('purchases.create', compact('item'));
    }

    // 購入処理（決済連携は後日）
    public function store(Request $request, Item $item)
    {
        abort_if($item->status === 'sold', 404);

        $validated = $request->validate([
            'payment_method'       => ['required', Rule::in(['credit_card','convenience_store','bank_transfer'])],
            'shipping_postal_code' => ['required','string','max:20'],
            'shipping_address'     => ['required','string','max:255'],
            'shipping_building'    => ['nullable','string','max:255'],
        ]);

        Purchase::create([
            'user_id'              => Auth::id(),
            'item_id'              => $item->id,
            'payment_method'       => $validated['payment_method'],
            'amount'               => $item->price,
            'shipping_postal_code' => $validated['shipping_postal_code'],
            'shipping_address'     => $validated['shipping_address'],
            'shipping_building'    => $validated['shipping_building'] ?? null,
            'status'               => 'pending',
            'paid_at'              => null,
        ]);

        // 在庫状態を「sold」に
        $item->update(['status' => 'sold']);

        return redirect()->route('items.show', $item)->with('success', '購入を受け付けました。');
    }
}
