<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_ログイン済みユーザーはジャンル一覧画面を表示できる(): void
    {
        Genre::factory()->count(3)->create();

        $response = $this->actingAs($this->user)
            ->get(route('genres.index'));

        $response->assertOk();
        $response->assertViewIs('genres.index');
        $response->assertViewHas('genres');
    }

    public function test_ログイン済みユーザーはジャンル登録画面を表示できる(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('genres.create'));

        $response->assertOk();
        $response->assertViewIs('genres.create');
    }

    public function test_未ログインではジャンル登録画面を表示できない(): void
    {
        $response = $this->get(route('genres.create'));

        $response->assertRedirect(route('login'));
    }

    public function test_ログイン済みユーザーはジャンルを登録できる(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('genres.store'), [
                'name' => 'Laravel',
            ]);

        $response
            ->assertRedirect(route('genres.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('genres', [
            'name' => 'Laravel',
        ]);
    }

    public function test_ジャンル名が未入力なら登録できない(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('genres.store'), [
                'name' => '',
            ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_ログイン済みユーザーはジャンル詳細画面を表示できる(): void
    {
        $genre = Genre::factory()->create();

        $response = $this->actingAs($this->user)
            ->get(route('genres.show', $genre));

        $response->assertOk();
        $response->assertViewIs('genres.show');
    }

    public function test_ログイン済みユーザーはジャンルを更新できる(): void
    {
        $genre = Genre::factory()->create([
            'name' => 'PHP',
        ]);

        $response = $this->actingAs($this->user)
            ->put(route('genres.update', $genre), [
                'name' => 'Laravel',
            ]);

        $response
            ->assertRedirect(route('genres.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => 'Laravel',
        ]);
    }

    public function test_重複したジャンル名には更新できない(): void
    {
        Genre::factory()->create([
            'name' => 'PHP',
        ]);

        $genre = Genre::factory()->create([
            'name' => 'Laravel',
        ]);

        $response = $this->actingAs($this->user)
            ->put(route('genres.update', $genre), [
                'name' => 'PHP',
            ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_自分自身のジャンル名なら更新できる(): void
    {
        $genre = Genre::factory()->create([
            'name' => 'PHP',
        ]);

        $response = $this->actingAs($this->user)
            ->put(route('genres.update', $genre), [
                'name' => 'PHP',
            ]);

        $response
            ->assertRedirect(route('genres.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => 'PHP',
        ]);
    }

    public function test_書籍が紐付いていないジャンルは削除できる(): void
    {
        $genre = Genre::factory()->create();

        $response = $this->actingAs($this->user)
            ->delete(route('genres.destroy', $genre));

        $response
            ->assertRedirect(route('genres.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('genres', [
            'id' => $genre->id,
        ]);
    }

    public function test_書籍が紐付いているジャンルは削除できない(): void
    {
        $genre = Genre::factory()->create();

        $book = Book::factory()->create();

        $book->genres()->attach($genre);

        $response = $this->actingAs($this->user)
            ->delete(route('genres.destroy', $genre));

        $response
            ->assertSessionHas('error');

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
        ]);
    }
}
