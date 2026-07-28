<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BookController;
use App\Http\Controllers\Api\V1\BookSearchController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->as('api.v1.')->group(function () {

    Route::get(
        'books/isbn/{isbn}',
        [BookSearchController::class, 'show']
    )->name('books.search');

    Route::apiResource('books', BookController::class)
        ->only(['index', 'show']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('books', BookController::class)
            ->only(['store', 'update', 'destroy']);
    });
});
