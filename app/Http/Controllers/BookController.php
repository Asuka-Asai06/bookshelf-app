<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use App\Models\Genre;

class BookController extends Controller
{
    public function index()
    {
        $books = Book::all();
        $genres = Genre::all();

        $books = Book::with(['genres'])
            ->paginate(10)
            ->withQueryString();

        return view('books.index', compact('books', 'genres'));
    }

    public function create()
    {
        $genres = Genre::orderBy('name')->get();

        return view('books.create', compact('genres'));
    }

    public function store(StoreBookRequest $request)
    {
        $book = Book::create([
            ...$request->safe()->except('genres'),
            'user_id' => auth()->id(),
        ]);

        $book->genres()->sync(
            $request->validated('genres')
        );

        return redirect()->route('books.index')
            ->with('success', '書籍を登録しました。');
    }

    public function show(Book $book)
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

    public function edit(Book $book)
    {
        $this->authorize('update', $book);

        $book->load('genres');

        $genres = Genre::orderBy('name')->get();

        return view('books.edit', compact('book', 'genres'));
    }

    public function update(UpdateBookRequest $request, Book $book)
    {
        $this->authorize('update', $book);

        $book->update($request->validated());

        return redirect()->route('books.show', $book)
            ->with('success', '書籍を更新しました。');
    }

    public function destroy(Book $book)
    {
        $this->authorize('delete', $book);

        $book->delete();

        return redirect()->route('books.index')
            ->with('success', '書籍を削除しました。');
    }
}
