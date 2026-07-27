<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Genre $genre;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->genre = Genre::factory()->create();
    }

    public function test_書籍一覧画面を表示できる(): void
    {
        Book::factory()->count(3)->create();

        $response = $this->get(route('books.index'));

        $response->assertOk();
        $response->assertViewIs('books.index');
    }

    public function test_ログイン済みユーザーは書籍登録画面を表示できる(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('books.create'));

        $response->assertOk();
        $response->assertViewIs('books.create');
    }

    public function test_未ログインでは書籍登録画面を表示できない(): void
    {
        $response = $this->get(route('books.create'));

        $response->assertRedirect(route('login'));
    }

    public function test_ログイン済みユーザーは書籍を登録できる(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('books.store'), [
                'title' => 'Laravel',
                'author' => '山田',
                'isbn' => '9781234567890',
                'published_date' => now()->toDateString(),
                'description' => '説明',
                'image_url' => 'https://example.com/book.jpg',
                'genres' => [$this->genre->id],
            ]);

        $response
            ->assertRedirect(route('books.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('books', [
            'title' => 'Laravel',
            'author' => '山田',
            'isbn' => '9781234567890',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_タイトルが未入力なら登録できない(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('books.store'), [
                'title' => '',
                'author' => '山田',
                'isbn' => '9781234567890',
                'published_date' => now()->toDateString(),
                'genres' => [$this->genre->id],
            ]);

        $response
            ->assertSessionHasErrors('title');

        $this->assertDatabaseMissing('books', [
            'isbn' => '9781234567890',
        ]);
    }

    public function test_書籍詳細画面を表示できる(): void
    {
        $book = Book::factory()->create();

        $response = $this->get(route('books.show', $book));

        $response->assertOk();
        $response->assertViewIs('books.show');
    }

    public function test_作成者は編集画面を表示できる(): void
    {
        $book = Book::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('books.edit', $book));

        $response->assertOk();
    }

    public function test_作成者以外は編集画面を表示できない(): void
    {
        $owner = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $owner->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('books.edit', $book));

        $response->assertForbidden();
    }

    public function test_作成者は書籍を更新できる(): void
    {
        $book = Book::factory()->create([
            'user_id' => $this->user->id,
            'isbn' => '9781111111111',
        ]);

        $response = $this->actingAs($this->user)
            ->put(route('books.update', $book), [
                'title' => '更新後タイトル',
                'author' => '更新後著者',
                'isbn' => '9781111111111',
                'published_date' => now()->toDateString(),
                'description' => '更新後説明',
                'image_url' => 'https://example.com/new.jpg',
                'genres' => [$this->genre->id],
            ]);

        $response
            ->assertRedirect(route('books.show', $book))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新後タイトル',
            'author' => '更新後著者',
        ]);
    }

    public function test_自分自身のisbnなら更新できる(): void
    {
        $book = Book::factory()->create([
            'user_id' => $this->user->id,
            'isbn' => '9781234567890',
        ]);

        $response = $this->actingAs($this->user)
            ->put(route('books.update', $book), [
                'title' => $book->title,
                'author' => $book->author,
                'isbn' => '9781234567890',
                'published_date' => $book->published_date,
                'description' => $book->description,
                'image_url' => $book->image_url,
                'genres' => [$this->genre->id],
            ]);

        $response->assertSessionHasNoErrors();
    }

    public function test_他の書籍とisbnが重複すると更新できない(): void
    {
        Book::factory()->create([
            'isbn' => '9789999999999',
        ]);

        $book = Book::factory()->create([
            'user_id' => $this->user->id,
            'isbn' => '9781111111111',
        ]);

        $response = $this->actingAs($this->user)
            ->put(route('books.update', $book), [
                'title' => 'Laravel',
                'author' => '山田',
                'isbn' => '9789999999999',
                'published_date' => now()->toDateString(),
                'genres' => [$this->genre->id],
            ]);

        $response->assertSessionHasErrors('isbn');
    }

    public function test_作成者は書籍を削除できる(): void
    {
        $book = Book::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('books.destroy', $book));

        $response
            ->assertRedirect(route('books.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);
    }

    public function test_作成者以外は書籍を削除できない(): void
    {
        $owner = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $owner->id,
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('books.destroy', $book));

        $response->assertForbidden();

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
        ]);
    }
}
