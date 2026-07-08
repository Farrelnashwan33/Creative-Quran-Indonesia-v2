<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\QuranController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BookmarkController;
use App\Http\Controllers\Api\V1\NoteController;
use App\Http\Controllers\Api\V1\HighlightController;
use App\Http\Controllers\Api\V1\LastReadController;
use App\Http\Controllers\Api\V1\StatisticsController;

Route::prefix('v1')->group(function () {
    // Auth Routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Quran API (Publicly accessible)
    Route::get('/surah', [QuranController::class, 'getSurahs']);
    Route::get('/surah/{id}', [QuranController::class, 'getSurah']);
    Route::get('/juz', [QuranController::class, 'getJuzs']);
    Route::get('/juz/{id}', [QuranController::class, 'getJuz']);
    Route::get('/page/{page}', [QuranController::class, 'getPage']);
    Route::get('/search', [QuranController::class, 'search']);
    Route::get('/random', [QuranController::class, 'getRandomAyah']);
    Route::get('/tafsir/{surah}', [QuranController::class, 'getTafsir']);
    Route::get('/ayah/{surah}/{ayah}', [QuranController::class, 'getAyah']);
    Route::get('/audio/{surah}', [QuranController::class, 'getAudio']);
    Route::get('/reciters', [QuranController::class, 'getReciters']);

    // Protected Routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);

        // User Features CRUD
        Route::apiResource('bookmarks', BookmarkController::class);
        Route::apiResource('notes', NoteController::class);
        Route::apiResource('highlights', HighlightController::class);
        Route::apiResource('last-reads', LastReadController::class);

        // Statistics
        Route::get('/dashboard', [StatisticsController::class, 'dashboard']);
        Route::get('/statistics', [StatisticsController::class, 'statistics']);
    });
});
