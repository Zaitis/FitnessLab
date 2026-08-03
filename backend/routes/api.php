<?php

use App\Http\Controllers\BmiController;
use App\Http\Controllers\DisclaimerController;
use App\Http\Controllers\MeasurementController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/health', fn () => response()->json(['status' => 'ok']));

Route::post('/bmi/calculate', [BmiController::class, 'calculate'])
    ->middleware('throttle:bmi');

Route::get('/disclaimer', [DisclaimerController::class, 'show']);

Route::post('/measurements', [MeasurementController::class, 'store'])
    ->middleware('auth:sanctum');

require __DIR__.'/auth.php';
