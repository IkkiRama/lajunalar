<?php

use App\Http\Controllers\Auth\GoogleController;
use Illuminate\Support\Facades\Route;

Route::get('/login', fn () => redirect()->route('google.redirect'))->name('login');
Route::get('/auth/google/redirect', [GoogleController::class, 'redirect'])
    ->middleware('throttle:google-auth')
    ->name('google.redirect');
Route::get('/auth/google/callback', [GoogleController::class, 'callback'])
    ->middleware('throttle:google-auth')
    ->name('google.callback');
Route::post('/logout', [GoogleController::class, 'logout'])->middleware('auth')->name('logout');

Route::view('/{path?}', 'app')
    ->where('path', '^(?!api(?:/|$)|admin(?:/|$)|docs(?:/|$)|sanctum(?:/|$)).*')
    ->name('spa');
