<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // 登録前でも利用するため
    }

    public function rules(): array
    {
        return [
            'email'                 => ['required', 'email'],
            'password'              => ['required', 'string', 'min:8'],
            // 「確認用パスワード」= パスワードと一致のみ可
            'password_confirmation' => ['required', 'string', 'min:8', 'same:password'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'メールアドレスを入力してください',
            'email.email'    => 'メールアドレスの形式が正しくありません',

            'password.required' => 'パスワードを入力してください',
            'password.min'      => 'パスワードは8文字以上で入力してください',

            'password_confirmation.required' => '確認用パスワードを入力してください',
            'password_confirmation.min'      => '確認用パスワードは8文字以上で入力してください',
            'password_confirmation.same'     => '確認用パスワードが一致しません',
        ];
    }
}
