<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\LastFmController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SongController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\FriendRequestController;
use App\Http\Controllers\ProfileCommentController;
use App\Http\Controllers\CommentReplyController;
use Illuminate\Support\Facades\Route;

// last.fm routes
Route::get('/auth/lastfm', [LastFmController::class, 'redirectToLastFm'])->name('lastfm.auth');
Route::get('/lastfm/callback', [LastFmController::class, 'handleCallback'])->name('lastfm.callback');

// application main routes
Route::get('/', [HomeController::class, 'index'])->name('home');

// authenticated routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/top-stats', [StatsController::class, 'showTopStats']);
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/friend-request/{user}', [FriendRequestController::class, 'send'])->name('friend.request');
    Route::post('/friend-request/{request}/accept', [FriendRequestController::class, 'accept'])->name('friend.accept');
    Route::post('/friend-request/{request}/reject', [FriendRequestController::class, 'reject'])->name('friend.reject');
    Route::post('/friend-remove/{user}', [FriendRequestController::class, 'remove'])->name('friend.remove');
});
Route::get('/connect-lastfm', function () {
    return view('connect-lastfm');
})->name('connect-lastfm');

// profile routes 
Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit')->middleware('auth');
Route::put('/profile/update', [ProfileController::class, 'update'])->name('profile.update')->middleware('auth');
Route::get('/profile/{username}', [ProfileController::class, 'show'])->name('profile.show');

Route::get('/song/{name}/{artist}', [App\Http\Controllers\SongController::class, 'show'])->name('song.show');
Route::get('/artist/{name}', [App\Http\Controllers\ArtistController::class, 'show'])->name('artist.show');

Route::get('/search', [SearchController::class, 'search'])->name('search');

Route::middleware(['auth'])->group(function () {
    Route::post('/profile/{user}/comment', [ProfileCommentController::class, 'store'])->name('profile.comment.store');
    Route::delete('/profile/comment/{comment}', [ProfileCommentController::class, 'destroy'])->name('profile.comment.destroy');
    Route::post('/comment/{comment}/reply', [CommentReplyController::class, 'store'])->name('comment.reply.store');
    Route::delete('/reply/{reply}', [CommentReplyController::class, 'destroy'])->name('comment.reply.destroy');
});

require __DIR__.'/auth.php';

