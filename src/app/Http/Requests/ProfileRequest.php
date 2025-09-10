<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'avatar' => ['nullable', 'file', 'mimes:jpeg,png'], // 画像性のチェックだけでOKなら image に変更可
        ];
    }

    public function messages(): array
    {
        return [
            'avatar.mimes' => 'プロフィール画像は「.jpeg」または「.png」をアップロードしてください',
        ];
    }

    public function attributes(): array
    {
        return ['avatar' => 'プロフィール画像'];
    }
}
