<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string'],
            'postal_code' => ['required', 'regex:/^\d{3}-\d{4}$/'],
            'address'     => ['nullable', 'string'],
            'building'    => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'        => 'お名前を入力してください',
            'postal_code.required' => '郵便番号を入力してください',
            'postal_code.regex'    => '郵便番号は「123-4567」の形式で入力してください',
        ];
    }

    public function attributes(): array
    {
        return [
            'name'        => 'お名前',
            'postal_code' => '郵便番号',
            'address'     => '住所',
            'building'    => '建物名',
        ];
    }
}
