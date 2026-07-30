<?php

namespace App\Policies;

use App\Models\ReadingPlan;
use App\Models\User;

class ReadingPlanPolicy
{
    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, ReadingPlan $readingPlan): bool
    {
        return $readingPlan->user_id === $user->id;
    }

    public function delete(User $user, ReadingPlan $readingPlan): bool
    {
        return $readingPlan->user_id === $user->id;
    }
}
