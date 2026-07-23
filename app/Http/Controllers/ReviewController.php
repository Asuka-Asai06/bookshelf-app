<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Models\Book;
use App\Models\Review;

class ReviewController extends Controller
{
    public function store(StoreReviewRequest $request, Book $book)
    {
        $book->reviews()->create([
            ...$request->validated(),
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('books.show', $book)
            ->with('success', 'レビューを投稿しました。');
    }

    public function edit(Review $review)
    {
        $this->authorize('update', $review);

        $review->load('book');

        return view('reviews.edit', compact('review'));
    }

    public function update(UpdateReviewRequest $request, Review $review)
    {
        $this->authorize('update', $review);

        $review->update($request->validated());

        return redirect()->route('books.show', $review->book)
            ->with('success', 'レビューを更新しました。');
    }

    public function destroy(Review $review)
    {
        $this->authorize('delete', $review);

        $book = $review->book;

        $review->delete();

        return redirect()->route('books.show', $book)
            ->with('success', 'レビューを削除しました。');
    }
}
