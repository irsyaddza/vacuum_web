<?php

use App\Http\Controllers\DayahisapController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('main');
});
Route::get('about', function () {
    return view('about');
});

// Routes untuk Daya Hisap
Route::prefix('dayahisap')->group(function () {
    Route::post('store', [DayahisapController::class, 'store'])->name('dayahisap.store');
    Route::get('show', [DayahisapController::class, 'show'])->name('dayahisap.show');
    Route::get('latest', [DayahisapController::class, 'latest'])->name('dayahisap.latest');
});