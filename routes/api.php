<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// route to copy data from Porter API to local database
Route::get('/copy', [ReportController::class, 'copy']);

// route to fetch data for a specific car and month/year
Route::get('/fetch-data/{car}/{month}/{year}', [ReportController::class, 'fetchDataFromLocal']);

// route to get available cars and periods for dropdowns
Route::get('/available-data', [ReportController::class, 'getAvailableData']);

// route to fetch all data for a specific month/year (for report generation)
Route::get('/fetch-all/{month}/{year}', [ReportController::class, 'fetchAllForPeriod']);

// API key related routes
Route::post('/update-api-key', [ReportController::class, 'updateApiKey']);
Route::get('/get-api-key', [ReportController::class, 'getApiKey']);
