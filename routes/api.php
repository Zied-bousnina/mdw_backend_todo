<?php

use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/




Route::post('user/create', [UserController::class, 'register']);
Route::post('user/login', [UserController::class, 'login']);

Route::middleware('auth:sanctum')->group(function() {
    Route::post('/task', [TaskController::class, 'store']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    }) ;
Route::post('/user/logout', [UserController::class, 'logout']);
    Route::get('/read', [TaskController::class, 'read']);
    Route::delete('/delete/{id}', [TaskController::class, 'delete']);
    Route::patch('/update/{id}', [TaskController::class, 'update']);
    Route::get('/readWithSortBy', [TaskController::class, 'readWithSortBy']);
    Route::get('/getStatistiqueByUser', [TaskController::class, 'getTaskStatistics']);
});
