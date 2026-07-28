<?php

namespace App\Http\Controllers;

use App\Http\Requests\Book\StoreBookRequest;
use App\Http\Requests\Book\UpdateBookRequest;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
    public function index(): View
    {
        $books = Book::all();
        $genres = Genre::all();

        $books = Book::with(['genres'])
            ->paginate(10)
            ->withQueryString();

        return view('books.index', compact('books', 'genres'));
    }

    public function create(): View
    {
        $genres = Genre::orderBy('name')->get();

        return view('books.create', compact('genres'));
    }

    public function store(StoreBookRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {

            $book = Book::create([
                ...$request->safe()->except('genres'),
                'user_id' => auth()->id(),
            ]);

            $book->genres()->sync(
                $request->validated('genres')
            );
        });

        return redirect()->route('books.index')
            ->with('success', '書籍を登録しました。');
    }

    public function show(Book $book): View
    {
        $book->load([
            'user',
            'genres',
            'reviews' => fn ($query) => $query
                ->with('user')
                ->withCount('likedByUsers')
                ->latest(),
        ])->loadCount('reviews');

        return view('books.show', compact('book'));
    }

    public function edit(Book $book): View
    {
        $this->authorize('update', $book);

        $book->load('genres');

        $genres = Genre::orderBy('name')->get();

        return view('books.edit', compact('book', 'genres'));
    }

    public function update(UpdateBookRequest $request, Book $book): RedirectResponse
    {
        $this->authorize('update', $book);

        $book->update($request->validated());

        return redirect()->route('books.show', $book)
            ->with('success', '書籍を更新しました。');
    }

    public function destroy(Book $book): RedirectResponse
    {
        $this->authorize('delete', $book);

        $book->delete();

        return redirect()->route('books.index')
            ->with('success', '書籍を削除しました。');
    }

    public function ranking(): View
    {
        $rankedBooks = Book::query()
            ->with(['user', 'genres'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->has('reviews')
            ->orderByDesc('reviews_avg_rating')
            ->take(10)
            ->get();

        return view('ranking.index', compact('rankedBooks'));
    }
}
