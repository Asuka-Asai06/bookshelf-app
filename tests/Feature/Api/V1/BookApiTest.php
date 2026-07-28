<?php

namespace Tests\Feature\Api\V1;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookApiTest extends TestCase
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

    public function test_書籍一覧を取得できる(): void
    {
        Book::factory()->count(3)->create();

        $response = $this->getJson(route('api.v1.books.index'));

        $response
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'user' => [
                            'id',
                            'name',
                        ],
                        'title',
                        'author',
                        'genres',
                        'isbn',
                        'published_date',
                        'description',
                        'image_url',
                        'average_rating',
                        'reviews_count',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'links',
                'meta',
            ]);
    }

    public function test_タイトルで検索できる(): void
    {
        Book::factory()->create([
            'title' => 'Laravel入門',
        ]);

        Book::factory()->create([
            'title' => 'PHP入門',
        ]);

        $response = $this->getJson(
            route('api.v1.books.index', [
                'keyword' => 'Laravel',
            ])
        );

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_書籍詳細を取得できる(): void
    {
        $book = Book::factory()->create();

        $response = $this->getJson(
            route('api.v1.books.show', $book)
        );

        $response
            ->assertOk()
            ->assertJsonPath('data.id', $book->id)
            ->assertJsonPath('data.title', $book->title);
    }

    public function test_書籍を登録できる(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson(route('api.v1.books.store'),
            [
                'title' => 'Laravel',
                'author' => '山田',
                'isbn' => '9781234567890',
                'published_date' => now()->toDateString(),
                'description' => '説明',
                'image_url' => 'https://example.com/book.jpg',
                'genres' => [$this->genre->id],
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.title', 'Laravel');

        $this->assertDatabaseHas('books', [
            'title' => 'Laravel',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_未認証では書籍を登録できない(): void
    {
        $response = $this->postJson(route('api.v1.books.store'), [
            'title' => 'Laravel',
            'author' => '山田',
            'isbn' => '9781234567890',
            'published_date' => now()->toDateString(),
            'genres' => [$this->genre->id],
        ]);

        $response
            ->assertUnauthorized()
            ->assertJson([
                'message' => 'APIトークンが無効か、認証情報が設定されていません。',
            ]);
    }

    public function test_タイトル未入力では登録できない(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson(route('api.v1.books.store'),
            [
                'title' => '',
                'author' => '山田',
                'isbn' => '9781234567890',
                'published_date' => now()->toDateString(),
                'genres' => [$this->genre->id],
            ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'title',
            ]);
    }

    public function test_書籍を更新できる(): void
    {
        $book = Book::factory()->create([
            'user_id' => $this->user->id,
            'isbn' => '9781111111111',
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->putJson(route('api.v1.books.update', $book),
            [
                'title' => '更新後',
                'author' => '更新後著者',
                'isbn' => '9781111111111',
                'published_date' => now()->toDateString(),
                'description' => '更新後説明',
                'image_url' => 'https://example.com/new.jpg',
                'genres' => [$this->genre->id],
            ]);

        $response
            ->assertOk()
            ->assertJsonPath(
                'data.title',
                '更新後'
            );

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新後',
        ]);
    }

    public function test_自分自身のisbnなら更新できる(): void
    {
        $book = Book::factory()->create([
            'user_id' => $this->user->id,
            'isbn' => '9781234567890',
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->putJson(route('api.v1.books.update', $book),
            [
                'title' => $book->title,
                'author' => $book->author,
                'isbn' => '9781234567890',
                'published_date' => $book->published_date,
                'genres' => [$this->genre->id],
            ]);

        $response->assertOk();
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

        Sanctum::actingAs($this->user);

        $response = $this->putJson(route('api.v1.books.update', $book),
            [
                'title' => 'Laravel',
                'author' => '山田',
                'isbn' => '9789999999999',
                'published_date' => now()->toDateString(),
                'genres' => [$this->genre->id],
            ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'isbn',
            ]);
    }

    public function test_他人の書籍は更新できない(): void
    {
        $owner = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $owner->id,
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->putJson(route('api.v1.books.update', $book),
            [
                'title' => '更新後',
                'author' => '著者',
                'isbn' => $book->isbn,
                'published_date' => now()->toDateString(),
                'genres' => [$this->genre->id],
            ]
        );

        $response->assertForbidden();
    }

    public function test_未認証では書籍を更新できない(): void
    {
        $book = Book::factory()->create([
            'user_id' => $this->user->id,
            'isbn' => '9781111111111',
        ]);
        $response = $this->putJson(route('api.v1.books.update', $book),
            [
                'title' => '更新後',
                'author' => '著者',
                'isbn' => $book->isbn,
                'published_date' => now()->toDateString(),
                'genres' => [$this->genre->id],
            ]);

        $response->assertUnauthorized();
    }

    public function test_書籍を削除できる(): void
    {
        $book = Book::factory()->create([
            'user_id' => $this->user->id,
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->deleteJson(
            route('api.v1.books.destroy', $book)
        );

        $response->assertNoContent();

        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);
    }

    public function test_他人の書籍は削除できない(): void
    {
        $owner = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $owner->id,
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->deleteJson(
            route('api.v1.books.destroy', $book)
        );

        $response->assertForbidden();

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
        ]);
    }

    public function test_未認証では書籍を削除できない(): void
    {
        $book = Book::factory()->create();

        $response = $this->deleteJson(
            route('api.v1.books.destroy', $book)
        );

        $response->assertUnauthorized();

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
        ]);
    }
}
