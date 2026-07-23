<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class FavoriteSeeder extends Seeder
{
    public function run(): void
    {
        User::find(1)->favoriteBooks()->syncWithoutDetaching([1, 3, 6, 10]);
        User::find(2)->favoriteBooks()->syncWithoutDetaching([1, 5, 9]);
        User::find(3)->favoriteBooks()->syncWithoutDetaching([2, 4, 8, 10]);
        User::find(4)->favoriteBooks()->syncWithoutDetaching([3, 6, 11]);
        User::find(5)->favoriteBooks()->syncWithoutDetaching([1, 4, 6, 7, 10]);
    }
}
