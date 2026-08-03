<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use App\Notifications\ReadingPlanReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_認証ユーザーは通知一覧を表示できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $user->notify(
            new ReadingPlanReminderNotification(
                $readingPlan,
                'three_days_before'
            )
        );

        $response = $this
            ->actingAs($user)
            ->get(route('notifications.index'));

        $response->assertOk();

        $response->assertSee($book->title);
    }

    public function test_未認証ユーザーは通知一覧を表示できない(): void
    {
        $response = $this->get(
            route('notifications.index')
        );

        $response->assertRedirect(
            route('login')
        );
    }

    public function test_自分の通知を既読にできる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $user->notify(
            new ReadingPlanReminderNotification(
                $readingPlan,
                'three_days_before'
            )
        );

        $notification = $user
            ->notifications()
            ->first();

        $response = $this
            ->actingAs($user)
            ->post(
                route('notifications.read', $notification->id)
            );

        $response->assertRedirect(
            route('notifications.index')
        );

        $this->assertNotNull(
            $notification->fresh()->read_at
        );
    }

    public function test_他人の通知は既読にできない(): void
    {
        $user = User::factory()->create();

        $otherUser = User::factory()->create();

        $book = Book::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $otherUser->id,
            'book_id' => $book->id,
        ]);

        $otherUser->notify(
            new ReadingPlanReminderNotification(
                $readingPlan,
                'three_days_before'
            )
        );

        $notification = $otherUser
            ->notifications()
            ->first();

        $response = $this
            ->actingAs($user)
            ->post(
                route('notifications.read', $notification->id)
            );

        $response->assertNotFound();
    }
}
