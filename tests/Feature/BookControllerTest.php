<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
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

    public function test_ランキングは上位10件のみ表示される(): void
    {
        $user = User::factory()->create();

        for ($i = 1; $i <= 11; $i++) {

            $book = Book::factory()->create([
                'user_id' => $user->id,
                'title' => "Book {$i}",
            ]);

            Review::factory()->create([
                'user_id' => $user->id,
                'book_id' => $book->id,
                'rating' => 5,
            ]);
        }

        $response = $this->get(route('ranking.index'));

        $response->assertStatus(200);

        $response->assertViewHas('rankedBooks', function ($books) {
            return $books->count() === 10;
        });
    }

    public function test_書籍の平均評価が高い順にランキング表示される(): void
    {
        $user = User::factory()->create();

        // BookA（平均4.67）
        $bookA = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'BookA',
        ]);

        Review::factory()->count(2)->create([
            'book_id' => $bookA->id,
            'user_id' => $user->id,
            'rating' => 5,
        ]);

        Review::factory()->create([
            'book_id' => $bookA->id,
            'user_id' => $user->id,
            'rating' => 4,
        ]);

        // BookB（平均4.50）
        $bookB = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'BookB',
        ]);

        Review::factory()->create([
            'book_id' => $bookB->id,
            'user_id' => $user->id,
            'rating' => 5,
        ]);

        Review::factory()->create([
            'book_id' => $bookB->id,
            'user_id' => $user->id,
            'rating' => 4,
        ]);

        // BookC（平均4.00）
        $bookC = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'BookC',
        ]);

        Review::factory()->count(2)->create([
            'book_id' => $bookC->id,
            'user_id' => $user->id,
            'rating' => 4,
        ]);

        $response = $this->get(route('ranking.index'));

        $response->assertStatus(200);

        $response->assertSeeInOrder([
            'BookA',
            'BookB',
            'BookC',
        ]);
    }

    public function test_レビューがある書籍だけランキングに表示される(): void
    {
        $user = User::factory()->create();

        $reviewedBook = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'Reviewed Book',
        ]);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $reviewedBook->id,
            'rating' => 5,
        ]);

        $noReviewBook = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'No Review Book',
        ]);

        $response = $this->get(route('ranking.index'));

        $response->assertStatus(200);

        $response->assertViewHas('rankedBooks', function ($books) use ($reviewedBook) {
            return $books->count() === 1
                && $books->first()->is($reviewedBook);
        });

        $response->assertSee('Reviewed Book');

        $response->assertDontSee('No Review Book');
    }

    public function test_タイトルで書籍を検索できる(): void
    {
        Book::factory()->create([
            'title' => 'Laravel入門',
            'author' => '田中',
        ]);

        Book::factory()->create([
            'title' => 'PHP入門',
            'author' => '山田',
        ]);

        $response = $this->get(route('books.index', [
            'keyword' => 'Laravel',
        ]));

        $response->assertOk();

        $response->assertSee('Laravel入門');

        $response->assertDontSee('PHP入門');
    }

    public function test_著者名で書籍を検索できる(): void
    {
        Book::factory()->create([
            'title' => '本A',
            'author' => '夏目漱石',
        ]);

        Book::factory()->create([
            'title' => '本B',
            'author' => '太宰治',
        ]);

        $response = $this->get(route('books.index', [
            'keyword' => '夏目',
        ]));

        $response->assertOk();

        $response->assertSee('本A');

        $response->assertDontSee('本B');
    }

    public function test_ジャンルで書籍を検索できる(): void
    {
        $novel = Genre::factory()->create([
            'name' => '小説',
        ]);

        $business = Genre::factory()->create([
            'name' => 'ビジネス',
        ]);

        $book1 = Book::factory()->create([
            'title' => '小説A',
        ]);

        $book1->genres()->attach($novel);

        $book2 = Book::factory()->create([
            'title' => 'ビジネスA',
        ]);

        $book2->genres()->attach($business);

        $response = $this->get(route('books.index', [
            'genre' => $novel->id,
        ]));

        $response->assertOk();

        $response->assertSee('小説A');

        $response->assertDontSee('ビジネスA');
    }

    public function test_評価順で並び替えできる(): void
    {
        $book1 = Book::factory()->create([
            'title' => '低評価',
        ]);

        Review::factory()->create([
            'book_id' => $book1->id,
            'rating' => 2,
        ]);

        $book2 = Book::factory()->create([
            'title' => '高評価',
        ]);

        Review::factory()->create([
            'book_id' => $book2->id,
            'rating' => 5,
        ]);

        $response = $this->get(route('books.index', [
            'sort' => 'rating',
        ]));

        $response->assertOk();

        $response->assertSeeInOrder([
            '高評価',
            '低評価',
        ]);
    }

    public function test_キーワードが255文字を超える場合エラーになる(): void
    {
        $response = $this->get(route('books.index', [
            'keyword' => str_repeat('a', 256),
        ]));

        $response->assertSessionHasErrors([
            'keyword',
        ]);
    }

    public function test_存在しないジャンルでは検索できない(): void
    {
        $response = $this->get(route('books.index', [
            'genre' => 9999,
        ]));

        $response->assertSessionHasErrors([
            'genre',
        ]);
    }

    public function test_ジャンル_i_dは数字である必要がある(): void
    {
        $response = $this->get(route('books.index', [
            'genre' => 'abc',
        ]));

        $response->assertSessionHasErrors([
            'genre',
        ]);
    }

    public function test_存在しない並び順では検索できない(): void
    {
        $response = $this->get(route('books.index', [
            'sort' => 'popular',
        ]));

        $response->assertSessionHasErrors([
            'sort',
        ]);
    }
}
