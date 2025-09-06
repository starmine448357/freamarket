<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string'],
            // 郵便番号：ハイフンありの8文字（例：123-4567）
            'postal_code' => ['required', 'string', 'size:8', 'regex:/^\d{3}-\d{4}$/'],
            'address'     => ['nullable', 'string'],
            'building'    => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'お名前を入力してください',

            'postal_code.required' => '郵便番号を入力してください',
            'postal_code.size'     => '郵便番号はハイフンを含めて8文字で入力してください（例：123-4567）',
            'postal_code.regex'    => '郵便番号の形式が正しくありません（例：123-4567）',
        ];
    }
}
