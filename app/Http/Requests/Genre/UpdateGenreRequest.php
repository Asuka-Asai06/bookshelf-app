<?php

namespace App\Http\Requests\Genre;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGenreRequest extends FormRequest
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
                Rule::unique('genres', 'name')->ignore($this->genre),
            ],
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
