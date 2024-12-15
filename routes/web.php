<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\LastFmController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// last.fm routes
Route::get('/auth/lastfm', [LastFmController::class, 'redirectToLastFm'])->name('lastfm.auth');
Route::get('/lastfm/callback', [LastFmController::class, 'handleCallback'])->name('lastfm.callback');

// application main routes
Route::get('/', function () {
    return view('dashboard');
});

// authenticated routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/top-stats', [StatsController::class, 'showTopStats']);
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/connect-lastfm', function () {
    return view('connect-lastfm');
})->name('connect-lastfm');

require __DIR__.'/auth.php';
