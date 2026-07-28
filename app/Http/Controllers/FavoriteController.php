<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class FavoriteController extends Controller
{
    public function index(): View
    {
        $books = auth()->user()->favoriteBooks()->latest()->paginate(10);

        return view('favorites.index', compact('books'));
    }

    public function toggle(Book $book): RedirectResponse
    {
        auth()->user()->favoriteBooks()->toggle($book);

        return back();
    }
}
