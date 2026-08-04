<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;

class GoogleBooksService
{
    /**
     * ISBNから書籍情報を取得する。
     *
     * @param  string  $isbn  ISBN
     * @return array|null 書籍情報。見つからない場合はnull
     */
    public function searchByIsbn(string $isbn): ?array
    {
        $response = Http::get(
            'https://www.googleapis.com/books/v1/volumes',
            [
                'q' => "isbn:{$isbn}",
                'key' => config('services.google_books.key'),
            ]
        );

        // API利用制限エラー
        if ($response->status() === 429) {
            throw new Exception(
                'Google Books APIの利用制限に達しました。しばらく時間をおいて再度お試しください。'
            );
        }

        // その他のAPIエラー
        if (! $response->successful()) {
            throw new Exception(
                '書籍情報の取得に失敗しました。'
            );
        }

        $data = $response->json();

        if (($data['totalItems'] ?? 0) === 0) {
            return null;
        }

        $volumeInfo = $data['items'][0]['volumeInfo'];

        return [
            'title' => $volumeInfo['title'] ?? null,
            'author' => $volumeInfo['authors'][0] ?? null,
            'published_date' => $volumeInfo['publishedDate'] ?? null,
            'description' => $volumeInfo['description'] ?? null,
            'image_url' => $volumeInfo['imageLinks']['thumbnail'] ?? null,
        ];
    }
}
