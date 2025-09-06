<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExhibitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        // 認証済みであれば許可（routes側でauth/verifiedを掛けている前提）
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            // 画像：アップロード必須（temp方式 or 直接アップロードのどちらか）
            'temp_image'   => ['required_without:image', 'string'],
            'image'        => ['required_without:temp_image', 'file', 'mimes:jpeg,png', 'max:5120'],

            // カテゴリー：選択必須
            'categories'   => ['required', 'array', 'min:1'],
            'categories.*' => ['integer', 'exists:categories,id'],

            // 状態：選択必須
            'condition'    => ['required', Rule::in(['new','like_new','used','bad'])],

            // 商品名：入力必須
            'title'        => ['required', 'string', 'max:255'],

            // 商品説明：入力必須、最大255文字
            'description'  => ['required', 'string', 'max:255'],

            // 価格：入力必須、数値、0円以上
            'price'        => ['required', 'integer', 'min:0'],

            // 任意項目
            'brand'        => ['nullable', 'string', 'max:255'],
            'status'       => ['nullable', Rule::in(['selling','sold'])],
        ];
    }

    public function messages(): array
    {
        return [
            // 画像
            'temp_image.required_without' => '商品画像を登録してください',
            'image.required_without'      => '商品画像を登録してください',
            'image.mimes'                 => '「.png」または「.jpeg」形式でアップロードしてください',
            'image.max'                   => '画像サイズは5MB以内でアップロードしてください',

            // カテゴリー
            'categories.required'         => 'カテゴリーを選択してください',
            'categories.min'              => 'カテゴリーを1つ以上選択してください',
            'categories.*.exists'         => '存在しないカテゴリーが含まれています',

            // 状態
            'condition.required'          => '商品の状態を選択してください',
            'condition.in'                => '商品の状態の値が不正です',

            // 商品名・説明・価格
            'title.required'              => '商品名を入力してください',
            'description.required'        => '商品の説明を入力してください',
            'description.max'             => '商品の説明は255文字以内で入力してください',
            'price.required'              => '販売価格を入力してください',
            'price.integer'               => '販売価格は数値で入力してください',
            'price.min'                   => '販売価格は0円以上で入力してください',
        ];
    }
}
