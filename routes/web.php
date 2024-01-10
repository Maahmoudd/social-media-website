<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;


Route::get('/', [HomeController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');


Route::get('/u/{user:username}', [ProfileController::class, 'index'])
    ->name('profile');



require __DIR__.'/auth.php';
