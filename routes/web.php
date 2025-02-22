<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('guest');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('user');
    });

    Route::middleware(['admin'])->group(function () {
        Route::get('/admin', function () {
            return view('admin');
        });
    });
});

require __DIR__.'/auth.php';
