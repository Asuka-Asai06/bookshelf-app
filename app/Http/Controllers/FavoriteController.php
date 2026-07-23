<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\RedirectResponse;

class FavoriteController extends Controller
{
    public function index()
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
