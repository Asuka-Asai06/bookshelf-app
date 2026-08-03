<?php

namespace App\Http\Requests\Review;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'in:1,2,3,4,5'],
            'comment' => ['required', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $exists = $this->route('book')
                ->reviews()
                ->where('user_id', auth()->id())
                ->exists();

            if ($exists) {
                $validator->errors()->add(
                    'comment',
                    'この書籍にはすでにレビューを投稿しています。'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'rating.required' => '評価値を入力してください。',
            'comment.required' => 'コメントを入力してください。',
            'comment.max' => 'コメントは255文字以内で入力してください。',
        ];
    }
}
