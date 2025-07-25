<?php

use App\Http\Controllers\GameController;
use Illuminate\Support\Facades\Route;

Route::prefix('games')->group(function () {
    Route::get('genres', [GameController::class, 'genres']);

    Route::get('/', [GameController::class, 'index']);
    Route::get('/{id}', [GameController::class, 'show']);
    Route::post('/', [GameController::class, 'store']);
    Route::patch('/{id}', [GameController::class, 'update']);
    Route::delete('/{id}', [GameController::class, 'destroy']);
});
