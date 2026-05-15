<?php

use App\Http\Controllers\Api\AgentApiController;
use App\Http\Controllers\Api\AgentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::post('/heartbeat', [AgentApiController::class, 'heartbeat']);
    Route::get('/fetch-script', [AgentApiController::class, 'fetchScript']);
    Route::post('/agent/token', [AgentApiController::class, 'createToken']);
});

Route::prefix('v1/agent')->middleware('throttle:api')->group(function () {
    Route::post('/register', [AgentController::class, 'register']);

    Route::middleware('agent.auth')->group(function () {
        Route::post('/heartbeat', [AgentController::class, 'heartbeat']);
        Route::post('/poll', [AgentController::class, 'poll']);
        Route::post('/result', [AgentController::class, 'result']);
    });
});
