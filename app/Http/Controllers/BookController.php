<?php

namespace App\Http\Controllers;

use App\Http\Requests\Book\StoreBookRequest;
use App\Http\Requests\Book\UpdateBookRequest;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class BookController extends Controller
{
    /**
     * 書籍一覧を表示する。
     * 検索メソッドはscopeに切り分け。
     */
public function index(Request $request): View
{
    $books = Book::query()
        ->with('genres')
        ->keyword($request->keyword)
        ->genre($request->genre)
        ->sort($request->sort)
        ->paginate(10)
        ->withQueryString();

    $genres = Genre::all();

    return view('books.index', compact('books','genres'));
}

    /**
     * 書籍登録画面を表示する。
     */
    public function create(): View
    {
        $genres = Genre::orderBy('name')->get();

        return view('books.create', compact('genres'));
    }

    /**
     * 新しい書籍を登録する。
     * 登録成功時は書籍一覧画面へリダイレクトする。
     *
     * @param  StoreBookRequest  $request  バリデーション済みのリクエスト
     */
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

    /**
     * 書籍の詳細画面を表示する。
     *
     * @param  Book  $book  表示対象の書籍
     */
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

    /**
     * 書籍編集画面を表示する。
     *
     * @param  Book  $book  編集対象の書籍
     */
    public function edit(Book $book): View
    {
        $this->authorize('update', $book);

        $book->load('genres');

        $genres = Genre::orderBy('name')->get();

        return view('books.edit', compact('book', 'genres'));
    }

    /**
     * 書籍情報を更新する。
     * 更新成功時は書籍一覧画面へリダイレクトする。
     *
     * @param  UpdateBookRequest  $request  更新内容を含むリクエスト
     * @param  Book  $book  更新対象の書籍
     */
    public function update(UpdateBookRequest $request, Book $book): RedirectResponse
    {
        $this->authorize('update', $book);

        $book->update($request->validated());

        return redirect()->route('books.show', $book)
            ->with('success', '書籍を更新しました。');
    }

    /**
     * 書籍を削除する。
     * 削除成功時は書籍一覧画面へリダイレクトする。
     *
     * @param  Book  $book  削除対象の書籍
     */
    public function destroy(Book $book): RedirectResponse
    {
        $this->authorize('delete', $book);

        $book->delete();

        return redirect()->route('books.index')
            ->with('success', '書籍を削除しました。');
    }

    /**
     * 平均評価の高い書籍ランキング（上位10件）を表示する。
     */
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
