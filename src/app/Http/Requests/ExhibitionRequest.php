<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExhibitionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'], // DB列想定で max 付与。厳密仕様なら max を外す
            'description' => ['required', 'string', 'max:255'],
            'image'       => ['required', 'file', 'mimes:jpeg,png'],
            'categories'  => ['required', 'array', 'min:1'],
            'categories.*'=> ['integer', 'exists:categories,id'],
            'condition'   => ['required'/*, Rule::in(['new','like_new','used','bad'])*/],
            'price'       => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'        => '商品名を入力してください',
            'title.max'             => '商品名は255文字以内で入力してください',
            'description.required'  => '商品説明を入力してください',
            'description.max'       => '商品説明は255文字以内で入力してください',
            'image.required'        => '商品画像をアップロードしてください',
            'image.mimes'           => '商品画像は「.jpeg」または「.png」をアップロードしてください',
            'categories.required'   => '商品のカテゴリーを選択してください',
            'categories.array'      => '商品のカテゴリーの形式が不正です',
            'categories.min'        => '商品のカテゴリーを最低1つ選択してください',
            'categories.*.exists'   => '選択したカテゴリーが存在しません',
            'condition.required'    => '商品の状態を選択してください',
            'price.required'        => '商品価格を入力してください',
            'price.integer'         => '商品価格は数値で入力してください',
            'price.min'             => '商品価格は0円以上で入力してください',
        ];
    }

    public function attributes(): array
    {
        return [
            'title'       => '商品名',
            'description' => '商品説明',
            'image'       => '商品画像',
            'categories'  => '商品のカテゴリー',
            'condition'   => '商品の状態',
            'price'       => '商品価格',
        ];
    }
}
