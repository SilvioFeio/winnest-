<?php

use App\Services\PairRanking;
use Illuminate\Support\Facades\Route;
 
Route::view('/', 'home')->name('home');

Route::get('/rankings', function () {
    $ranking = (new PairRanking)->rank();

    return view('rankings', ['ranking' => $ranking]);
})->name('rankings');

Route::view('/results', 'results')->name('results');
Route::view('/pairs', 'pairs')->name('pairs');