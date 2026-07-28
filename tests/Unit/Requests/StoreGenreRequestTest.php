<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\Genre\StoreGenreRequest;
use App\Models\Genre;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Tests\TestCase;

class StoreGenreRequestTest extends TestCase
{
    use RefreshDatabase;

    private function validData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Laravel',
        ], $overrides);
    }

    private function validator(array $data): Validator
    {
        $request = new StoreGenreRequest;

        return ValidatorFacade::make(
            $data,
            $request->rules(),
            $request->messages()
        );
    }

    public function test_ジャンル名が入力されていればバリデーションを通過する(): void
    {
        $validator = $this->validator(
            $this->validData()
        );

        $this->assertTrue($validator->passes());
    }

    public function test_ジャンル名が未入力ならエラーになる(): void
    {
        $validator = $this->validator(
            $this->validData([
                'name' => '',
            ])
        );

        $this->assertFalse($validator->passes());

        $this->assertTrue(
            $validator->errors()->has('name')
        );
    }

    public function test_ジャンル名が255文字を超えるとエラーになる(): void
    {
        $validator = $this->validator(
            $this->validData([
                'name' => str_repeat('あ', 256),
            ])
        );

        $this->assertFalse($validator->passes());

        $this->assertTrue(
            $validator->errors()->has('name')
        );
    }

    public function test_ジャンル名が重複しているとエラーになる(): void
    {
        Genre::factory()->create([
            'name' => 'Laravel',
        ]);

        $validator = $this->validator(
            $this->validData([
                'name' => 'Laravel',
            ])
        );

        $this->assertFalse($validator->passes());

        $this->assertTrue(
            $validator->errors()->has('name')
        );
    }
}
