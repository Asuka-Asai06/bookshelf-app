<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\Book\UpdateBookRequest;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdateBookRequestTest extends TestCase
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
            'title' => 'Laravel入門',
            'author' => '山田太郎',
            'isbn' => '9781234567890',
            'published_date' => now()->toDateString(),
            'description' => '説明文',
            'image_url' => 'https://example.com/image.jpg',
            'genres' => [$this->genre->id],
        ], $overrides);
    }

    private function validator(array $data)
    {
        $request = new UpdateBookRequest;

        return Validator::make(
            $data,
            $request->rules(),
            $request->messages()
        );
    }

    public function test_必須項目が全て入力されていればバリデーションを通過する(): void
    {
        $validator = $this->validator($this->validData());

        $this->assertTrue($validator->passes());
    }

    public function test_タイトルが未入力ならエラーになる(): void
    {
        $validator = $this->validator(
            $this->validData([
                'title' => '',
            ])
        );

        $this->assertTrue($validator->errors()->has('title'));
    }

    public function test_タイトルが255文字を超えるとエラーになる(): void
    {
        $validator = $this->validator(
            $this->validData([
                'title' => str_repeat('あ', 256),
            ])
        );

        $this->assertTrue($validator->errors()->has('title'));
    }

    public function test_著者名が未入力ならエラーになる(): void
    {
        $validator = $this->validator(
            $this->validData([
                'author' => '',
            ])
        );

        $this->assertTrue($validator->errors()->has('author'));
    }

    public function test_著者名が255文字を超えるとエラーになる(): void
    {
        $validator = $this->validator(
            $this->validData([
                'author' => str_repeat('あ', 256),
            ])
        );

        $this->assertTrue($validator->errors()->has('author'));
    }

    public function test_isbnが未入力ならエラーになる(): void
    {
        $validator = $this->validator(
            $this->validData([
                'isbn' => '',
            ])
        );

        $this->assertTrue($validator->errors()->has('isbn'));
    }

    public function test_isbnが13桁でなければエラーになる(): void
    {
        $validator = $this->validator(
            $this->validData([
                'isbn' => '123456789',
            ])
        );

        $this->assertTrue($validator->errors()->has('isbn'));
    }

    public function test_登録済みisbnならエラーになる(): void
    {
        Book::factory()->create([
            'isbn' => '9781234567890',
        ]);

        $validator = $this->validator(
            $this->validData([
                'isbn' => '9781234567890',
            ])
        );

        $this->assertTrue($validator->errors()->has('isbn'));
    }

    public function test_出版日が未来日ならエラーになる(): void
    {
        $validator = $this->validator(
            $this->validData([
                'published_date' => now()->addDay()->toDateString(),
            ])
        );

        $this->assertTrue($validator->errors()->has('published_date'));
    }

    public function test_説明が1000文字を超えるとエラーになる(): void
    {
        $validator = $this->validator(
            $this->validData([
                'description' => str_repeat('あ', 1001),
            ])
        );

        $this->assertTrue($validator->errors()->has('description'));
    }

    public function test_画像urlがurl形式でなければエラーになる(): void
    {
        $validator = $this->validator(
            $this->validData([
                'image_url' => 'abc',
            ])
        );

        $this->assertTrue($validator->errors()->has('image_url'));
    }

    public function test_画像urlが255文字を超えるとエラーになる(): void
    {
        $url = 'https://example.com/'.str_repeat('a', 236);

        $validator = $this->validator(
            $this->validData([
                'image_url' => $url,
            ])
        );

        $this->assertTrue($validator->errors()->has('image_url'));
    }

    public function test_ジャンルが未選択ならエラーになる(): void
    {
        $validator = $this->validator(
            $this->validData([
                'genres' => [],
            ])
        );

        $this->assertTrue($validator->errors()->has('genres'));
    }

    public function test_存在しないジャンルidならエラーになる(): void
    {
        $validator = $this->validator(
            $this->validData([
                'genres' => [999],
            ])
        );

        $this->assertTrue($validator->errors()->has('genres.0'));
    }
}
