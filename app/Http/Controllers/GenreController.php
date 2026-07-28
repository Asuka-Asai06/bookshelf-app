<?php

namespace App\Http\Controllers;

use App\Http\Requests\Genre\StoreGenreRequest;
use App\Http\Requests\Genre\UpdateGenreRequest;
use App\Models\Genre;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class GenreController extends Controller
{
    /**
     * ジャンル一覧を表示する。
     */
    public function index(): View
    {
        $genres = Genre::query()
            ->withCount('books')
            ->orderBy('name')
            ->get();

        return view('genres.index', compact('genres'));
    }

    /**
     * ジャンル登録画面を表示する。
     */
    public function create(): View
    {
        return view('genres.create');
    }

    /**
     * 新しいジャンルを登録する。
     * 登録成功時はジャンル一覧画面へリダイレクトする。
     *
     * @param  StoreGenreRequest  $request  バリデーション済みのリクエスト
     */
    public function store(StoreGenreRequest $request): RedirectResponse
    {
        Genre::create(
            $request->validated()
        );

        return redirect()
            ->route('genres.index')
            ->with('success', 'ジャンルを登録しました。');
    }

    /**
     * ジャンルの詳細画面を表示する。
     *
     * @param  Genre  $genre  表示対象のジャンル
     */
    public function show(Genre $genre): View
    {
        $books = $genre->books()
            ->with(['genres'])
            ->latest()
            ->paginate(10);

        return view('genres.show', compact('genre', 'books'));
    }

    /**
     * ジャンル編集画面を表示する。
     *
     * @param  Genre  $genre  編集対象のジャンル
     */
    public function edit(Genre $genre): View
    {
        return view('genres.edit', compact('genre'));
    }

    /**
     * ジャンルを更新する。
     *更新成功時はジャンル一覧画面へリダイレクトする。
     *
     * @param  UpdateGenreRequest  $request  更新内容を含むリクエスト
     * @param  Genre  $genre  更新対象のジャンル
     */
    public function update(UpdateGenreRequest $request, Genre $genre): RedirectResponse
    {
        $genre->update($request->validated());

        return redirect()->route('genres.index')
            ->with('success', 'ジャンルを更新しました。');
    }

    /**
     * ジャンルを削除する。
     * 書籍が紐づいている場合は削除せず、前の画面へ戻る。
     * 削除成功時はジャンル一覧画面へリダイレクトする。
     *
     * @param  Genre  $genre  削除対象のジャンル
     */
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
