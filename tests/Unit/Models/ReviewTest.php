<?php

namespace Tests\Unit\Models;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_レビューの投稿者を取得できる(): void
    {
        $user = User::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $review->user);
        $this->assertSame($user->id, $review->user->id);
    }

    public function test_レビューが紐づく書籍を取得できる(): void
    {
        $book = Book::factory()->create();

        $review = Review::factory()->create([
            'book_id' => $book->id,
        ]);

        $this->assertInstanceOf(Book::class, $review->book);
        $this->assertSame($book->id, $review->book->id);
    }

    public function test_レビューにいいねしたユーザーを取得できる(): void
    {
        $review = Review::factory()->create();

        $users = User::factory()->count(3)->create();

        $review->likedByUsers()->attach($users->pluck('id'));

        $this->assertCount(3, $review->likedByUsers);

        $this->assertEqualsCanonicalizing(
            $users->pluck('id')->toArray(),
            $review->likedByUsers->pluck('id')->toArray()
        );
    }
}
