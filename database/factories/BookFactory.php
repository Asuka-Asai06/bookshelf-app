<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->words(3, true),
            'author' => fake()->name(),
            'isbn' => fake()->numerify('#############'),
            'published_date' => fake()->date(),
            'description' => fake()->paragraph(),
            'image_url' => fake()->imageUrl(),
        ];
    }

    public function withGenres(int $count = 2): static
    {
        return $this->afterCreating(function (Book $book) use ($count) {

            $genreIds = Genre::query()
                ->inRandomOrder()
                ->limit($count)
                ->pluck('id');

            $book->genres()->attach($genreIds);
        });
    }
}
