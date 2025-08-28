<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProfileSessionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Profile Session Management Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/profile-sessions/start', [ProfileSessionController::class, 'startSession']);
    Route::post('/profile-sessions/end', [ProfileSessionController::class, 'endSession']);
    Route::post('/profile-sessions/update-activity', [ProfileSessionController::class, 'updateActivity']);
    Route::get('/profile-sessions/status/{profileId}', [ProfileSessionController::class, 'checkStatus']);
});
