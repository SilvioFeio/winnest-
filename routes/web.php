<?php

use App\Http\Controllers\PageController;
use App\Services\PairRanking;
use Illuminate\Support\Facades\Route;
 
Route::view('/', 'home')->name('home');

Route::get('/rankings', function () {
    $ranking = (new PairRanking)->rank();

    return view('rankings', ['ranking' => $ranking]);
})->name('rankings');

Route::get('/results', [PageController::class, 'results'])->name('results');
Route::get('/pairs', [PageController::class, 'pairs'])->name('pairs');
