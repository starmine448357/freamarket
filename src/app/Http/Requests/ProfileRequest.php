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
            'profile_image' => ['nullable', 'file', 'mimes:jpeg,png'],
        ];
    }

    public function messages(): array
    {
        return [
            'profile_image.file'  => 'プロフィール画像のアップロードに失敗しました',
            'profile_image.mimes' => 'プロフィール画像は「.jpeg」または「.png」をアップロードしてください',
        ];
    }

    public function attributes(): array
    {
        return [
            'profile_image' => 'プロフィール画像',
        ];
    }
}
