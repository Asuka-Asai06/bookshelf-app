<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class IndexBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'keyword' => ['nullable', 'string', 'max:255'],

            'genre_ids' => ['nullable', 'array'],
            'genre_ids.*' => ['exists:genres,id'],

            'per_page' => ['nullable', 'integer', 'between:1,100'],
        ];
    }
}
