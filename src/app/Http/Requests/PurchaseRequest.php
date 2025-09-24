<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequest extends FormRequest
{
    /**
     * 認可判定
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * バリデーションルール
     */
    public function rules(): array
    {
        return [
            'payment'              => ['required'],
            'shipping_postal_code' => ['required', 'regex:/^\d{3}-\d{4}$/'],
            'shipping_address'     => ['required', 'string'],
            'shipping_building'    => ['nullable', 'string'],
        ];
    }

    /**
     * エラーメッセージ
     */
    public function messages(): array
    {
        return [
            'payment.required'              => '支払い方法を選択してください',
            'shipping_postal_code.required' => '郵便番号を入力してください',
            'shipping_postal_code.regex'    => '郵便番号は「123-4567」の形式で入力してください',
            'shipping_address.required'     => '住所を入力してください',
        ];
    }

    /**
     * 属性名（フォームラベル用）
     */
    public function attributes(): array
    {
        return [
            'payment'              => '支払い方法',
            'shipping_postal_code' => '郵便番号',
            'shipping_address'     => '住所',
            'shipping_building'    => '建物名',
        ];
    }
}
