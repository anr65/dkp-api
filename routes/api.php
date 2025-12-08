<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\V1\CarController;
use App\Http\Controllers\Api\V1\ContractController;
use App\Http\Controllers\Api\V1\OcrController;
use App\Http\Controllers\Api\V1\PersonController;
use App\Http\Controllers\Api\V1\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::get('/current', [AuthController::class, 'current']);

Route::prefix('v1')->group(function () {
    Route::apiResource('contracts', ContractController::class);
    Route::get('subs/available', [SubscriptionController::class, 'available']);

    Route::post('ocr/passport', [OcrController::class, 'passport']);
    Route::post('ocr/sts', [OcrController::class, 'sts']);

    Route::post('passport', [PersonController::class, 'store']);
    Route::post('car', [CarController::class, 'store']);
});
