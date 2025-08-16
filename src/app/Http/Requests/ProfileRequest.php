<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
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
            'img' => 'mimes:jpeg,png| extensions:jpeg,png',
            'name' => 'required|max:20',
            'post_code' => 'required|regex:^\d{3}-\d{4}$',
            'address' => 'required',
            ];
    }

    public function messages()
    {
        return [
            'img.mimes' => '画像はjpegまたはpng形式でアップロードしてください',
            'img.extensions' => '画像はjpegまたはpng形式でアップロードしてください',
            'name.required' => 'お名前を入力してください',
            'name.max' => 'お名前は20文字以内で入力してください',
            'post_code.required' => '郵便番号を入力してください',
            'post_code.regex' => '郵便番号はハイフンを含め8文字で入力してください',
            'address.required' => '住所を入力してください',
        ];
    }
}
