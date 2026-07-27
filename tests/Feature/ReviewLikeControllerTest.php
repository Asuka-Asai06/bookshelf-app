<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewLikeControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Book $book;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->book = Book::factory()->create();
    }

    public function test_レビューに対するいいねを登録_解除_再登録できる(): void
    {
        $owner = User::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $owner->id,
            'book_id' => $this->book->id,
        ]);
        // 初期状態ではお気に入りではない
        $this->assertDatabaseMissing('review_likes', [
            'user_id' => $this->user->id,
            'review_id' => $review->id,
        ]);

        // 1回目：登録
        $this->actingAs($this->user)
            ->post(route('reviews.like', $review));

        $this->assertDatabaseHas('review_likes', [
            'user_id' => $this->user->id,
            'review_id' => $review->id,
        ]);

        // 2回目：解除
        $this->actingAs($this->user)
            ->post(route('reviews.like', $review));

        $this->assertDatabaseMissing('review_likes', [
            'user_id' => $this->user->id,
            'review_id' => $review->id,
        ]);

        // 3回目：再登録
        $this->actingAs($this->user)
            ->post(route('reviews.like', $review));

        $this->assertDatabaseHas('review_likes', [
            'user_id' => $this->user->id,
            'review_id' => $review->id,
        ]);
    }

    public function test_自分のレビューにはいいねできない(): void
    {
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('reviews.like', $review));

        $response
            ->assertRedirect(route('books.show', $this->book))
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('review_likes', [
            'user_id' => $this->user->id,
            'review_id' => $review->id,
        ]);
        $response->assertSessionHas(
            'error',
            '自分のレビューにいいねはできません。'
        );
    }

    public function test_未ログインではいいねできない(): void
    {
        $review = Review::factory()->create([
            'book_id' => $this->book->id,
        ]);

        $response = $this->post(route('reviews.like', $review));

        $response->assertRedirect(route('login'));

        $this->assertDatabaseMissing('review_likes', [
            'review_id' => $review->id,
        ]);
    }
}
