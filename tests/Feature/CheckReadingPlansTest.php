<?php

namespace Tests\Feature;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use App\Notifications\ReadingPlanReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CheckReadingPlansTest extends TestCase
{
    use RefreshDatabase;

    public function test_期限を過ぎた読書計画は期限超過になる(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create();

        $plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => ReadingPlanStatus::In_Progress,
            'target_date' => today()->subDay(),
        ]);

        $this->artisan('reading-plans:check')
            ->assertSuccessful();

        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan->id,
            'status' => ReadingPlanStatus::Overdue->value,
        ]);
    }

    public function test_期限3日前に通知される(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $book = Book::factory()->create();

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => ReadingPlanStatus::In_Progress,
            'target_date' => today()->addDays(3),
        ]);

        $this->artisan('reading-plans:check')
            ->assertSuccessful();

        Notification::assertSentTo(
            $user,
            ReadingPlanReminderNotification::class
        );
    }

    public function test_期限当日に通知される(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $book = Book::factory()->create();

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => ReadingPlanStatus::In_Progress,
            'target_date' => today(),
        ]);

        $this->artisan('reading-plans:check')
            ->assertSuccessful();

        Notification::assertSentTo(
            $user,
            ReadingPlanReminderNotification::class
        );
    }

    public function test_期限3日超過で通知される(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $book = Book::factory()->create();

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => ReadingPlanStatus::In_Progress,
            'target_date' => today()->subDays(3),
        ]);

        $this->artisan('reading-plans:check')
            ->assertSuccessful();

        Notification::assertSentTo(
            $user,
            ReadingPlanReminderNotification::class
        );
    }

    public function test_同じ通知は重複送信されない(): void
{
    $user = User::factory()->create();

    $book = Book::factory()->create();

    ReadingPlan::factory()->create([
        'user_id' => $user->id,
        'book_id' => $book->id,
        'status' => ReadingPlanStatus::In_Progress,
        'target_date' => today()->addDays(3),
    ]);

    $this->artisan('reading-plans:check')
        ->assertSuccessful();

    $this->artisan('reading-plans:check')
        ->assertSuccessful();

    $this->assertDatabaseCount(
        'notifications',
        1
    );
}
}
