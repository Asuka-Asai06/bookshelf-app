<?php

namespace Tests\Unit\Http\Requests\Api\V1;

use App\Http\Requests\Api\V1\IndexBookRequest;
use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class IndexBookRequestTest extends TestCase
{
    use RefreshDatabase;

    protected Genre $genre;

    protected function setUp(): void
    {
        parent::setUp();

        $this->genre = Genre::factory()->create();
    }

    private function validData(array $overrides = []): array
    {
        return array_merge([
            'keyword' => 'Laravel',
            'genre_ids' => [$this->genre->id],
            'per_page' => 20,
        ], $overrides);
    }

    private function validator(array $data)
    {
        $request = new IndexBookRequest;

        return Validator::make(
            $data,
            $request->rules()
        );
    }

    public function test_検索条件が正しければバリデーションを通過する(): void
    {
        $validator = $this->validator(
            $this->validData()
        );

        $this->assertTrue($validator->passes());
    }

    public function test_検索条件が未入力でもバリデーションを通過する(): void
    {
        $validator = $this->validator([]);

        $this->assertTrue($validator->passes());
    }

    public function test_キーワードが255文字を超えるとエラーになる(): void
    {
        $validator = $this->validator(
            $this->validData([
                'keyword' => str_repeat('あ', 256),
            ])
        );

        $this->assertFalse($validator->passes());

        $this->assertTrue(
            $validator->errors()->has('keyword')
        );
    }

    public function test_ジャンルidが配列でなければエラーになる(): void
    {
        $validator = $this->validator(
            $this->validData([
                'genre_ids' => 1,
            ])
        );

        $this->assertFalse($validator->passes());

        $this->assertTrue(
            $validator->errors()->has('genre_ids')
        );
    }

    public function test_存在しないジャンルidならエラーになる(): void
    {
        $validator = $this->validator(
            $this->validData([
                'genre_ids' => [999],
            ])
        );

        $this->assertFalse($validator->passes());

        $this->assertTrue(
            $validator->errors()->has('genre_ids.0')
        );
    }

    public function test_per_pageが整数でなければエラーになる(): void
    {
        $validator = $this->validator(
            $this->validData([
                'per_page' => 'abc',
            ])
        );

        $this->assertFalse($validator->passes());

        $this->assertTrue(
            $validator->errors()->has('per_page')
        );
    }

    public function test_per_pageが1未満ならエラーになる(): void
    {
        $validator = $this->validator(
            $this->validData([
                'per_page' => 0,
            ])
        );

        $this->assertFalse($validator->passes());

        $this->assertTrue(
            $validator->errors()->has('per_page')
        );
    }

    public function test_per_pageが100を超えるとエラーになる(): void
    {
        $validator = $this->validator(
            $this->validData([
                'per_page' => 101,
            ])
        );

        $this->assertFalse($validator->passes());

        $this->assertTrue(
            $validator->errors()->has('per_page')
        );
    }
}
