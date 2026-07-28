<?php

namespace App\Http\Controllers;

use App\Http\Requests\Review\StoreReviewRequest;
use App\Http\Requests\Review\UpdateReviewRequest;
use App\Models\Book;
use App\Models\Review;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ReviewController extends Controller
{
    /**
     * 新しいレビューを投稿する。
     * 投稿成功時は書籍詳細画面へリダイレクトする。
     *
     * @param  StoreReviewRequest  $request  バリデーション済みのリクエスト
     * @param  Book  $book  レビュー投稿対象の書籍
     */
    public function store(StoreReviewRequest $request, Book $book): RedirectResponse
    {
        $book->reviews()->create([
            ...$request->validated(),
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('books.show', $book)
            ->with('success', 'レビューを投稿しました。');
    }

    /**
     * レビュー編集画面を表示する。
     *
     * @param  Review  $review  編集対象のレビュー
     */
    public function edit(Review $review): View
    {
        $this->authorize('update', $review);

        $review->load('book');

        return view('reviews.edit', compact('review'));
    }

    /**
     * レビューを更新する。
     * 更新成功時は書籍詳細画面へリダイレクトする。
     *
     * @param  UpdateReviewRequest  $request  更新内容を含むリクエスト
     * @param  Review  $review  更新対象のレビュー
     */
    public function update(UpdateReviewRequest $request, Review $review): RedirectResponse
    {
        $this->authorize('update', $review);

        $review->update($request->validated());

        return redirect()->route('books.show', $review->book)
            ->with('success', 'レビューを更新しました。');
    }

    /**
     * レビューを削除する。
     * 削除成功時は書籍詳細画面へリダイレクトする。
     *
     * @param  Review  $review  削除対象のレビュー
     */
    public function destroy(Review $review): RedirectResponse
    {
        $this->authorize('delete', $review);

        $book = $review->book;

        $review->delete();

        return redirect()->route('books.show', $book)
            ->with('success', 'レビューを削除しました。');
    }
}
