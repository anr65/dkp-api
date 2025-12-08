<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\V1\ContractController;
use App\Http\Controllers\Api\V1\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::get('/current', [AuthController::class, 'current']);

Route::prefix('v1')->group(function () {
    Route::apiResource('contracts', ContractController::class);
    Route::get('subs/available', [SubscriptionController::class, 'available']);
});
