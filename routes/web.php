<?php

use App\Http\Controllers\RunController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::resource('runs', RunController::class)->only(['index', 'create', 'store']);
});

require __DIR__.'/settings.php';
