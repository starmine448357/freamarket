<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionMessageRequest extends FormRequest
{
    /**
     * 認可（権限チェックはコントローラー側で実施済み）
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
            'message' => ['required', 'string', 'max:400'],
            'image'   => ['nullable', 'file', 'mimes:jpeg,png'],
        ];
    }

    /**
     * エラーメッセージ
     */
    public function messages(): array
    {
        return [
            'message.required' => '本文を入力してください',
            'message.max'      => '本文は400文字以内で入力してください',

            'image.mimes'      => '「.png」または「.jpeg」形式でアップロードしてください',
        ];
    }
}
