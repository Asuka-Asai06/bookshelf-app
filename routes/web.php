<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ReviewLikeController;
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

Route::middleware('auth')->group(function () {
    Route::post(
        '/books/{book}/reviews',
        [ReviewController::class, 'store']
    )->name('reviews.store');

    Route::get(
        '/reviews/{review}/edit',
        [ReviewController::class, 'edit']
    )->name('reviews.edit');

    Route::put(
        '/reviews/{review}',
        [ReviewController::class, 'update']
    )->name('reviews.update');

    Route::delete(
        '/reviews/{review}',
        [ReviewController::class, 'destroy']
    )->name('reviews.destroy');

    Route::post(
        '/books/{book}/favorites',
        [FavoriteController::class, 'toggle']
    )->name('favorites.toggle');

    Route::post(
        '/reviews/{review}/likes',
        [ReviewLikeController::class, 'toggle']
    )->name('reviews.like');
});

Route::get('/ranking/index', [RankingController::class, 'index'])->name('ranking.index');

Route::get('/favorite/index', [FavoriteController::class, 'create'])->name('favorites.index');
