<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'payment'    => ['required'],             // 例: in:card,convenience を追加可
            'address_id' => ['required', 'integer'],  // 必要なら exists:addresses,id など追加
        ];
    }

    public function messages(): array
    {
        return [
            'payment.required'    => '支払い方法を選択してください',
            'address_id.required' => '配送先を選択してください',
            'address_id.integer'  => '配送先の指定が不正です',
        ];
    }

    public function attributes(): array
    {
        return [
            'payment'    => '支払い方法',
            'address_id' => '配送先',
        ];
    }
}
