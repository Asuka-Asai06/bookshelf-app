<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewLikeSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = User::pluck('id');

        Review::all()->each(function (Review $review) use ($userIds) {

            $likedUserIds = $userIds
                ->reject(fn ($id) => $id === $review->user_id)
                ->shuffle()
                ->take(rand(0, 3));

            $review->likedByUsers()->syncWithoutDetaching($likedUserIds);
        });
    }
}
