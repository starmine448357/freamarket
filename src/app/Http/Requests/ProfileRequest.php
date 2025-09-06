<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            // プロフィール画像：拡張子が.jpeg もしくは .png
            // （サイズ制限が不要なら max は付けない）
            'avatar' => ['nullable', 'file', 'mimes:jpeg,png'],
        ];
    }

    public function messages(): array
    {
        return [
            'avatar.mimes' => 'プロフィール画像は .jpeg または .png を選択してください',
        ];
    }
}
