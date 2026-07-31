<?php

namespace Database\Seeders;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ReadingPlanSeeder extends Seeder
{
    public function run(): void
    {
        $today = Carbon::today();

        ReadingPlan::create([
            'user_id' => 1,
            'book_id' => 1,
            'target_date' => $today->copy()->addDays(3),
            'status' => ReadingPlanStatus::In_Progress,
            'completed_at' => null,
        ]);

        ReadingPlan::create([
            'user_id' => 1,
            'book_id' => 2,
            'target_date' => $today->copy(),
            'status' => ReadingPlanStatus::In_Progress,
            'completed_at' => null,
        ]);

        ReadingPlan::create([
            'user_id' => 1,
            'book_id' => 3,
            'target_date' => $today->copy()->subDays(3),
            'status' => ReadingPlanStatus::In_Progress,
            'completed_at' => null,
        ]);

        ReadingPlan::create([
            'user_id' => 1,
            'book_id' => 4,
            'target_date' => $today->copy()->addDays(7),
            'status' => ReadingPlanStatus::In_Progress,
            'completed_at' => null,
        ]);

        ReadingPlan::create([
            'user_id' => 1,
            'book_id' => 5,
            'target_date' => $today->copy()->subDays(10),
            'status' => ReadingPlanStatus::Completed,
            'completed_at' => $today->copy()->subDays(5),
        ]);

        ReadingPlan::create([
            'user_id' => 2,
            'book_id' => 6,
            'target_date' => $today->copy()->addDays(5),
            'status' => ReadingPlanStatus::In_Progress,
            'completed_at' => null,
        ]);
    }
}
