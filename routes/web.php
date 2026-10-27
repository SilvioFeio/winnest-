<?php

use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

// Every page is a method on PageController; the name is used by route('...') in the views
Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/rankings', [PageController::class, 'rankings'])->name('rankings');
Route::get('/results', [PageController::class, 'results'])->name('results');
Route::get('/pairs', [PageController::class, 'pairs'])->name('pairs');
