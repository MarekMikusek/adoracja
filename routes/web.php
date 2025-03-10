<?php

use App\Http\Controllers\CurrentDutyController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PatternController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReserveController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('patterns', [PatternController::class, 'index'])->name('patterns.index');

Route::middleware('auth')->group(function () {
    Route::post('current-duty', [CurrentDutyController::class, 'store'])->name('current-duty.store');
    Route::delete('patterns/{dutyPattern}', [PatternController::class, 'destroy'])->name('patterns.delete');
    Route::post('patterns', [PatternController::class, 'store'])->name('patterns.store');
    Route::post('suspend_petterns', [PatternController::class, 'suspend'])->name('patterns.suspend');
    Route::post('reserves', [ReserveController::class, 'store'])->name('reserves.store');
    Route::delete('reserves', [ReserveController::class, 'destroy'])->name('reserves.delete');

    Route::get('/reserves', [ReserveController::class, 'index'])->name('reserves.index');
    Route::post('/suspend', [ProfileController::class, 'saveSuspend'])->name('profile.save-suspend');
    Route::get('/suspend', [ProfileController::class, 'editSuspend'])->name('profile.edit-suspend');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

});

require __DIR__ . '/auth.php';
