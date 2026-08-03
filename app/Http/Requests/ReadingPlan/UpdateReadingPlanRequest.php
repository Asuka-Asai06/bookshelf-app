<?php

namespace App\Http\Requests\ReadingPlan;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateReadingPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'book_id' => [
                'required',
                'exists:books,id',
                Rule::unique('reading_plans')
                    ->where('user_id', auth()->id())
                    ->ignore($this->reading_plan),
            ],
            'target_date' => ['required', 'date', 'after_or_equal:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'target_date.required' => '期日を選択してください',
            'target_date.after_or_equal' => '期日は今日以降の日付を選択してください。',
        ];
    }
}
