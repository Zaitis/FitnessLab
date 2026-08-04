<?php

use App\Http\Controllers\Admin\ErrorLogController;
use App\Http\Controllers\BmiController;
use App\Http\Controllers\DisclaimerController;
use App\Http\Controllers\MeasurementController;
use App\Http\Controllers\NutritionPlanController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkoutPlanController;
use App\Models\ErrorLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::patch('/user/locale', [UserController::class, 'updateLocale'])
    ->middleware('auth:sanctum');

Route::get('/health', fn () => response()->json(['status' => 'ok']));

Route::post('/bmi/calculate', [BmiController::class, 'calculate'])
    ->middleware('throttle:bmi');

Route::get('/disclaimer', [DisclaimerController::class, 'show']);

Route::get('/measurements', [MeasurementController::class, 'index'])
    ->middleware('auth:sanctum');

Route::post('/measurements', [MeasurementController::class, 'store'])
    ->middleware('auth:sanctum');

Route::get('/admin/logs', [ErrorLogController::class, 'index'])
    ->middleware(['auth:sanctum', 'can:viewAny,'.ErrorLog::class]);

Route::apiResource('workout-plans', WorkoutPlanController::class)
    ->only(['index', 'store', 'show'])
    ->middleware('auth:sanctum');

Route::apiResource('nutrition-plans', NutritionPlanController::class)
    ->only(['index', 'store', 'show'])
    ->middleware('auth:sanctum');

require __DIR__.'/auth.php';
