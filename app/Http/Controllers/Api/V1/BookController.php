<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\V1\IndexBookRequest;
use App\Http\Requests\API\V1\StoreBookRequest;
use App\Http\Requests\API\V1\UpdateBookRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
    /**
     * 書籍一覧を取得する。
     *
     * キーワードやジャンルで絞り込み、ページネーションした結果を返す。
     *
     * @param  IndexBookRequest  $request  検索条件を含むリクエスト
     */
    public function index(IndexBookRequest $request): AnonymousResourceCollection
    {
        $books = Book::query()
            ->with(['user', 'genres'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')

            ->when(
                $request->filled('keyword'),
                function ($query) use ($request) {
                    $keyword = $request->keyword;

                    $query->where(function ($query) use ($keyword) {
                        $query->where('title', 'like', "%{$keyword}%")
                            ->orWhere('author', 'like', "%{$keyword}%");
                    });
                }
            )
            ->when(
                $request->filled('genre'),
                function ($query) use ($request) {
                    $query->whereHas(
                        'genres',
                        fn ($query) => $query->where(
                            'name',
                            $request->genre
                        )
                    );
                }
            )

            ->latest()
            ->paginate(
                $request->integer('per_page', 20)
            );

        return BookResource::collection($books);
    }

    /**
     * 新しい書籍を登録し、登録した書籍情報を返す。
     *
     * @param  StoreBookRequest  $request  バリデーション済みのリクエスト
     */
    public function store(StoreBookRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $book = DB::transaction(function () use ($validated) {

            $book = Book::create(
                collect($validated)
                    ->except('genres')
                    ->merge([
                        'user_id' => auth()->id(),
                    ])
                    ->all()
            );

            $book->genres()->attach($validated['genres']);

            return $book;
        });

        $book->load([
            'user',
            'genres',
        ]);

        return (new BookResource($book))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * 指定した書籍の詳細情報を取得する。
     *
     * @param  Book  $book  取得対象の書籍
     */
    public function show(Book $book): BookResource
    {
        $book->load([
            'user',
            'genres',
        ])->loadAvg('reviews', 'rating')
            ->loadCount('reviews');

        return new BookResource($book);
    }

    /**
     * 指定した書籍情報を更新し、更新後の書籍情報を返す。
     *
     * @param  UpdateBookRequest  $request  バリデーション済みのリクエスト
     * @param  Book  $book  更新対象の書籍
     */
    public function update(UpdateBookRequest $request, Book $book): JsonResponse
    {
        $this->authorize('update', $book);

        $validated = $request->validated();

        DB::transaction(function () use ($book, $validated) {

            $book->update(
                collect($validated)
                    ->except('genres')
                    ->all()
            );

            $book->genres()->sync(
                $validated['genres'] ?? $book->genres->pluck('id')
            );
        });

        $book->load([
            'user',
            'genres',
        ]);

        return (new BookResource($book))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * 指定した書籍を削除し、204 No Content を返す。
     *
     * @param  Book  $book  削除対象の書籍
     */
    public function destroy(Book $book): JsonResponse
    {
        $this->authorize('delete', $book);

        $book->delete();

        return response()->json(null, 204);
    }
}
