<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExhibitionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required',
            'description' => 'required|max:255',
            'img' => 'required|mimes:jpeg,png|extensions:jpeg,png|',
            'categories' => 'required|array',
            'categories.*' => 'exists:categories,id',
            'condition' => 'required',
            'price' => 'required|integer|min:0',
            'brand' => 'nullable|string|max:255',
            ];
    }

    public function messages()
    {
        return [
            'name.required' => '商品名を入力してください',
            'description.required' => '商品説明を入力してください',
            'description.max' => '商品説明は255文字以内で入力してください',
            'img.required' => '商品画像をアップロードしてください',
            'img.mimes' => '画像はjpegまたはpng形式でアップロードしてください',
            'categories.required' => 'カテゴリーを選択してください',
            'condition.required' => '商品の状態を選択してください',
            'price.required' => '価格を入力してください',
            'price.integer' => '価格は整数で入力してください',
            'price.min' => '価格は0以上で入力してください',
            'brand.string' => '建物は文字列で入力してください',
            'brand.max' => '建物は255文字以内で入力してください',
        ];
    }
}
