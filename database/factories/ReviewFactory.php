<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'book_id' => Book::factory(),
            'user_id' => User::factory(),
            'rating' => fake()->numberBetween(1, 5),
            'comment' => fake()->realText(80),
        ];
    }

    public function forUserAndBook(User $user, Book $book): static
    {
        return $this->state([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }
}
