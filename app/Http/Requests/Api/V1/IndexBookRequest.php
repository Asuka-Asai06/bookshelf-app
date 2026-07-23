<?php

namespace App\Http\Requests\API\V1;

use Illuminate\Foundation\Http\FormRequest;

class IndexBookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
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
