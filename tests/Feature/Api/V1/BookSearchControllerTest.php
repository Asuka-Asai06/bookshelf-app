<?php

namespace Tests\Feature\Api\V1;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BookSearchControllerTest extends TestCase
{
    public function test_isbnから書籍情報を取得できる(): void
    {
        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response([
                'totalItems' => 1,
                'items' => [
                    [
                        'volumeInfo' => [
                            'title' => '吾輩は猫である',
                            'authors' => [
                                '夏目漱石',
                            ],
                            'publishedDate' => '1905-01-01',
                            'description' => '小説です。',
                            'imageLinks' => [
                                'thumbnail' => 'https://example.com/image.jpg',
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->getJson(
            route('api.v1.books.search', [
                'isbn' => '9784163238609',
            ])
        );

        $response
            ->assertOk()
            ->assertJson([
                'title' => '吾輩は猫である',
                'author' => '夏目漱石',
                'published_date' => '1905-01-01',
                'description' => '小説です。',
                'image_url' => 'https://example.com/image.jpg',
            ]);
    }

    public function test_存在しないisbnの場合404を返す(): void
    {
        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response([
                'totalItems' => 0,
            ], 200),
        ]);

        $response = $this->getJson(
            route('api.v1.books.search', [
                'isbn' => '9999999999999',
            ])
        );

        $response
            ->assertNotFound()
            ->assertJson([
                'message' => '書籍が見つかりません。',
            ]);
    }

    public function test_googlebooksapiエラー時は404を返す(): void
    {
        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response([], 500),
        ]);

        $response = $this->getJson(
            route('api.v1.books.search', [
                'isbn' => '9784163238609',
            ])
        );

        $response
            ->assertNotFound()
            ->assertJson([
                'message' => '書籍が見つかりません。',
            ]);
    }
}
