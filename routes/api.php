<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\ReplyController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\AgentController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/posts', [PostController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('tickets', TicketController::class)->except(['destroy']);

    Route::get('/tickets/{ticket}/replies', [ReplyController::class, 'index']);
    Route::post('/tickets/{ticket}/replies', [ReplyController::class, 'store']);

    Route::prefix('agent')->group(function () {
        Route::get('/tickets', [AgentController::class, 'tickets']);
        Route::get('/tickets/{ticket}', [AgentController::class, 'show']);
        Route::put('/tickets/{ticket}/status', [AgentController::class, 'updateStatus']);
        Route::get('/tickets/{ticket}/replies', [AgentController::class, 'replies']);
        Route::post('/tickets/{ticket}/replies', [AgentController::class, 'reply']);
    });
});
