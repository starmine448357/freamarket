<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            // 支払い方法：選択必須（候補が固定なら in: を追加してOK）
            'payment_method' => ['required', 'string'],

            // 配送先：選択必須（保存済み住所を選ぶ想定なら exists を有効化）
            'address_id'     => ['required', 'integer'],
            // 例）addresses テーブルがあるなら：
            // 'address_id'  => ['required', 'integer', 'exists:addresses,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'payment_method.required' => '支払い方法を選択してください',

            'address_id.required' => '配送先を選択してください',
            'address_id.integer'  => '配送先の指定が不正です',
            // 'address_id.exists' => '選択した配送先が見つかりません',
        ];
    }
}
