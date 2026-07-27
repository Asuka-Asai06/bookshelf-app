<?php

namespace Tests\Unit\Models;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_ユーザーが投稿した書籍を取得できる(): void
    {
        $user = User::factory()->create();

        $books = Book::factory()
            ->count(3)
            ->create([
                'user_id' => $user->id,
            ]);

        $this->assertCount(3, $user->books);

        $this->assertEqualsCanonicalizing(
            $books->pluck('id')->toArray(),
            $user->books->pluck('id')->toArray()
        );
    }

    public function test_ユーザーが投稿したレビューを取得できる(): void
    {
        $user = User::factory()->create();

        $reviews = Review::factory()
            ->count(3)
            ->create([
                'user_id' => $user->id,
            ]);

        $this->assertCount(3, $user->reviews);

        $this->assertEqualsCanonicalizing(
            $reviews->pluck('id')->toArray(),
            $user->reviews->pluck('id')->toArray()
        );
    }

    public function test_ユーザーがお気に入りした書籍を取得できる(): void
    {
        $user = User::factory()->create();

        $books = Book::factory()->count(3)->create();

        $user->favoriteBooks()->attach($books->pluck('id'));

        $this->assertCount(3, $user->favoriteBooks);

        $this->assertEqualsCanonicalizing(
            $books->pluck('id')->toArray(),
            $user->favoriteBooks->pluck('id')->toArray()
        );
    }

    public function test_ユーザーがいいねしたレビューを取得できる(): void
    {
        $user = User::factory()->create();

        $reviews = Review::factory()->count(3)->create();

        $user->likedReviews()->attach($reviews->pluck('id'));

        $this->assertCount(3, $user->likedReviews);

        $this->assertEqualsCanonicalizing(
            $reviews->pluck('id')->toArray(),
            $user->likedReviews->pluck('id')->toArray()
        );
    }
}
