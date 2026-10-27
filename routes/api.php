<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Debate\DebateController;
use App\Http\Controllers\Message\MessageController;
use App\Http\Controllers\Militant\MilitantController;
use App\Http\Controllers\Suggestion\SuggestionController;
use App\Http\Controllers\Voting\SignatureController;
use App\Http\Controllers\Voting\VoteTopicController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('register', [AuthController::class, 'register'])->middleware('throttle:5,1');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::post('militants', [MilitantController::class, 'store'])->middleware('throttle:5,1');

    Route::get('debates', [DebateController::class, 'index']);
    Route::get('debates/{debate}', [DebateController::class, 'show']);
    Route::get('debates/{debate}/messages', [MessageController::class, 'index']);
    Route::get('vote-topics', [VoteTopicController::class, 'index']);
    Route::get('vote-topics/{voteTopic}', [VoteTopicController::class, 'show']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('/user', function (Request $request) {
            return $request->user()->toAuthArray();
        });
        Route::patch('/user', [AuthController::class, 'update']);

        Route::post('debates/{debate}/messages', [MessageController::class, 'store'])
            ->middleware('throttle:30,1');

        Route::post('vote-topics/{voteTopic}/sign', [SignatureController::class, 'store'])
            ->middleware('throttle:signatures');
        Route::get('my-signatures', [SignatureController::class, 'index']);

        Route::get('suggestions', [SuggestionController::class, 'index']);
        Route::post('suggestions', [SuggestionController::class, 'store'])->middleware('throttle:10,1');

        Route::middleware('admin')->group(function (): void {
            Route::post('debates', [DebateController::class, 'store']);
            Route::match(['put', 'patch', 'post'], 'debates/{debate}', [DebateController::class, 'update']);
            Route::delete('debates/{debate}', [DebateController::class, 'destroy']);

            Route::post('vote-topics', [VoteTopicController::class, 'store']);
            Route::match(['put', 'patch'], 'vote-topics/{voteTopic}', [VoteTopicController::class, 'update']);
            Route::delete('vote-topics/{voteTopic}', [VoteTopicController::class, 'destroy']);

            Route::get('admin/summary', \App\Http\Controllers\Admin\DashboardController::class);
            Route::get('admin/users', [\App\Http\Controllers\Admin\UserController::class, 'index']);
            Route::get('admin/militants', [\App\Http\Controllers\Admin\MilitantController::class, 'index']);
            Route::patch('admin/militants/{militant}', [\App\Http\Controllers\Admin\MilitantController::class, 'update']);
            Route::get('admin/vote-topics/{voteTopic}/signatures', [\App\Http\Controllers\Admin\SignatureController::class, 'index']);

            Route::get('admin/suggestions', [\App\Http\Controllers\Admin\SuggestionController::class, 'index']);
            Route::post('admin/suggestions/{suggestion}/publish', [\App\Http\Controllers\Admin\SuggestionController::class, 'publish']);
            Route::post('admin/suggestions/{suggestion}/reject', [\App\Http\Controllers\Admin\SuggestionController::class, 'reject']);
        });
    });
});
