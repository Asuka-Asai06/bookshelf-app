<?php

namespace Tests\Unit\Models;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    public function test_書籍の投稿者を取得できる(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $book->user);
        $this->assertEquals($user->id, $book->user->id);
    }

    public function test_書籍に紐づくジャンルを取得できる(): void
    {
        $book = Book::factory()->create();

        $genres = Genre::factory()->count(3)->create();

        $book->genres()->attach($genres->pluck('id'));

        $this->assertCount(3, $book->genres);

        $this->assertEqualsCanonicalizing(
            $genres->pluck('id')->toArray(),
            $book->genres->pluck('id')->toArray()
        );
    }

    public function test_書籍に紐づくレビューを取得できる(): void
    {
        $book = Book::factory()->create();

        $reviews = Review::factory()
            ->count(3)
            ->create([
                'book_id' => $book->id,
            ]);

        $this->assertCount(3, $book->reviews);

        $this->assertEqualsCanonicalizing(
            $reviews->pluck('id')->toArray(),
            $book->reviews->pluck('id')->toArray()
        );
    }

    public function test_書籍をお気に入りしたユーザーを取得できる(): void
    {
        $book = Book::factory()->create();

        $users = User::factory()->count(3)->create();

        $book->favoritedUsers()->attach($users->pluck('id'));

        $this->assertCount(3, $book->favoritedUsers);

        $this->assertEqualsCanonicalizing(
            $users->pluck('id')->toArray(),
            $book->favoritedUsers->pluck('id')->toArray()
        );
    }
}
