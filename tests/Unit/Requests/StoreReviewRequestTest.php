<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\Review\StoreReviewRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Tests\TestCase;

class StoreReviewRequestTest extends TestCase
{
    use RefreshDatabase;

    private function validData(array $overrides = []): array
    {
        return array_merge([
            'rating' => 5,
            'comment' => 'とても面白い本でした。',
        ], $overrides);
    }

    private function validator(array $data): Validator
    {
        $request = new StoreReviewRequest;

        return ValidatorFacade::make(
            $data,
            $request->rules(),
            $request->messages()
        );
    }

    public function test_必須項目が全て入力されていればバリデーションを通過する(): void
    {
        $validator = $this->validator(
            $this->validData()
        );

        $this->assertTrue($validator->passes());
    }

    public function test_評価値が未入力ならエラーになる(): void
    {
        $validator = $this->validator(
            $this->validData([
                'rating' => null,
            ])
        );

        $this->assertFalse($validator->passes());

        $this->assertTrue(
            $validator->errors()->has('rating')
        );
    }

    public function test_評価値が1から5以外ならエラーになる(): void
    {
        $validator = $this->validator(
            $this->validData([
                'rating' => 6,
            ])
        );

        $this->assertFalse($validator->passes());

        $this->assertTrue(
            $validator->errors()->has('rating')
        );
    }

    public function test_コメントが未入力ならエラーになる(): void
    {
        $validator = $this->validator(
            $this->validData([
                'comment' => '',
            ])
        );

        $this->assertFalse($validator->passes());

        $this->assertTrue(
            $validator->errors()->has('comment')
        );
    }

    public function test_コメントが255文字を超えるとエラーになる(): void
    {
        $validator = $this->validator(
            $this->validData([
                'comment' => str_repeat('あ', 256),
            ])
        );

        $this->assertFalse($validator->passes());

        $this->assertTrue(
            $validator->errors()->has('comment')
        );
    }
}
