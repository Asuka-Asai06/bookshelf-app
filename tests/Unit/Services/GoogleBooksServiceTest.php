<?php

namespace Tests\Unit\Services;

use App\Services\GoogleBooksService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleBooksServiceTest extends TestCase
{
    use RefreshDatabase;

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

        $service = new GoogleBooksService;

        $result = $service->searchByIsbn('9784163238609');

        $this->assertSame([
            'title' => '吾輩は猫である',
            'author' => '夏目漱石',
            'published_date' => '1905-01-01',
            'description' => '小説です。',
            'image_url' => 'https://example.com/image.jpg',
        ], $result);
    }

    public function test_書籍が存在しない場合はnullを返す(): void
    {
        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response([
                'totalItems' => 0,
            ], 200),
        ]);

        $service = new GoogleBooksService;

        $result = $service->searchByIsbn('9999999999999');

        $this->assertNull($result);
    }

    public function test_ap_i通信に失敗した場合はnullを返す(): void
    {
        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response([], 500),
        ]);

        $service = new GoogleBooksService;

        $result = $service->searchByIsbn('9784163238609');

        $this->assertNull($result);
    }
}
