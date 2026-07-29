<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ReportService
{
    /**
     * レポート情報を取得する。
     */
    public function getStats(User $user): array
    {
        $reviews = $user->reviews()->getQuery();

        return [
            'summary' => $this->getSummary(clone $reviews),
            'rating_distribution' => $this->getRatingDistribution(clone $reviews),
            'top_rated_books' => $this->getTopRatedBooks(clone $reviews),
            'genre_ratings' => $this->getGenreRatings(clone $reviews),
        ];
    }

    /**
     * 基本サマリー（総レビュー数、読了冊数、平均評価）を取得する。
     */
    private function getSummary(Builder $reviews): array
    {
        return [
            'total_reviews' => (clone $reviews)->count(),
            'books_read' => (clone $reviews)
                ->distinct('book_id')
                ->count('book_id'),
            'average_rating' => (clone $reviews)
                ->avg('rating') ?? 0,
        ];
    }

    /**
     * 評価分布を取得する。
     */
    private function getRatingDistribution(Builder $reviews): Collection
    {
        return collect(range(1, 5))
            ->map(fn ($rating) => (clone $reviews)
                ->where('rating', $rating)
                ->count());
    }

    /**
     * 高評価書籍TOP5を取得する。
     */
    private function getTopRatedBooks(Builder $reviews): Collection
    {
        return $reviews
            ->with('book')
            ->where('rating', '>=', 4)
            ->orderByDesc('rating')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($review) => [
                'id' => $review->book->id,
                'title' => $review->book->title,
                'author' => $review->book->author,
                'rating' => $review->rating,
            ]);
    }

    /**
     * ジャンル別評価傾向TOP5を取得する。
     */
    private function getGenreRatings(Builder $reviews): Collection
    {
        return $reviews
            ->with('book.genres')
            ->get()
            ->flatMap(function ($review) {
                return $review->book->genres
                    ->map(function ($genre) use ($review) {
                        return [
                            'genre' => $genre,
                            'rating' => $review->rating,
                        ];
                    });
            })
            ->groupBy(function ($item) {
                return $item['genre']->id;
            })
            ->map(function ($items) {
                $genre = $items->first()['genre'];

                return [
                    'id' => $genre->id,
                    'name' => $genre->name,
                    'count' => $items->count(),
                    'average_rating' => $items->avg('rating'),
                ];
            })
            ->sortByDesc('average_rating')
            ->take(5)
            ->values();
    }
}
