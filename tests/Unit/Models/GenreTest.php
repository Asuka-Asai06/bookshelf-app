<?php

namespace Tests\Unit\Models;

use App\Models\Book;
use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use RefreshDatabase;

    public function test_ジャンルに紐づく書籍を取得できる(): void
    {
        $genre = Genre::factory()->create();

        $books = Book::factory()->count(3)->create();

        $genre->books()->attach($books->pluck('id'));

        $this->assertCount(3, $genre->books);

        $this->assertEqualsCanonicalizing(
            $books->pluck('id')->toArray(),
            $genre->books->pluck('id')->toArray()
        );
    }
}
