<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequest extends FormRequest
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
            'payment_method' => 'required',
            'price' => 'nullable|integer',
            'post_code' => 'required',
            'address' => 'required',
            'building' => 'nullable',
            'payment_status' => 'nullable',
            'is_deleted' => 'nullable'
            ];
    }

    public function messages()
    {
        return [
            'payment_method.required' => '支払い方法を入力してください',
            'post_code.required' => '配送先住所を入力してください',
            'address.required' => '配送先住所を入力してください'
        ];
    }
}
