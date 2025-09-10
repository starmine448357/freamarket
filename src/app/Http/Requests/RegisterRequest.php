<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'                  => ['required', 'string', 'max:255'],  // ← ここ追加！
            'email'                 => ['required', 'email', 'unique:users,email'],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required', 'string', 'min:8'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'                  => 'お名前を入力してください',

            'email.required'                 => 'メールアドレスを入力してください',
            'email.email'                    => 'メールアドレスの形式が正しくありません',
            'email.unique'                   => 'このメールアドレスは既に登録されています',

            'password.required'              => 'パスワードを入力してください',
            'password.min'                   => 'パスワードは8文字以上で入力してください',
            'password.confirmed'             => 'パスワードと一致しません',

            'password_confirmation.required' => '確認用パスワードを入力してください',
            'password_confirmation.min'      => '確認用パスワードは8文字以上で入力してください',
        ];
    }

    public function attributes(): array
    {
        return [
            'name'                  => 'ユーザー名',   // ← フォームに合わせて「ユーザー名」
            'email'                 => 'メールアドレス',
            'password'              => 'パスワード',
            'password_confirmation' => '確認用パスワード',
        ];
    }
}
