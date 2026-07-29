<?php

namespace App\Http\Requests\Book;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'isbn' => ['nullable', 'digits:13', 'unique:books,isbn'],
            'published_date' => ['nullable', 'date', 'before_or_equal:today'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image_url' => ['nullable', 'url', 'max:255'],

            'genres' => ['required', 'array', 'min:1'],
            'genres.*' => ['exists:genres,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'タイトルを入力してください。',
            'title.max' => 'タイトルは255文字以内で入力してください。',

            'author.required' => '著者名を入力してください。',
            'author.max' => '著者名は255文字以内で入力してください。',

            'isbn.digits' => 'ISBNは13桁の数字で入力してください。',
            'isbn.unique' => 'このISBNは既に登録されています。',

            'published_date.date' => '出版日は正しい日付形式で入力してください。',
            'published_date.before_or_equal' => '出版日は今日以前の日付を入力してください。',

            'description.max' => '説明は1000文字以内で入力してください。',

            'image_url.max' => '画像URLは255文字以内で入力してください。',
            'image_url.url' => 'URL形式が不正です。',

            'genres.required' => 'ジャンルを選択してください。',
            'genres.array' => 'ジャンルの形式が不正です。',
            'genres.*.exists' => '存在しないジャンルが選択されています。',
        ];
    }
}
