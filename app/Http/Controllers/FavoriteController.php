<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class FavoriteController extends Controller
{
    /**
     * お気に入り登録した書籍一覧を表示する。
     */
    public function index(): View
    {
        $books = auth()->user()->favoriteBooks()->latest()->paginate(10);

        return view('favorites.index', compact('books'));
    }

    /**
     * 書籍のお気に入り登録状態を切り替える。
     * 登録済みの場合は解除し、未登録の場合は登録する。
     */
    public function toggle(Book $book): RedirectResponse
    {
        auth()->user()->favoriteBooks()->toggle($book);

        return back();
    }
}
