<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\V1\IndexBookRequest;
use App\Http\Requests\API\V1\StoreBookRequest;
use App\Http\Requests\API\V1\UpdateBookRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
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
// WhereInでor検索、ANDならWhereに変更
            ->when(
                $request->filled('genre_ids'),
                function ($query) use ($request) {
                    $query->whereHas(
                        'genres',
                        fn ($query) => $query->whereIn(
                            'genres.id',
                            $request->genre_ids
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

    public function store(StoreBookRequest $request)
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

    public function show(Book $book): BookResource
    {
        $book->load([
            'user',
            'genres',
        ])->loadAvg('reviews', 'rating')
            ->loadCount('reviews');

        return new BookResource($book);
    }

    public function update(UpdateBookRequest $request, Book $book)
    {
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

    public function destroy(Book $book)
    {
        $book->delete();

        return response()->json(null, 204);
    }
}
