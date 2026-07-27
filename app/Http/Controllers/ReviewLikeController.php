<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ReviewLikeController extends Controller
{
    public function toggle(Review $review): RedirectResponse
    {
        if (Gate::denies('like', $review)) {
            return redirect()
                ->route('books.show', $review->book)
                ->with('error', '自分のレビューにいいねはできません。');
        }

        auth()->user()->likedReviews()->toggle($review);

        return back();
    }
}
