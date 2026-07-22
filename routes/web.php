<?php

use App\Http\Controllers\BookController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BookController::class, 'index']);

Route::resource('books', BookController::class)
    ->only(['index', 'show']);

Route::middleware('auth')->group(function () {
    Route::resource('books', BookController::class)
        ->except(['index', 'show']);
});

Route::get('/ranking/index', [RankingController::class, 'index'])->name('ranking.index');

Route::get('/favorite/index', [FavoriteController::class, 'create'])->name('favorites.index');

Route::get('/genres/index', [GenresController::class, 'index'])->name('genres.index');
