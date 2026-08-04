<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\GoogleBooksService;
use Exception;
use Illuminate\Http\JsonResponse;

class BookSearchController extends Controller
{
    public function __construct(private GoogleBooksService $googleBooksService) {}

    /**
     * ISBNから書籍情報を取得する。
     *
     * @param  string  $isbn  検索対象のISBN
     * @return JsonResponse 書籍情報をJSON形式で返す
     */
    public function show(string $isbn): JsonResponse
    {
        try {
            $book = $this->googleBooksService->searchByIsbn($isbn);

            if ($book === null) {
                return response()->json([
                    'error' => '書籍が見つかりません。',
                ], 404);
            }

            return response()->json($book);

        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 429);
        }
    }
}
