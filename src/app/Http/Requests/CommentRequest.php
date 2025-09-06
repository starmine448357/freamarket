<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // ルート側で auth ミドルウェアを掛けているので true でもOKだが、
        // 念のため現在の実装に合わせておく
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'max:255'], // ← フィールド名を content に統一
        ];
    }

    public function messages(): array
    {
        return [
            'content.required' => 'コメントを入力してください',
            'content.max'      => 'コメントは255文字以内で入力してください',
        ];
    }
}
