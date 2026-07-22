<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\GenreController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BookController::class, 'index']);

Route::middleware('auth')->group(function () {
    Route::resource('books', BookController::class)
        ->except(['index', 'show']);
});

Route::resource('books', BookController::class)
    ->only(['index', 'show']);

Route::middleware('auth')->group(function () {
    Route::resource('genres', GenreController::class);
});

Route::get('/ranking/index', [RankingController::class, 'index'])->name('ranking.index');

Route::get('/favorite/index', [FavoriteController::class, 'create'])->name('favorites.index');
