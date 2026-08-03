<?php

namespace Tests\Unit\Services;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\Genre;
use App\Models\ReadingPlan;
use App\Models\Review;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_基本統計を取得できる(): void
    {
        $user = User::factory()->create();

        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();
        $book3 = Book::factory()->create();

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book1->id,
            'rating' => 5,
        ]);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book2->id,
            'rating' => 3,
        ]);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book3->id,
            'rating' => 4,
        ]);

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book1->id,
            'status' => ReadingPlanStatus::Completed,
        ]);

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book2->id,
            'status' => ReadingPlanStatus::Completed,
        ]);

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => Book::factory()->create()->id,
            'status' => ReadingPlanStatus::In_Progress,
        ]);

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => Book::factory()->create()->id,
            'status' => ReadingPlanStatus::Overdue,
        ]);

        $service = app(ReportService::class);
        $stats = $service->getStats($user);

        $this->assertSame(3, $stats['summary']['total_reviews']);

        $this->assertSame(2, $stats['summary']['books_read']);

        $this->assertEquals(4, $stats['summary']['average_rating']);
    }

    public function test_評価分布を取得できる(): void
    {
        $user = User::factory()->create();

        $ratings = [1, 2, 2, 4, 5];

        foreach ($ratings as $rating) {
            $book = Book::factory()->create();

            Review::factory()->create([
                'user_id' => $user->id,
                'book_id' => $book->id,
                'rating' => $rating,
            ]);
        }

        $service = app(ReportService::class);
        $stats = $service->getStats($user);

        $this->assertEquals(
            [
                1,
                2,
                0,
                1,
                1,
            ],
            $stats['rating_distribution']->toArray()
        );
    }

    public function test_高評価書籍top5を取得できる(): void
    {
        $user = User::factory()->create();

        $book1 = Book::factory()->create([
            'title' => '最高評価の本',
        ]);

        $book2 = Book::factory()->create([
            'title' => '高評価の本',
        ]);

        $book3 = Book::factory()->create([
            'title' => '低評価の本',
        ]);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book1->id,
            'rating' => 5,
        ]);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book2->id,
            'rating' => 4,
        ]);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book3->id,
            'rating' => 3,
        ]);

        $service = app(ReportService::class);
        $stats = $service->getStats($user);

        $this->assertEquals(
            [
                '最高評価の本',
                '高評価の本',
            ],
            $stats['top_rated_books']
                ->pluck('title')
                ->toArray()
        );
    }

    public function test_ジャンル別評価傾向を取得できる(): void
    {
        $user = User::factory()->create();

        $highGenre = Genre::factory()->create([
            'name' => 'SF',
        ]);

        $lowGenre = Genre::factory()->create([
            'name' => '恋愛',
        ]);

        $highBook1 = Book::factory()->create();
        $highBook1->genres()->attach($highGenre);

        $highBook2 = Book::factory()->create();
        $highBook2->genres()->attach($highGenre);

        $lowBook = Book::factory()->create();
        $lowBook->genres()->attach($lowGenre);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $highBook1->id,
            'rating' => 5,
        ]);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $highBook2->id,
            'rating' => 4,
        ]);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $lowBook->id,
            'rating' => 3,
        ]);

        $service = app(ReportService::class);
        $stats = $service->getStats($user);

        $this->assertEquals(
            [
                'SF',
                '恋愛',
            ],
            $stats['genre_ratings']
                ->pluck('name')
                ->toArray()
        );

        $this->assertEquals(
            4.5,
            $stats['genre_ratings']
                ->first()['average_rating']
        );
    }
}
