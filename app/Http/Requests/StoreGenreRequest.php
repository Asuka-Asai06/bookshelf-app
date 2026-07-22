<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGenreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:genres,name', ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'ジャンルを入力してください。',
            'name.max' => 'ジャンルは255文字以内で入力してください。',
            'name.unique' => 'このジャンルは既に登録されています。',
        ];
    }
}
