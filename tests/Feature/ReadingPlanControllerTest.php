<?php

namespace Tests\Feature;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingPlanControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_認証ユーザーは読書計画一覧を表示できる(): void
    {
        $user = User::factory()->create();

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('reading-plans.index'));

        $response->assertStatus(200);

        $response->assertViewIs('reading-plans.index');
    }

    public function test_未認証ユーザーは読書計画一覧を表示できない(): void
    {
        $response = $this->get(
            route('reading-plans.index')
        );

        $response->assertRedirect(
            route('login')
        );
    }

    public function test_自分の読書計画のみ表示される(): void
    {
        $user = User::factory()->create();

        $otherUser = User::factory()->create();

        $myPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
        ]);

        $otherPlan = ReadingPlan::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('reading-plans.index'));

        $response->assertStatus(200);

        $response->assertSee(
            $myPlan->book->title
        );

        $response->assertDontSee(
            $otherPlan->book->title
        );
    }

    public function test_ステータスで絞り込みできる(): void
    {
        $user = User::factory()->create();

        $completedPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::Completed,
        ]);

        $progressPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::In_Progress,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('reading-plans.index', [
                'status' => ReadingPlanStatus::Completed->value,
            ]));

        $response->assertStatus(200);

        $response->assertSee(
            $completedPlan->book->title
        );

        $response->assertDontSee(
            $progressPlan->book->title
        );
    }

    public function test_存在しないステータスならエラーになる(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(route('reading-plans.index', [
                'status' => 'invalid',
            ]));

        $response->assertSessionHasErrors('status');
    }

    public function test_認証ユーザーは読書計画を登録できる(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('reading-plans.store'), [
                'book_id' => $book->id,
                'target_date' => now()->addDays(7)->format('Y-m-d'),
            ]);

        $response->assertRedirect(
            route('reading-plans.index')
        );

        $this->assertDatabaseHas('reading_plans', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    public function test_未認証ユーザーは読書計画を登録できない(): void
    {
        $book = Book::factory()->create();

        $response = $this->post(
            route('reading-plans.store'),
            [
                'book_id' => $book->id,
                'target_date' => now()->addDays(7)->format('Y-m-d'),
            ]
        );

        $response->assertRedirect(
            route('login')
        );
    }

    public function test_書籍が未指定の場合は登録できない(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('reading-plans.store'), [
                'target_date' => now()->addDays(7)->format('Y-m-d'),
            ]);

        $response->assertSessionHasErrors([
            'book_id',
        ]);
    }

    public function test_期限日が未指定の場合は登録できない(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('reading-plans.store'), [
                'book_id' => $book->id,
            ]);

        $response->assertSessionHasErrors([
            'target_date',
        ]);
    }

    public function test_同じ書籍を重複登録できない(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('reading-plans.store'), [
                'book_id' => $book->id,
                'target_date' => now()->addDays(7)->format('Y-m-d'),
            ]);

        $response->assertSessionHasErrors([
            'book_id',
        ]);
    }

    public function test_認証ユーザーは読書計画を読了に変更できる(): void
    {
        $user = User::factory()->create();

        $plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::In_Progress,
            'completed_at' => null,
        ]);

        $response = $this
            ->actingAs($user)
            ->post(
                route('reading-plans.complete', $plan)
            );

        $response->assertRedirect(
            route('reading-plans.index')
        );

        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan->id,
            'status' => ReadingPlanStatus::Completed->value,
        ]);

        $this->assertNotNull(
            $plan->fresh()->completed_at
        );
    }

    public function test_未認証ユーザーは読了処理できない(): void
    {
        $plan = ReadingPlan::factory()->create();

        $response = $this->post(
            route('reading-plans.complete', $plan)
        );

        $response->assertRedirect(
            route('login')
        );
    }

    public function test_他ユーザーの読書計画は読了に変更できない(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $plan = ReadingPlan::factory()->create([
            'user_id' => $otherUser->id,
            'status' => ReadingPlanStatus::In_Progress,
        ]);

        $response = $this
            ->actingAs($user)
            ->post(
                route('reading-plans.complete', $plan)
            );

        $response->assertForbidden();

        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan->id,
            'status' => ReadingPlanStatus::In_Progress->value,
        ]);
    }
}
