<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\ReadingPlan\StoreReadingPlanRequest;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreReadingPlanRequestTest extends TestCase
{
    use RefreshDatabase;

    private function validator(array $data)
    {
        $request = new StoreReadingPlanRequest;

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

        $validator = $this->validator([
            'book_id' => $book->id,
            'target_date' => now()->addDays(7)->format('Y-m-d'),
        ]);

        $this->assertTrue(
            $validator->passes()
        );
    }

    public function test_書籍が未選択の場合はエラーになる(): void
    {
        $validator = $this->validator([
            'target_date' => now()->addDays(7)->format('Y-m-d'),
        ]);

        $this->assertFalse(
            $validator->passes()
        );

        $this->assertTrue(
            $validator->errors()->has('book_id')
        );
    }

    public function test_存在しない書籍はエラーになる(): void
    {
        $validator = $this->validator([
            'book_id' => 99999,
            'target_date' => now()->addDays(7)->format('Y-m-d'),
        ]);

        $this->assertFalse(
            $validator->passes()
        );

        $this->assertTrue(
            $validator->errors()->has('book_id')
        );
    }

    public function test_同じ書籍を重複登録できない(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $book = Book::factory()->create();

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $validator = $this->validator([
            'book_id' => $book->id,
            'target_date' => now()->addDays(7)->format('Y-m-d'),
        ]);

        $this->assertFalse(
            $validator->passes()
        );

        $this->assertTrue(
            $validator->errors()->has('book_id')
        );
    }

    public function 期日が未選択の場合はエラーになる(): void
    {
        $book = Book::factory()->create();

        $validator = $this->validator([
            'book_id' => $book->id,
        ]);

        $this->assertFalse(
            $validator->passes()
        );

        $this->assertTrue(
            $validator->errors()->has('target_date')
        );
    }

    public function test_過去の日付はエラーになる(): void
    {
        $book = Book::factory()->create();

        $validator = $this->validator([
            'book_id' => $book->id,
            'target_date' => now()->subDay()->format('Y-m-d'),
        ]);

        $this->assertFalse(
            $validator->passes()
        );

        $this->assertTrue(
            $validator->errors()->has('target_date')
        );
    }

    public function test_今日の日付は登録できる(): void
    {
        $book = Book::factory()->create();

        $validator = $this->validator([
            'book_id' => $book->id,
            'target_date' => today()->format('Y-m-d'),
        ]);

        $this->assertTrue(
            $validator->passes()
        );
    }
}
