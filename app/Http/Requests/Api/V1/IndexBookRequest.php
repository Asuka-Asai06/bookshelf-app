<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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

            'genre' => [
                'nullable',
                Rule::exists('genres', 'name'),
            ],

            'per_page' => ['nullable', 'integer', 'between:1,100'],
        ];
    }
}
