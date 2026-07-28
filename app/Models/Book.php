<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'author',
        'isbn',
        'published_date',
        'description',
        'image_url',
    ];

    protected $casts = [
        'published_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(
            Genre::class,
            'book_genre'
        );
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function favoritedUsers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'favorites'
        );
    }

    /**
     * タイトルまたは著者名で検索する。
     */
    public function scopeKeyword(Builder $query, ?string $keyword): Builder
    {
        if (empty($keyword)) {
            return $query;
        }

        return $query->where(function ($query) use ($keyword) {
            $query->where('title', 'like', "%{$keyword}%")
                ->orWhere('author', 'like', "%{$keyword}%");
        });
    }

    /**
     * 指定したジャンルで絞り込む。
     */
    public function scopeGenre(Builder $query, ?int $genreId): Builder
    {
        if (empty($genreId)) {
            return $query;
        }

        return $query->whereHas(
            'genres',
            function ($query) use ($genreId) {
                $query->where('genres.id', $genreId);
            }
        );
    }

    /**
     * 指定された条件で並び替える。
     */
    public function scopeSort(Builder $query, ?string $sort): Builder
    {
        return match ($sort) {

            'oldest' => $query->oldest(),

            'title' => $query->orderBy('title'),

            'rating' => $query
                ->withAvg('reviews', 'rating')
                ->orderByDesc('reviews_avg_rating'),

            default => $query->latest(),
        };
    }
}
