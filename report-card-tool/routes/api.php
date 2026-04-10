<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/copy', [ReportController::class, 'copy']);
Route::get('/fetch-data/{car}/{month}/{year}', [ReportController::class, 'fetchDataFromLocal']);
Route::get('/available-data', [ReportController::class, 'getAvailableData']);