<?php
 
use Illuminate\Support\Facades\Route;
 
Route::view('/', 'home')->name('home');
Route::view('/rankings', 'rankings')->name('rankings');
Route::view('/results', 'results')->name('results');
Route::view('/pairs', 'pairs')->name('pairs');