<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewControllerTest extends TestCase
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

    public function test_レビューを投稿できる(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('reviews.store', $this->book), [
                'rating' => 5,
                'comment' => 'とても面白かったです。',
            ]);

        $response
            ->assertRedirect(route('books.show', $this->book))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('reviews', [
            'book_id' => $this->book->id,
            'user_id' => $this->user->id,
            'rating' => 5,
            'comment' => 'とても面白かったです。',
        ]);
    }

    public function test_同じユーザーは同じ本に複数レビューできない(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('reviews.store', $book), [
                'rating' => 5,
                'comment' => '2回目のレビュー',
            ]);

        $response->assertSessionHasErrors();
    }

    public function test_評価が未入力では投稿できない(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('reviews.store', $this->book), [
                'rating' => '',
                'comment' => 'コメント',
            ]);

        $response->assertSessionHasErrors('rating');
    }

    public function test_投稿者は編集画面を表示できる(): void
    {
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('reviews.edit', $review));

        $response->assertOk();
        $response->assertViewIs('reviews.edit');
    }

    public function test_投稿者以外は編集画面を表示できない(): void
    {
        $owner = User::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $owner->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('reviews.edit', $review));

        $response->assertForbidden();
    }

    public function test_投稿者はレビューを更新できる(): void
    {
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->actingAs($this->user)
            ->put(route('reviews.update', $review), [
                'rating' => 3,
                'comment' => '更新後コメント',
            ]);

        $response
            ->assertRedirect(route('books.show', $this->book))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 3,
            'comment' => '更新後コメント',
        ]);
    }

    public function test_投稿者以外はレビューを更新できない(): void
    {
        $owner = User::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $owner->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->actingAs($this->user)
            ->put(route('reviews.update', $review), [
                'rating' => 5,
                'comment' => '変更',
            ]);

        $response->assertForbidden();

        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
            'rating' => 5,
            'comment' => '変更',
        ]);
    }

    public function test_レビューを削除できる(): void
    {
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('reviews.destroy', $review));

        $response
            ->assertRedirect(route('books.show', $this->book))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
        ]);
    }

    public function test_投稿者以外はレビューを削除できない(): void
    {
        $owner = User::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $owner->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('reviews.destroy', $review));

        $response->assertForbidden();

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
        ]);
    }
}
