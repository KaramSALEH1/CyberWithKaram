<?php

use App\Http\Controllers\Api\AgentApiController;
use App\Http\Controllers\Api\AgentController;
use Illuminate\Support\Facades\Route;

// Licensed agent API (Sanctum personal access token).
// `fetch-script` is throttled hardest because it returns executable payload
// code and accepts a brute-forceable licence key.
Route::middleware(['auth:sanctum', 'throttle:agent-script'])->group(function () {
    Route::get('/fetch-script', [AgentApiController::class, 'fetchScript']);
});

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::post('/heartbeat', [AgentApiController::class, 'heartbeat']);
    Route::post('/agent/token', [AgentApiController::class, 'createToken']);
});

// v1 agent protocol (X-Agent-Key + hashed bearer token).
Route::prefix('v1/agents')->middleware('throttle:agent-auth')->group(function () {
    Route::post('/register', [AgentController::class, 'register'])->middleware('auth:sanctum');

    Route::middleware('agent.auth')->group(function () {
        Route::post('/heartbeat', [AgentController::class, 'heartbeat']);
        Route::post('/poll', [AgentController::class, 'poll']);
        Route::post('/result', [AgentController::class, 'result']);
    });
});

// Legacy alias
Route::prefix('v1/agent')->middleware('throttle:agent-auth')->group(function () {
    Route::post('/register', [AgentController::class, 'register'])->middleware('auth:sanctum');
    Route::middleware('agent.auth')->group(function () {
        Route::post('/heartbeat', [AgentController::class, 'heartbeat']);
        Route::post('/poll', [AgentController::class, 'poll']);
        Route::post('/result', [AgentController::class, 'result']);
    });
});
