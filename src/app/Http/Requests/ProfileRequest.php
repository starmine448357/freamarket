<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
{
    /**
     * 認可判定
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * バリデーションルール
     */
    public function rules(): array
    {
        return [
            'profile_image' => ['nullable', 'file', 'mimes:jpeg,png'],
        ];
    }

    /**
     * エラーメッセージ
     */
    public function messages(): array
    {
        return [
            'profile_image.file'  => 'プロフィール画像のアップロードに失敗しました',
            'profile_image.mimes' => 'プロフィール画像は「.jpeg」または「.png」をアップロードしてください',
        ];
    }

    /**
     * 属性名（フォームラベル用）
     */
    public function attributes(): array
    {
        return [
            'profile_image' => 'プロフィール画像',
        ];
    }
}
