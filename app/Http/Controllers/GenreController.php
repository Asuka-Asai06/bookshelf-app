<?php

namespace App\Http\Controllers;

use App\Http\Requests\Genre\StoreGenreRequest;
use App\Http\Requests\Genre\UpdateGenreRequest;
use App\Models\Genre;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class GenreController extends Controller
{
    public function index(): View
    {
        $genres = Genre::query()
            ->withCount('books')
            ->orderBy('name')
            ->get();

        return view('genres.index', compact('genres'));
    }

    public function create(): View
    {
        return view('genres.create');
    }

    public function store(StoreGenreRequest $request): RedirectResponse
    {
        Genre::create(
            $request->validated()
        );

        return redirect()
            ->route('genres.index')
            ->with('success', 'ジャンルを登録しました。');
    }

    public function show(Genre $genre): View
    {
        $books = $genre->books()
            ->with(['genres'])
            ->latest()
            ->paginate(10);

        return view('genres.show', compact('genre', 'books'));
    }

    public function edit(Genre $genre): View
    {
        return view('genres.edit', compact('genre'));
    }

    public function update(UpdateGenreRequest $request, Genre $genre): RedirectResponse
    {
        $genre->update($request->validated());

        return redirect()->route('genres.index')
            ->with('success', 'ジャンルを更新しました。');
    }

    public function destroy(Genre $genre): RedirectResponse
    {
        if ($genre->books()->exists()) {
            return back()->with(
                'error',
                'このジャンルに紐づく書籍が存在するため削除できません。'
            );
        }

        $genre->delete();

        return redirect()->route('genres.index')
            ->with('success', 'ジャンルを削除しました。');
    }
}
