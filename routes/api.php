<?php

use App\Http\Controllers\Api\AgentController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/agent')->group(function () {
    Route::post('/register', [AgentController::class, 'register']);

    Route::middleware('agent.auth')->group(function () {
        Route::post('/heartbeat', [AgentController::class, 'heartbeat']);
        Route::post('/poll', [AgentController::class, 'poll']);
        Route::post('/result', [AgentController::class, 'result']);
    });
});
