<?php

use App\Http\Controllers\VacuumAPIController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('main');
});
Route::get('about', function () {
    return view('about');
});

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

// Remove atau comment out default route jika ada
// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// ===== VACUUM API v1 Routes =====
Route::prefix('v1')->name('api.')->group(function () {
    Route::prefix('vacuum')->group(function () {
        
        // ===== GET Endpoints (Polling from ESP32 & Web App) =====
        Route::get('status', [VacuumAPIController::class, 'getStatus'])->name('vacuum.status');
        Route::get('battery/latest', [VacuumAPIController::class, 'getLatestBattery'])->name('vacuum.battery.latest');
        Route::get('battery/history', [VacuumAPIController::class, 'getBatteryHistory'])->name('vacuum.battery.history');
        Route::get('full-status', [VacuumAPIController::class, 'getFullStatus'])->name('vacuum.full-status');
        
        // ===== POST Endpoints (Commands from Web App & ESP32) =====
        Route::post('command', [VacuumAPIController::class, 'sendCommand'])->name('vacuum.command');
        Route::post('power-mode', [VacuumAPIController::class, 'setPowerMode'])->name('vacuum.power-mode');
        Route::post('battery', [VacuumAPIController::class, 'updateBattery'])->name('vacuum.battery');
    });
});