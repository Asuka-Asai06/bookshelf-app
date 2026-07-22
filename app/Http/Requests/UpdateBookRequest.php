<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'isbn' => [
                'required',
                'digits:13',
                Rule::unique('books', 'isbn')->ignore($this->book),
            ],
            'published_at' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:255'],
            'image_url' => ['nullable', 'url', 'max:255'],

            'genre_ids' => ['required', 'array', 'min:1'],
            'genre_ids.*' => ['exists:genres,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'タイトルを入力してください。',
            'title.max' => 'タイトルは255文字以内で入力してください。',

            'author.required' => '著者名を入力してください。',
            'author.max' => '著者名は255文字以内で入力してください。',

            'isbn.required' => 'ISBNを入力してください。',
            'isbn.digits' => 'ISBNは13桁の数字で入力してください。',
            'isbn.unique' => 'このISBNは既に登録されています。',

            'published_at.required' => '出版日を入力してください。',
            'published_at.date' => '出版日は正しい日付形式で入力してください。',

            'description.max' => '説明は255文字以内で入力してください。',

            'image_url.max' => '画像URLは255文字以内で入力してください。',
            'image_url.url' => 'URL形式が不正です。',

            'genre_ids.required' => 'ジャンルを選択してください。',
            'genre_ids.array' => 'ジャンルの形式が不正です。',
            'genre_ids.*.exists' => '存在しないジャンルが選択されています。',
        ];
    }
}
