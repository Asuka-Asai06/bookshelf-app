<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_お気に入り一覧を表示できる(): void
    {
        $book = Book::factory()->create();

        $this->user->favoriteBooks()->attach($book);

        $response = $this->actingAs($this->user)
            ->get(route('favorites.index'));

        $response->assertOk();
        $response->assertViewIs('favorites.index');
        $response->assertViewHas('books');
    }

    public function test_お気に入りを登録_解除_再登録できる(): void
    {
        $book = Book::factory()->create();

        // 初期状態ではお気に入りではない
        $this->assertDatabaseMissing('favorites', [
            'user_id' => $this->user->id,
            'book_id' => $book->id,
        ]);

        // 1回目：登録
        $this->actingAs($this->user)
            ->post(route('favorites.toggle', $book))
            ->assertRedirect();

        $this->assertDatabaseHas('favorites', [
            'user_id' => $this->user->id,
            'book_id' => $book->id,
        ]);

        // 2回目：解除
        $this->actingAs($this->user)
            ->post(route('favorites.toggle', $book))
            ->assertRedirect();

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $this->user->id,
            'book_id' => $book->id,
        ]);

        // 3回目：再登録
        $this->actingAs($this->user)
            ->post(route('favorites.toggle', $book))
            ->assertRedirect();

        $this->assertDatabaseHas('favorites', [
            'user_id' => $this->user->id,
            'book_id' => $book->id,
        ]);
    }

    public function test_未ログインではお気に入り登録できない(): void
    {
        $book = Book::factory()->create();

        $response = $this->post(
            route('favorites.toggle', $book)
        );

        $response->assertRedirect(route('login'));

        $this->assertDatabaseMissing('favorites', [
            'book_id' => $book->id,
        ]);
    }
}
