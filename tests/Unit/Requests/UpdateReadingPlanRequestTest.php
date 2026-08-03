<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\ReadingPlan\UpdateReadingPlanRequest;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdateReadingPlanRequestTest extends TestCase
{
    use RefreshDatabase;

    private function validator(array $data, ReadingPlan $readingPlan)
    {
        $request = new UpdateReadingPlanRequest;

        $request->setRouteResolver(function () use ($readingPlan) {
            return new class($readingPlan)
            {
                public function __construct(
                    private ReadingPlan $readingPlan
                ) {}

                public function parameter($key)
                {
                    return $key === 'reading_plan'
                        ? $this->readingPlan
                        : null;
                }
            };
        });

        return Validator::make(
            $data,
            $request->rules()
        );
    }

    public function test_必須項目が入力されていればバリデーションを通過する(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $book = Book::factory()->create();

        $plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $validator = $this->validator([
            'book_id' => $book->id,
            'target_date' => now()->addDays(7)->format('Y-m-d'),
        ], $plan);

        $this->assertTrue($validator->passes());
    }

    public function test_書籍が未選択の場合はエラーになる(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
        ]);

        $validator = $this->validator([
            'target_date' => now()->addDays(7)->format('Y-m-d'),
        ], $plan);

        $this->assertFalse($validator->passes());

        $this->assertTrue(
            $validator->errors()->has('book_id')
        );
    }

    public function test_期日が未選択の場合はエラーになる(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
        ]);

        $validator = $this->validator([
            'book_id' => $plan->book_id,
        ], $plan);

        $this->assertFalse($validator->passes());

        $this->assertTrue(
            $validator->errors()->has('target_date')
        );
    }

    public function test_過去の日付はエラーになる(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
        ]);

        $validator = $this->validator([
            'book_id' => $plan->book_id,
            'target_date' => now()->subDay()->format('Y-m-d'),
        ], $plan);

        $this->assertFalse($validator->passes());

        $this->assertTrue(
            $validator->errors()->has('target_date')
        );
    }

    public function test_今日の日付はバリデーションを通過する(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
        ]);

        $validator = $this->validator([
            'book_id' => $plan->book_id,
            'target_date' => today()->format('Y-m-d'),
        ], $plan);

        $this->assertTrue($validator->passes());
    }

    public function test_他の読書計画と同じ書籍には変更できない(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book1->id,
        ]);

        $plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book2->id,
        ]);

        $validator = $this->validator([
            'book_id' => $book1->id,
            'target_date' => now()->addDays(7)->format('Y-m-d'),
        ], $plan);

        $this->assertFalse($validator->passes());

        $this->assertTrue(
            $validator->errors()->has('book_id')
        );
    }

    public function test_自分自身の書籍は更新できる(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $book = Book::factory()->create();

        $plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $validator = $this->validator([
            'book_id' => $book->id,
            'target_date' => now()->addDays(10)->format('Y-m-d'),
        ], $plan);

        $this->assertTrue($validator->passes());
    }
}
